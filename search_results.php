<?php
$mysqli = new mysqli('localhost', 'root', '', 'calendar_app', 3307);

$search_title = $_GET['search_title'];

// Prepare the query to search for tasks by title
$query = "SELECT * FROM tasks WHERE title LIKE ?";
$stmt = $mysqli->prepare($query);
$search_param = '%' . $search_title . '%';
$stmt->bind_param('s', $search_param);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <h1>Search Results for "<?php echo htmlspecialchars($search_title); ?>"</h1>
        <a href="index.php" class="btn btn-secondary mb-3">Back to Timetable</a>
        <ul class="list-group">
            <?php while ($task = $result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <p><strong>Start:</strong> <?php echo htmlspecialchars($task['start_time']); ?></p>
                    <p><strong>End:</strong> <?php echo htmlspecialchars($task['end_time']); ?></p>
                    <p><strong>Recurrence:</strong> <?php echo htmlspecialchars($task['recurrence']); ?></p>
                    <button class="btn btn-primary edit-activity" data-id="<?php echo $task['id']; ?>"
                        data-title="<?php echo htmlspecialchars($task['title']); ?>"
                        data-description="<?php echo htmlspecialchars($task['description']); ?>"
                        data-start="<?php echo htmlspecialchars($task['start_time']); ?>"
                        data-end="<?php echo htmlspecialchars($task['end_time']); ?>"
                        data-recurrence="<?php echo htmlspecialchars($task['recurrence']); ?>" data-bs-toggle="modal"
                        data-bs-target="#editActivityModal">Edit</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Edit Activity Modal -->
    <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editActivityModalLabel">Edit Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editActivityForm" action="update_task.php" method="POST">
                        <input type="hidden" id="editActivityId" name="id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editStartTime" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="editStartTime" name="start_time"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="editEndTime" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="editEndTime" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRecurrence" class="form-label">Recurrence</label>
                            <select class="form-control" id="editRecurrence" name="recurrence" required>
                                <option value="none">None</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button class="btn btn-danger delete-task">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // When the Edit button is clicked, populate the modal with the activity data
            $(document).on('click', '.edit-activity', function () {
                let id = $(this).data('id');
                let title = $(this).data('title');
                let description = $(this).data('description');
                let start = $(this).data('start');
                let end = $(this).data('end');
                let recurrence = $(this).data('recurrence');

                $('#editActivityId').val(id);
                $('#editTitle').val(title);
                $('#editDescription').val(description);
                $('#editStartTime').val(start.replace(' ', 'T'));
                $('#editEndTime').val(end.replace(' ', 'T'));
                $('#editRecurrence').val(recurrence);
            });

            // Handle the form submission to update the task
            $('#editActivityForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'update_task.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#editActivityModal').modal('hide');
                        location.reload(); // Reload the page to show updated data
                    }
                });
            });

            $(document).on('click', '.delete-task', function (e) {
                e.preventDefault();

                let taskId = $('#editActivityId').val();

                if (confirm('Are you sure you want to delete this task?')) {
                    $.ajax({
                        url: 'delete_task.php', // Ensure this points to the correct server-side script
                        method: 'POST',
                        data: { id: taskId },
                        success: function (response) {
                            $('#editActivityModal').modal('hide'); // Hide the modal on success
                            location.reload(); // Reload the page to show updated data
                            alert('Task deleted successfully!');
                        },
                        error: function () {
                            alert('There was an error deleting the task.');
                        }
                    });
                }
            });

        });
    </script>
</body>

</html>

<?php
$stmt->close();
$mysqli->close();
?>