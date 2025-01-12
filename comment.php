<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
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

    // Get input from POST request
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (
        !isset($input['comment_text'], $input['video_id'], $input['user_id']) ||
        empty(trim($input['comment_text'])) ||
        !is_numeric($input['video_id']) ||
        !is_numeric($input['user_id'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $commentText = trim($input['comment_text']);
    $videoId = (int) $input['video_id'];
    $userId = (int) $input['user_id'];

    // Validate comment text length
    if (strlen($commentText) > 250) {
        echo json_encode(['success' => false, 'message' => 'Comment must not exceed 250 characters.']);
        exit;
    }

    // Insert the comment into the database
    $stmt = $pdo->prepare("INSERT INTO comments (video_id, user_id, comment_text) VALUES (:video_id, :user_id, :comment_text)");
    $stmt->bindParam(':video_id', $videoId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':comment_text', $commentText, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit the comment.']);
    }
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    // Handle general errors
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    exit;
}
