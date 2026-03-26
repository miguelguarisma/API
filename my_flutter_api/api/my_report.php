<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Connect to DB
$conn = new mysqli("localhost", "root", "", "community_app");
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}
$conn->set_charset("utf8mb4");

// Get user_id safely
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid user_id"
    ]);
    exit;
}

// Fetch reports for this user
$stmt = $conn->prepare("SELECT * FROM community_reports WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        "id" => $row["id"],
        "waste_type" => $row["waste_type"],
        "description" => $row["description"],
        "location" => $row["location"],
        "photo" => $row["photo"], // return the exact filename from DB
        "created_at" => $row["created_at"]
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $reports
]);

$stmt->close();
$conn->close();
?>
