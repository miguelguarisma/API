<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include __DIR__ . "/../db_connect2.php";

$team = $_GET['team'] ?? '';

if (!$team) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, barangay, collection_date, status, photo FROM waste_tracking WHERE assigned_to = ?");
$stmt->bind_param("s", $team);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {

    // build photo url
    if (!empty($row['photo'])) {
        $row['photo_url'] = "http://192.168.254.105/Toxtack/uploads/" . $row['photo'];
    } else {
        $row['photo_url'] = null;
    }

    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($data);
?>