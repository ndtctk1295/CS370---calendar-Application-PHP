<?php
if (isset($_POST['id'])) {
    $taskId = $_POST['id'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'calendar_app', 3307);

    // Check connection
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->bind_param('i', $taskId);

    if ($stmt->execute()) {
        echo 'Task deleted successfully';
    } else {
        echo 'Error deleting task';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request';
}

