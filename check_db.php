<?php
$mysqli = new mysqli("localhost", "root", "", "hvt_petshop");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("DESCRIBE usuarios");
$rows = [];
while($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows, JSON_PRETTY_PRINT);
$mysqli->close();
