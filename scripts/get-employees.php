<?php
$host = "your-rds-endpoint";
$username = "your-db-username";
$password = "your-db-password";
$database = "dipen_project";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "SELECT * FROM employees ORDER BY created_at DESC";
$result = $conn->query($sql);

$employees = array();
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode($employees);
$conn->close();
?>