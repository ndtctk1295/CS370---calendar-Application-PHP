<?php
$conn = new mysqli('localhost', 'root', '', 'calendar_app', 3307); // Note the port number 3307
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$title = $_POST['title'];
$description = $_POST['description'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$recurrence = $_POST['recurrence'];

$sql = "UPDATE tasks SET title='$title', description='$description', start_time='$start_time', end_time='$end_time', recurrence='$recurrence' WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
