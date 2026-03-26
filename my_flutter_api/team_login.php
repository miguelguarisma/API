<?php
session_start();

// CORS headers for Flutter Web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include __DIR__ . "/../db_connect2.php"; // adjust path

$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$team = trim($data['team'] ?? '');

if (!$username || !$password || !$team) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check user in DB
$stmt = $conn->prepare("SELECT id, username, password FROM team_accounts WHERE username=? AND team=? LIMIT 1");
$stmt->bind_param("ss", $username, $team);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();
$conn->close();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['team'] = $team;
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username, password, or team']);
}
?>