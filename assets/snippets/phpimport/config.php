<?php

// IMPORTANT:
// This repository must not contain real credentials.
// Provide DB credentials via environment variables or via an ignored file:
// - assets/snippets/phpimport/config.private.php

@include __DIR__ . '/config.private.php';

function tnt_env(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    if ($value === false || $value === '') return $default;
    return $value;
}

// Defaults are safe/local-only.
define('DB_HOST', tnt_env('TNT_DB_HOST', 'localhost'));
define('DB_USER', tnt_env('TNT_DB_USER', ''));
define('DB_PASS', tnt_env('TNT_DB_PASS', ''));
define('DB_NAME', tnt_env('TNT_DB_NAME', ''));

require_once __DIR__ . '/i18n.php';
require "functions.php";

if (DB_USER === '' || DB_NAME === '') {
    echo "<p>Database not configured. Set TNT_DB_* environment variables or create config.private.php.</p>";
    $conn = false;
} else {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        echo "<p>Unable to connect to database</p>";
    } else {
        $message =  "Database connection successful!";
        # write_mysql_log($message, '1', $conn);
    }
}

?>