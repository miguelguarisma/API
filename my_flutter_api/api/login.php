<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // hide errors in output

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill all fields"]);
    exit;
}

// Connect to DB
$conn = @new mysqli("localhost", "root", "", "community_app");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// Fetch user by email
$sql = "SELECT id, username, email, password, full_name, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => $user['id'],
            "username" => $user['username'],
            "email" => $user['email'],
            "full_name" => $user['full_name'],
            "role" => $user['role'],
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Incorrect password"]);
}

$stmt->close();
$conn->close();
