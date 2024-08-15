<?php
$conn = new mysqli('localhost', 'root', '', 'calendar_app', 3307); // Note the port number 3307
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'];
$description = $_POST['description'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$recurrence = $_POST['recurrence'];

$sql = "INSERT INTO tasks (title, description, start_time, end_time, recurrence) VALUES ('$title', '$description', '$start_time', '$end_time', '$recurrence')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>