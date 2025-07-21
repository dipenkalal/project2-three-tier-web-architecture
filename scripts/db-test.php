<?php
$host = "dipen-db.cjc8e2a8elx5.us-east-2.rds.amazonaws.com";
$user = "admin";
$pass = "your-db-password";  // Replace with actual password
$db = "dipenncpl";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Connected successfully to RDS '$db' 🌐 Server time: " . date("Y-m-d H:i:s");
?>
