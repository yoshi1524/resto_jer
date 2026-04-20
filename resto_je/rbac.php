<?php
// Redirect to new login page for backwards compatibility
header('Location: login.php');
exit;

function isSafeRedirect(string $url): bool {
    $parsed = parse_url($url);
    if ($parsed === false) {
        return false;
    }
    if (isset($parsed['scheme']) || isset($parsed['host'])) {
        return false;
    }
    if (!isset($parsed['path']) || strpos($parsed['path'], '..') !== false) {
        return false;
    }
    return preg_match('#^[a-zA-Z0-9_\/\.\-]+$#', $parsed['path']);
}

$redirectUrl = 'restaurant_pos.php';
if (isset($_GET['redirect']) && isSafeRedirect($_GET['redirect'])) {
    $redirectUrl = $_GET['redirect'];
}

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'resto_pos';

function dbConnect() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $conn->set_charset('utf8mb4');
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);
    return $conn;
}

function ensureSchema(mysqli $conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
        status ENUM('active','archived') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS user_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(50) NULL,
        action VARCHAR(100) NOT NULL,
        detail TEXT NULL,
        action_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id),
        INDEX(action_time),
        CONSTRAINT fk_user_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $result = $conn->query("SELECT COUNT(*) AS count FROM users");
    $row = $result->fetch_assoc();
    if (isset($row['count']) && (int)$row['count'] === 0) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, password, role, status) VALUES ('admin', '{$conn->real_escape_string($passwordHash)}', 'admin', 'active')");
        logAction($conn, null, 'system', 'default_admin_created', 'Created default admin user with username admin and password admin123');
    }
}

function logAction(mysqli $conn, ?int $userId, ?string $username, string $action, ?string $detail = null) {
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, username, action, detail) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $userId, $username, $action, $detail);
    $stmt->execute();
    $stmt->close();
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: ?');
        exit;
    }
}

function canManageUsers(): bool {
    $user = currentUser();
    return $user && in_array($user['role'], ['admin', 'manager'], true);
}

function getUsers(mysqli $conn): array {
    $result = $conn->query("SELECT id, username, role, status, created_at FROM users ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getLogs(mysqli $conn, int $limit = 50): array {
    $stmt = $conn->prepare("SELECT id, user_id, username, action, detail, action_time FROM user_logs ORDER BY action_time DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

$conn = dbConnect();
ensureSchema($conn);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['redirect']) && isSafeRedirect($_POST['redirect'])) {
        $redirectUrl = $_POST['redirect'];
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, username, password, role, status FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = 'Invalid credentials.';
            } elseif ($user['status'] !== 'active') {
                $error = 'This account has been archived and cannot login.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Invalid credentials.';
            } else {
                unset($user['password']);
                $_SESSION['user'] = $user;
                logAction($conn, $user['id'], $user['username'], 'login', 'User logged in');
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
    } elseif ($action === 'logout') {
        $user = currentUser();
        if ($user) {
            logAction($conn, $user['id'], $user['username'], 'logout', 'User logged out');
        }
        session_destroy();
        header('Location: ?');
        exit;
    } elseif ($action === 'add_user') {
        requireLogin();
        if (!canManageUsers()) {
            $error = 'Access denied. Only admin or commissary can add accounts.';
        } else {
            $username = trim($_POST['new_username'] ?? '');
            $password = trim($_POST['new_password'] ?? '');
            $role = $_POST['new_role'] ?? 'staff';
            if ($username === '' || $password === '') {
                $error = 'Username and password are required.';
            } elseif (!in_array($role, ['admin', 'manager', 'staff'], true)) {
                $error = 'Invalid role selected.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, "active")');
                $stmt->bind_param('sss', $username, $hash, $role);
                if ($stmt->execute()) {
                    $message = 'User account created successfully.';
                    $user = currentUser();
                    logAction($conn, $user['id'], $user['username'], 'add_user', "Created account: {$username} ({$role})");
                } else {
                    $error = 'Could not create user. Username may already exist.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'archive_user') {
        requireLogin();
        if (!canManageUsers()) {
            $error = 'Access denied. Only admin or commissary can archive accounts.';
        } else {
            $userId = intval($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                $error = 'Invalid user ID.';
            } else {
                $stmt = $conn->prepare('UPDATE users SET status = "archived" WHERE id = ? AND status = "active"');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $message = 'User account archived successfully.';
                    $user = currentUser();
                    logAction($conn, $user['id'], $user['username'], 'archive_user', "Archived account ID {$userId}");
                } else {
                    $error = 'Unable to archive this account.';
                }
                $stmt->close();
            }
        }
    }
}

$user = currentUser();
$users = $user ? getUsers($conn) : [];
$logs = $user ? getLogs($conn, 100) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Countryside User Management</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#111; color:#efefef; }
        .page { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { width:100%; max-width:980px; background:#1f1f1f; border:1px solid #333; border-radius:16px; box-shadow:0 24px 60px rgba(0,0,0,.45); overflow:hidden; }
        .card-header { background:#141414; padding:22px 28px; border-bottom:1px solid #2c2c2c; }
        .card-header h1 { margin:0; font-size:24px; letter-spacing:-.5px; }
        .card-body { padding:24px; }
        .form-group { margin-bottom:16px; }
        label { display:block; margin-bottom:6px; color:#ccc; font-size:13px; }
        input, select { width:100%; padding:12px 14px; border-radius:12px; border:1px solid #333; background:#121212; color:#eee; font-size:14px; }
        button { cursor:pointer; border:none; border-radius:12px; padding:12px 18px; background:#e8a045; color:#111; font-weight:700; transition:.2s; }
        button:hover { filter:brightness(1.05); }
        .actions { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .badge { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:rgba(232,160,69,.12); color:#f5d8b1; font-size:13px; }
        table { width:100%; border-collapse:collapse; margin-top:12px; }
        th, td { padding:12px 10px; text-align:left; border-bottom:1px solid #2c2c2c; font-size:14px; }
        th { color:#aaa; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
        .status-active { color:#8edc7d; }
        .status-archived { color:#f17c7c; }
        .message { margin-bottom:20px; padding:14px 18px; border-radius:12px; background:#2c2c2c; }
        .message.success { border:1px solid #4a8; }
        .message.error { border:1px solid #c55; }
        .log-list { max-height:320px; overflow:auto; }
        .log-row { padding:10px 0; border-bottom:1px solid #242424; font-size:13px; color:#ddd; }
        .log-row:last-child { border-bottom:none; }
        .topbar-link { color:#e8a045; text-decoration:none; }
        .login-panel { display:grid; gap:16px; }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="card-header">
            <h1>CountrysidePOS User Management and Audit Log</h1>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="message success"><?= sanitize($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <?php if (!$user): ?>
                <div class="login-panel">
                    <p>Sign in with an account that has permission to manage POS users and audit login/logout actions.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="login" />
                        <input type="hidden" name="redirect" value="<?= sanitize($redirectUrl) ?>" />
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" required />
                        </div>
                        <button type="submit">Login</button>
                    </form>
                    <p style="margin-top:16px;font-size:13px;color:#aaa;">Default admin: <strong>admin</strong> / <strong>admin123</strong></p>
                </div>
            <?php else: ?>
                <div class="actions">
                    <div>
                        <span class="badge">Signed in as <?= sanitize($user['username']) ?> (<?= sanitize($user['role']) ?>)</span>
                    </div>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="action" value="logout" />
                        <button type="submit">Logout</button>
                    </form>
                </div>

                <?php if (canManageUsers()): ?>
                    <section style="margin-bottom:28px;">
                        <h2 style="margin:0 0 14px 0;font-size:18px;">Add New POS User</h2>
                        <form method="post" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:end;">
                            <input type="hidden" name="action" value="add_user" />
                            <div class="form-group">
                                <label for="new_username">Username</label>
                                <input id="new_username" name="new_username" type="text" required />
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password</label>
                                <input id="new_password" name="new_password" type="password" required />
                            </div>
                            <div class="form-group" style="grid-column:span 2;">
                                <label for="new_role">Role</label>
                                <select id="new_role" name="new_role">
                                    <option value="staff">Staff</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit">Create Account</button>
                        </form>
                    </section>
                <?php endif; ?>

                <section style="margin-bottom:28px;">
                    <h2 style="margin:0 0 14px 0;font-size:18px;">User Accounts</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <?php if (canManageUsers()): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $account): ?>
                                <tr>
                                    <td><?= sanitize((string)$account['id']) ?></td>
                                    <td><?= sanitize($account['username']) ?></td>
                                    <td><?= sanitize($account['role']) ?></td>
                                    <td class="status-<?= sanitize($account['status']) ?>"><?= sanitize(ucfirst($account['status'])) ?></td>
                                    <td><?= sanitize($account['created_at']) ?></td>
                                    <?php if (canManageUsers()): ?>
                                        <td>
                                            <?php if ($account['status'] === 'active'): ?>
                                                <form method="post" style="display:inline-block;margin:0;">
                                                    <input type="hidden" name="action" value="archive_user" />
                                                    <input type="hidden" name="user_id" value="<?= sanitize((string)$account['id']) ?>" />
                                                    <button type="submit" style="background:#c64f4f;">Archive</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:#888;">Archived</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section>
                    <h2 style="margin:0 0 14px 0;font-size:18px;">Recent User Actions</h2>
                    <div class="log-list">
                        <?php if ($logs): ?>
                            <?php foreach ($logs as $entry): ?>
                                <div class="log-row">
                                    <strong><?= sanitize($entry['action']) ?></strong>
                                    <span style="color:#999;">by <?= sanitize($entry['username'] ?? 'system') ?> at <?= sanitize($entry['action_time']) ?></span>
                                    <?php if ($entry['detail']): ?>
                                        <div style="margin-top:4px;color:#bbb;"><?= sanitize($entry['detail']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="log-row">No user actions logged yet.</div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
