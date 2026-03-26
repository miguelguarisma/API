<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include __DIR__ . '/../db_connect2.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? '';
$photo = $data['photo'] ?? '';
$filename = $data['filename'] ?? '';

if (!$id || !$photo) {
    echo json_encode(["success"=>false,"message"=>"Missing data"]);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir,0777,true);
}

$newFilename = uniqid("waste_") . "_" . $filename;
$filePath = $uploadDir . $newFilename;

$imageData = base64_decode($photo);

if (file_put_contents($filePath,$imageData)) {

    $stmt = $conn->prepare("UPDATE waste_tracking SET status='Collected', photo=? WHERE id=?");
    $stmt->bind_param("si",$newFilename,$id);

    if ($stmt->execute()) {
        echo json_encode(["success"=>true,"message"=>"Waste collected"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Database update failed"]);
    }

    $stmt->close();

} else {
    echo json_encode(["success"=>false,"message"=>"Image save failed"]);
}

$conn->close();
?>