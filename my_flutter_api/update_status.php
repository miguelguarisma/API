<?php
// Allow Flutter Web to POST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include DB connection
include __DIR__ . "/../db_connect2.php"; // make sure path is correct

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || !isset($data['id']) || !isset($data['status'])) {
    echo json_encode(["error" => "Missing data"]);
    exit;
}

$id = $data['id'];
$status = $data['status'];

// Update query
$stmt = $conn->prepare("UPDATE waste_tracking SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "id" => $id, "status" => $status]);
} else {
    echo json_encode(["error" => $stmt->error]);
}
?>
