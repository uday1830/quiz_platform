<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$quizzes_query = "SELECT q.*, u.name as created_by_name,
                  (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
                  FROM quizzes q
                  JOIN users u ON q.created_by = u.id
                  ORDER BY q.created_at DESC";
$quizzes_result = $conn->query($quizzes_query);

$page_title = 'Manage Quizzes';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php">Quiz Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo dirname(dirname($_SERVER['PHP_SELF'])); ?>/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-light sidebar" style="min-height: calc(100vh - 56px);">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quizzes.php">Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/attempts.php">View Attempts</a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Quizzes</h1>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quiz_form.php" class="btn btn-primary">Create New Quiz</a>
            </div>

            <?php if ($quizzes_result->num_rows > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Questions</th>
                                        <th>Time Limit</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($quiz['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(mb_strimwidth($quiz['description'], 0, 50, '...')); ?></td>
                                            <td><?php echo $quiz['question_count']; ?></td>
                                            <td><?php echo $quiz['time_limit']; ?> min</td>
                                            <td>
                                                <?php if ($quiz['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($quiz['created_by_name']); ?></td>
                                            <td>
                                                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info">Questions</a>
                                                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quiz_form.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/delete_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No quizzes created yet. Click "Create New Quiz" to get started.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
