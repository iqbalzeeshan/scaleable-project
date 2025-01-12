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

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents("php://input"), true);
$video_id = $data['video_id'] ?? null;
$user_id = $data['user_id'] ?? null;

if (!$video_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid video or user ID.']);
    exit;
}

try {
    // Check if the user has already liked the video
    $checkLike = $pdo->prepare("SELECT * FROM likes WHERE video_id = :video_id AND user_id = :user_id");
    $checkLike->execute(['video_id' => $video_id, 'user_id' => $user_id]);

    if ($checkLike->rowCount() > 0) {
        // User has liked before, remove the like
        $removeLike = $pdo->prepare("DELETE FROM likes WHERE video_id = :video_id AND user_id = :user_id");
        $removeLike->execute(['video_id' => $video_id, 'user_id' => $user_id]);
        $message = "You disliked the video.";
    } else {
        // Add a new like
        $addLike = $pdo->prepare("INSERT INTO likes (video_id, user_id) VALUES (:video_id, :user_id)");
        $addLike->execute(['video_id' => $video_id, 'user_id' => $user_id]);
        $message = "You liked the video.";
    }

    // Get the updated like count
    $getLikeCount = $pdo->prepare("SELECT COUNT(*) AS likes_count FROM likes WHERE video_id = :video_id");
    $getLikeCount->execute(['video_id' => $video_id]);
    $likeCount = $getLikeCount->fetch(PDO::FETCH_ASSOC)['likes_count'];

    echo json_encode([
        'success' => true,
        'message' => $message,
        'likes_count' => $likeCount,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
