<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration for Azure
$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb";
$username = "adminzeeshan";
$password = "Zeeshan@786";

try {
    // Establish a PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check for duplicate username or email
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    if ($stmt->rowCount() > 0) {
        die("Username or email already exists.");
    }

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user data into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword])) {
        // Retrieve the last inserted ID
        $lastInsertId = $conn->lastInsertId();
        //start session to log in
        session_start();
        $_SESSION['user_id'] = $lastInsertId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;

        header("Location: index.php");
        exit();
    } else {
        echo "Registration failed.";
    }
}
?>
