<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$stats_query = "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM quizzes WHERE is_active = 1) as total_quizzes,
                (SELECT COUNT(*) FROM questions) as total_questions,
                (SELECT COUNT(*) FROM attempts WHERE status = 'completed') as total_attempts";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$recent_attempts_query = "SELECT a.*, u.name, q.title
                          FROM attempts a
                          JOIN users u ON a.user_id = u.id
                          JOIN quizzes q ON a.quiz_id = q.id
                          WHERE a.status = 'completed'
                          ORDER BY a.submitted_at DESC
                          LIMIT 10";
$recent_attempts_result = $conn->query($recent_attempts_query);

$page_title = 'Admin Dashboard';
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
                        <a class="nav-link active" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quizzes.php">Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/attempts.php">View Attempts</a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2><?php echo $stats['total_users']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Active Quizzes</h5>
                            <h2><?php echo $stats['total_quizzes']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Questions</h5>
                            <h2><?php echo $stats['total_questions']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Completed Attempts</h5>
                            <h2><?php echo $stats['total_attempts']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mb-3">Recent Quiz Attempts</h3>
            <?php if ($recent_attempts_result->num_rows > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Quiz</th>
                                        <th>Score</th>
                                        <th>Correct/Total</th>
                                        <th>Time Taken</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attempt = $recent_attempts_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['name']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['title']); ?></td>
                                            <td><strong><?php echo number_format($attempt['score'], 2); ?>%</strong></td>
                                            <td><?php echo $attempt['correct_answers'] . '/' . $attempt['total_questions']; ?></td>
                                            <td><?php echo gmdate("i:s", $attempt['time_taken']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($attempt['submitted_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No quiz attempts yet.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
