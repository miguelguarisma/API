<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once __DIR__ . "/../db_connect2.php";

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$password = trim($data["password"] ?? "");
$team     = trim($data["team"] ?? "");

if (!$username || !$password || !$team) {
    echo json_encode([
        "success" => false,
        "message" => "Missing fields"
    ]);
    exit;
}

// check existing
$stmt = $conn->prepare("SELECT id FROM team_accounts WHERE username=? AND team=? LIMIT 1");
$stmt->bind_param("ss", $username, $team);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Username already exists"
    ]);
    exit;
}

$stmt->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO team_accounts (username,password,team) VALUES (?,?,?)");
$stmt->bind_param("sss", $username, $hashed, $team);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Account created"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "DB insert failed"
    ]);
}

$stmt->close();
$conn->close();