<?php
// CORS headers for Flutter Web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Debug: log received data
file_put_contents("php://stderr", print_r($input, true));

$username  = trim($input['username'] ?? '');
$email     = trim($input['email'] ?? '');
$password  = trim($input['password'] ?? '');
$full_name = trim($input['full_name'] ?? '');

// Validate fields
if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
    echo json_encode(["status" => "error", "message" => "Please fill all fields"]);
    exit;
}

// Hash password
$pass_hash = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

// Connect to database
$conn = new mysqli("localhost", "root", "", "community_app");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// Insert user
$sql = "INSERT INTO users (username, email, password, full_name, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $username, $email, $pass_hash, $full_name, $role);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Account registered successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed"]);
}

$stmt->close();
$conn->close();
