<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb";
$username = "adminzeeshan";
$password = "Zeeshan@786";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate POST data
    if (empty($_POST['email']) || empty($_POST['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(['success' => true, 'username' => $user['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
}
?>
