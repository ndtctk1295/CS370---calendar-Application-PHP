<?php
$mysqli = new mysqli('localhost', 'root', '', 'calendar_app', 3307);

$id = $_POST['id'];
$title = $_POST['title'];
$description = $_POST['description'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$recurrence = $_POST['recurrence'];

// Prepare the update query
$query = "UPDATE tasks SET title = ?, description = ?, start_time = ?, end_time = ?, recurrence = ? WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sssssi', $title, $description, $start_time, $end_time, $recurrence, $id);

if ($stmt->execute()) {
    echo "Task updated successfully";
} else {
    echo "Error updating task: " . $mysqli->error;
}

$stmt->close();
$mysqli->close();
?>
