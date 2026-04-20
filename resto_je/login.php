<?php
if (isset($_SESSION['user_id'])) {
    
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } elseif ($_SESSION['role'] === 'manager') {
        header('Location: manager.php');
    } else {
        header('Location: cashier.php');
    }
    exit;
}
require 'config.php';

$conn = dbConnect();
ensureSchema($conn);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                
                // Route to appropriate dashboard
                // After password_verify is successful...
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role']; // This must be 'manager'
$_SESSION['firstname'] = $user['firstname']; 

// The Redirect Switch
if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
} elseif ($_SESSION['role'] === 'manager') { // Add this specific check
    header('Location: managerr.php'); // Redirect to manager dashboard
} else {
    header('Location: staff.php'); // Redirect for staff
}
exit;
                exit;
            }
        }
    } elseif ($action === 'logout') {
        $user = currentUser();
        if ($user) {
            $conn = dbConnect();
            logAction($conn, $user['id'], $user['username'], 'logout', 'User logged out');
        }
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Handle logout from query string
if (isset($_GET['logout'])) {
    $user = currentUser();
    if ($user) {
        logAction($conn, $user['id'], $user['username'], 'logout', 'User logged out');
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countryside POS - Login</title>
    <link rel="icon" href="assets/cside.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f0e0c;
            --surface: #1a1917;
            --surface2: #242320;
            --accent: #e8a045;
            --border: #3a3835;
            --text: #f0ede8;
            --text3: #6a6560;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-image {
            max-width: 200px;
            height: auto;
            margin-bottom: 8px;
        }
        .logo-sub {
            font-size: 13px;
            color: var(--text3);
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-size: 12px;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-login {
            width: 100%;
            padding: 12px 16px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
            margin-top: 20px;
        }
        .btn-login:hover {
            background: #f0b055;
            transform: translateY(-2px);
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .message.error {
            background: rgba(224, 92, 92, 0.15);
            border: 1px solid rgba(224, 92, 92, 0.3);
            color: #f0a0a0;
        }
        .message.success {
            background: rgba(91, 191, 138, 0.15);
            border: 1px solid rgba(91, 191, 138, 0.3);
            color: #a5e8c5;
        }
        .default-creds {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text3);
            text-align: center;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="logo-section">
            <img src="assets/cside.png" alt="Countryside Logo" class="logo-image">
            <div class="logo-sub">Point of Sale System</div>
        </img>
        

        <?php if ($user): ?>
            <div class="message success">You are already logged in as <?= htmlspecialchars($user['username']) ?> (<?= ucfirst($user['role']) ?>)</div>
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn-login">Logout</button>
            </form>
        <?php else: ?>
            <?php if ($message): ?>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
