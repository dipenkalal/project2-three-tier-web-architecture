<?php
$host = "your-rds-endpoint";
$username = "your-db-username";
$password = "your-db-password";
$database = "dipen_project";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];
$department = $_POST['department'];

$sql = "INSERT INTO employees (name, email, role, department) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $role, $department);

if ($stmt->execute()) {
    echo "✅ Employee added successfully!";
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
?>