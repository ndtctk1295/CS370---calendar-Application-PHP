<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Timetable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .activity {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Timetable</h1>

        <!-- Search Form -->
        <form id="searchForm" action="search_results.php" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search_title" placeholder="Search by Activity Title"
                        required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <form id="addActivityForm">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="title" placeholder="Activity Title" required>
                </div>
                <div class="col-md-3">
                    <input type="datetime-local" class="form-control" name="start_time" required>
                </div>
                <div class="col-md-3">
                    <input type="datetime-local" class="form-control" name="end_time" required>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="recurrence" required>
                        <option value="none">None</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <textarea class="form-control" name="description" placeholder="Activity Description"></textarea>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Add Activity</button>
                </div>
            </div>
        </form>

        <!-- Rest of the Timetable code remains the same -->

        <!-- Previous, Current, Next Week Navigation -->
        <div class="d-flex justify-content-between align-items-center mt-5">
            <button id="prevWeek" class="btn btn-secondary">Previous Week</button>
            <h2 id="weekDisplay">Week of <span id="weekStartDate"></span> - <span id="weekEndDate"></span></h2>
            <button id="nextWeek" class="btn btn-secondary">Next Week</button>
        </div>

        <!-- Timetable Display -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Time</th>
                    <th id="day0">Sun</th>
                    <th id="day1">Mon</th>
                    <th id="day2">Tue</th>
                    <th id="day3">Wed</th>
                    <th id="day4">Thu</th>
                    <th id="day5">Fri</th>
                    <th id="day6">Sat</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 24; $i++): ?>
                    <tr>
                        <td><?php echo $i . ': 00'; ?></td>
                        <?php for ($j = 0; $j < 7; $j++): ?>
                            <td class="time-slot" data-hour="<?php echo $i; ?>" data-day="<?php echo $j; ?>"></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
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
                    <form id="editActivityForm">
                        <input type="hidden" id="editActivityId" name="id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editStartTime" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="editStartTime" name="start_time">
                        </div>
                        <div class="mb-3">
                            <label for="editEndTime" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="editEndTime" name="end_time">
                        </div>
                        <div class="mb-3">
                            <label for="editRecurrence" class="form-label">Recurrence</label>
                            <select class="form-control" id="editRecurrence" name="recurrence">
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            let currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);

            function fetchTasks() {
                $.ajax({
                    url: 'fetch_tasks.php',
                    method: 'GET',
                    success: function (data) {
                        let tasks = JSON.parse(data);
                        renderTimetable(tasks);
                    }
                });

                updateWeekDisplay(currentDate);
            }

            function getStartOfWeek(date) {
                let day = date.getDay();
                let diff = date.getDate() - (day === 0 ? 6 : day - 1); // Adjust when day is Sunday
                return new Date(date.setDate(diff));
            }

            function getEndOfWeek(date) {
                let startOfWeek = getStartOfWeek(new Date(date));
                return new Date(startOfWeek.getTime() + 6 * 24 * 60 * 60 * 1000);
            }

            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function updateWeekDisplay(date) {
                let startOfWeek = getStartOfWeek(new Date(date));
                let endOfWeek = getEndOfWeek(new Date(date));

                $('#weekStartDate').text(formatDate(startOfWeek));
                $('#weekEndDate').text(formatDate(endOfWeek));

                for (let i = 0; i < 7; i++) {
                    let day = new Date(startOfWeek.getTime() + i * 24 * 60 * 60 * 1000);
                    $(`#day${i}`).text(day.toDateString());
                }
            }

            function renderMultiDayTask(task, start, end, startOfWeek, endOfWeek) {
                let current = new Date(start);
                console.log('Rendering multi-day task:', task.title);
                // Ensure the task is rendered only if it falls within the week
                while (current <= end) {
                    // Adjust day index for the timetable
                    let day = current.getDay();
                    day = (day === 0) ? 6 : day - 1; // Adjust Sunday to index 6 and shift others

                    // Determine start and end hours for the current day
                    let startHour = (current.toDateString() === start.toDateString()) ? start.getHours() : 0;
                    let endHour = (current.toDateString() === end.toDateString()) ? end.getHours() : 23;
                    console.log(`Rendering task ${task.title} on day ${day} from hour ${startHour} to ${endHour}`);

                    // Render the task in the appropriate cells for each day it spans
                    renderTask(task, day, startHour, endHour);

                    // Move to the next day
                    current.setDate(current.getDate() + 1);

                    // Stop rendering if we exceed the end of the week
                    if (current > endOfWeek) break;
                }
                let day = current.getDay();
                day = (day === 0) ? 6 : day - 1;
                let startHour = 0;
                let endHour = end.getHours();
                console.log(`Rendering task ${task.title} on day ${day} from hour ${startHour} to ${endHour}`);
                renderTask(task, day, startHour, endHour);
            }

            function renderTimetable(tasks) {
                $('td.time-slot').empty();  // Clear existing tasks from the timetable
                let startOfWeek = getStartOfWeek(currentDate);
                let endOfWeek = getEndOfWeek(currentDate);
                tasks.forEach(task => {
                    let start = new Date(task.start_time);
                    let end = new Date(task.end_time);
                    if (task.recurrence === 'none') {
                        if (start <= endOfWeek && end >= startOfWeek) { // Task spans the current week
                            if (start.toDateString() === end.toDateString()) {
                                // Single-day task
                                renderTask(task, start.getDay(), start.getHours(), end.getHours());
                            } else {
                                // Multi-day task
                                renderMultiDayTask(task, start, end, startOfWeek, endOfWeek);
                            }
                        }
                    } else if (task.recurrence === 'weekly') {
                        renderTask(task, start.getDay(), start.getHours(), end.getHours());
                    } else if (task.recurrence === 'monthly') {
                        renderMonthlyTask(task, start, end, startOfWeek, endOfWeek);
                    }
                });
            }

            function renderTask(task, day, startHour, endHour) {
                for (let hour = startHour; hour <= endHour; hour++) {
                    let cell = $('td[data-hour="' + hour + '"][data-day="' + day + '"]');
                    if (cell.length > 0) {
                        cell.append('<div class="bg-primary text-white p-1 mb-1 activity" data-id="' + task.id + '" data-bs-toggle="modal" data-bs-target="#editActivityModal">' + task.title + '</div>');
                        cell.data(task);
                    } else {
                        console.error('Cell not found for hour:', hour, 'day:', day);
                    }
                }
            }


            function renderMonthlyTask(task, start, end, startOfWeek, endOfWeek) {
                let taskDayOfMonth = start.getDate();
                let startDayOfMonth = startOfWeek.getDate();
                let endDayOfMonth = endOfWeek.getDate();

                // Handle cases where the week spans two months
                let startMonth = startOfWeek.getMonth();
                let endMonth = endOfWeek.getMonth();
                let startYear = startOfWeek.getFullYear();
                let endYear = endOfWeek.getFullYear();

                // If the task falls on the 31st, treat it as the last day of the month
                let lastDayOfTaskMonth = new Date(start.getFullYear(), start.getMonth() + 1, 0).getDate();
                if (taskDayOfMonth > lastDayOfTaskMonth) {
                    taskDayOfMonth = lastDayOfTaskMonth;
                }

                // Check if the task is in the first part of the week in the same month
                if (startMonth === endMonth && taskDayOfMonth >= startDayOfMonth && taskDayOfMonth <= endDayOfMonth) {
                    let taskDate = new Date(startYear, startMonth, taskDayOfMonth);
                    renderTaskOnCorrectDay(task, taskDate, start, end);
                }
                // Check if the task is in the first part of the week but in the previous month
                else if (startMonth !== endMonth && start.getMonth() === startMonth && taskDayOfMonth >= startDayOfMonth) {
                    let taskDate = new Date(startYear, startMonth, taskDayOfMonth);
                    renderTaskOnCorrectDay(task, taskDate, start, end);
                }
                // Check if the task is in the latter part of the week but in the next month
                else if (startMonth !== endMonth && start.getMonth() === endMonth && taskDayOfMonth <= endDayOfMonth) {
                    let taskDate = new Date(endYear, endMonth, taskDayOfMonth);
                    renderTaskOnCorrectDay(task, taskDate, start, end);
                }
            }

            function renderTaskOnCorrectDay(task, taskDate, start, end) {
                let taskDay = taskDate.getDay();

                // Adjust the day to match the Monday-start week
                if (taskDay === 0) {
                    taskDay = 6; // Sunday should be the last day, so it becomes 6
                } else {
                    taskDay = taskDay - 1; // Shift everything else by one day
                }

                renderTask(task, taskDay, start.getHours(), end.getHours());
            }




            $(document).on('click', '.activity', function () {
                let task = $(this).parent().data();
                $('#editActivityId').val(task.id);
                $('#editTitle').val(task.title);
                $('#editDescription').val(task.description);
                $('#editStartTime').val(task.start_time.replace(' ', 'T'));
                $('#editEndTime').val(task.end_time.replace(' ', 'T'));
                $('#editRecurrence').val(task.recurrence);
            });

            $('#addActivityForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_task.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        fetchTasks();
                        $('#addActivityForm')[0].reset();
                    }
                });
            });

            $('#editActivityForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'update_task.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#editActivityModal').modal('hide');
                        fetchTasks();
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
                            fetchTasks(); // Refresh the task list
                            alert('Task deleted successfully!');
                        },
                        error: function () {
                            alert('There was an error deleting the task.');
                        }
                    });
                }
            });

            $('#prevWeek').click(function () {
                currentDate.setDate(currentDate.getDate() - 7);
                fetchTasks();
            });

            $('#nextWeek').click(function () {
                currentDate.setDate(currentDate.getDate() + 7);
                fetchTasks();
            });

            fetchTasks();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>