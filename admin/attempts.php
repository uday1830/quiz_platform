<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$filter_quiz = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$filter_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$quizzes_query = "SELECT id, title FROM quizzes ORDER BY title";
$quizzes_result = $conn->query($quizzes_query);

$users_query = "SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name";
$users_result = $conn->query($users_query);

$attempts_query = "SELECT a.*, u.name as user_name, u.email, q.title as quiz_title
                   FROM attempts a
                   JOIN users u ON a.user_id = u.id
                   JOIN quizzes q ON a.quiz_id = q.id
                   WHERE a.status = 'completed'";

if ($filter_quiz > 0) {
    $attempts_query .= " AND a.quiz_id = " . $filter_quiz;
}

if ($filter_user > 0) {
    $attempts_query .= " AND a.user_id = " . $filter_user;
}

$attempts_query .= " ORDER BY a.submitted_at DESC LIMIT 100";
$attempts_result = $conn->query($attempts_query);

$page_title = 'View Attempts';
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
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quizzes.php">Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/attempts.php">View Attempts</a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz Attempts</h1>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filters</h5>
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="quiz_id" class="form-label">Quiz</label>
                            <select class="form-select" id="quiz_id" name="quiz_id">
                                <option value="0">All Quizzes</option>
                                <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
                                    <option value="<?php echo $quiz['id']; ?>" <?php echo $filter_quiz == $quiz['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="0">All Users</option>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $filter_user == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/attempts.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($attempts_result->num_rows > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Quiz</th>
                                        <th>Score</th>
                                        <th>Correct/Total</th>
                                        <th>Time Taken</th>
                                        <th>Started At</th>
                                        <th>Submitted At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                            <td>
                                                <?php
                                                $score = $attempt['score'];
                                                $color = 'text-danger';
                                                if ($score >= 75) $color = 'text-success';
                                                elseif ($score >= 50) $color = 'text-warning';
                                                ?>
                                                <strong><span class="<?php echo $color; ?>"><?php echo number_format($score, 2); ?>%</span></strong>
                                            </td>
                                            <td><?php echo $attempt['correct_answers'] . '/' . $attempt['total_questions']; ?></td>
                                            <td><?php echo gmdate("i:s", $attempt['time_taken']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($attempt['started_at'])); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($attempt['submitted_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No attempts found matching your filters.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
