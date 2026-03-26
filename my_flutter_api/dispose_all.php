<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include __DIR__ . '/../db_connect2.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$ids = $data['ids'] ?? [];
$photo = $data['photo'] ?? '';
$filename = $data['filename'] ?? '';

if (empty($ids) || empty($photo) || empty($filename)) {
    echo json_encode(["success"=>false,"message"=>"Missing data"]);
    exit;
}

// Ensure uploads folder exists
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Save the image
$newFilename = uniqid("dispose_") . "_" . basename($filename);
$filePath = $uploadDir . $newFilename;
$imageData = base64_decode($photo);

if (!file_put_contents($filePath, $imageData)) {
    echo json_encode(["success"=>false,"message"=>"Failed to save image"]);
    exit;
}

// Convert IDs to integers
$ids = array_map('intval', $ids);
$ids_string = implode(",", $ids);

// Update all selected rows with the same disposal photo
$sql = "UPDATE waste_tracking SET status='Disposed', disposal_photo=? WHERE id IN ($ids_string)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $newFilename);

if ($stmt->execute()) {
    echo json_encode(["success"=>true,"message"=>"Waste disposed successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>$stmt->error]);
}

$stmt->close();
$conn->close();