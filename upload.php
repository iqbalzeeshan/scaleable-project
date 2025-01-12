<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Database connection
$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb";
$username = "adminzeeshan";
$password = "Zeeshan@786";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $userId = $_SESSION['user_id'];

    // Validate title
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Video title is required.']);
        exit;
    }

    // Validate file
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        exit;
    }

    $file = $_FILES['video'];
    $allowedTypes = ['video/mp4', 'video/avi', 'video/mkv', 'video/mov'];
    $maxSize = 1 * 1024 * 1024; // 1 MB

    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only MP4, AVI, MKV, and MOV allowed.']);
        exit;
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 1 MB.']);
        exit;
    }
    // echo 'now here'; die();

    // Save file to the server
    $uploadsDir = 'uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $uploadsDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
        exit;
    }

    // Save to database
    $stmt = $conn->prepare("INSERT INTO videos (user_id, title, video_url) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $title, $filePath);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Video uploaded successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
