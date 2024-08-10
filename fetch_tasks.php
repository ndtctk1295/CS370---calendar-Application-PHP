<?php
$mysqli = new mysqli('localhost', 'root', '', 'calendar_app', 3307);

$query = "SELECT * FROM tasks";
$result = $mysqli->query($query);
$tasks = [];

while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>
