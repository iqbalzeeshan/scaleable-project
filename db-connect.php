<?php
$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb"
$username = "adminzeeshan";
$password = "Zeeshan@786";

// Create connection
$conn = new mysqli($servername, $database, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?>