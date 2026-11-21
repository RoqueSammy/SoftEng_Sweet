<?php 
date_default_timezone_set("Asia/Manila");
$db_server = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASSWORD") ?: "";
$db_name = getenv("DB_NAME") ?: "softecoms";
$db_port = (int)(getenv("DB_PORT") ?: 3306);

$conn = mysqli_init();
$connectionFlags = 0;
if (strtolower((string)getenv("DB_SSL")) === "true") {
    mysqli_ssl_set($conn, null, null, null, null, null);
    $connectionFlags = MYSQLI_CLIENT_SSL;
}

mysqli_real_connect($conn, $db_server, $db_user, $db_pass, $db_name, $db_port, null, $connectionFlags);

if (!$conn) {
    error_log("MySQL connection failed: " . mysqli_connect_error());
    http_response_code(500);
    exit("The store is temporarily unavailable. Please try again later.");
}

mysqli_set_charset($conn, "utf8mb4");
?>
