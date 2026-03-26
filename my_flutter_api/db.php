<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "community_app";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

// important for utf-8 text (emoji, Filipino words, etc.)
$conn->set_charset("utf8mb4");
