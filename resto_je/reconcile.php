<?php
require 'config.php';

$conn = dbConnect();
ensureSchema($conn);

try {
    $rows = performDailyReconciliation($conn, null, 'system');
    echo "Daily reconciliation completed for {$rows} payment methods.";
    exit(0);
} catch (Exception $ex) {
    echo 'Reconciliation failed: ' . $ex->getMessage();
    exit(1);
}
