<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_custom_error.log');
error_reporting(E_ALL);

// Trigger a test error
trigger_error("🚨 This is a test error from test_error_log.php");

echo "If you see this, logging should be working.";
