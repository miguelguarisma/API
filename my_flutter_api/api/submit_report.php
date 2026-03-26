<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Content-Type: application/json");
require_once "../db.php";

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "community_app");
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}

// Sanitize inputs
$user_id     = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$waste_type  = isset($_POST['waste_type']) ? trim($_POST['waste_type']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$location    = isset($_POST['location']) ? trim($_POST['location']) : '';

// Validate required fields
if (
    $user_id <= 0 ||
    $waste_type === '' ||
    $description === '' ||
    $location === ''
) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing fields",
        "debug" => $_POST // remove later if you want
    ]);
    exit;
}

// Handle photo upload
$photoName = null;

if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . "/../uploads/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoName = time() . "_" . basename($_FILES["photo"]["name"]);
    $uploadPath = $uploadDir . $photoName;

    if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadPath)) {
        echo json_encode([
            "status" => "error",
            "message" => "Photo upload failed"
        ]);
        exit;
    }
}

// Insert into DB
$stmt = $conn->prepare(
    "INSERT INTO community_reports 
    (user_id, waste_type, description, location, photo)
    VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "issss",
    $user_id,
    $waste_type,
    $description,
    $location,
    $photoName
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Report submitted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
