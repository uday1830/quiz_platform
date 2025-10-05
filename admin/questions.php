<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($quiz_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

$quiz_query = "SELECT * FROM quizzes WHERE id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

$quiz = $quiz_result->fetch_assoc();

$questions_query = "SELECT q.*, COUNT(o.id) as option_count
                    FROM questions q
                    LEFT JOIN options o ON q.id = o.question_id
                    WHERE q.quiz_id = ?
                    GROUP BY q.id
                    ORDER BY q.question_order, q.id";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

$page_title = 'Manage Questions';
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
                <div>
                    <h1 class="h2">Manage Questions</h1>
                    <p class="text-muted"><?php echo htmlspecialchars($quiz['title']); ?></p>
                </div>
                <div>
                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/question_form.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">Add Question</a>
                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quizzes.php" class="btn btn-secondary">Back to Quizzes</a>
                </div>
            </div>

            <?php if ($questions_result->num_rows > 0): ?>
                <div class="row">
                    <?php $question_number = 1; ?>
                    <?php while ($question = $questions_result->fetch_assoc()): ?>
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="flex: 1;">
                                            <h5 class="card-title">Question <?php echo $question_number++; ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                            <small class="text-muted">Options: <?php echo $question['option_count']; ?></small>
                                        </div>
                                        <div class="ms-3">
                                            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/options.php?question_id=<?php echo $question['id']; ?>" class="btn btn-sm btn-info">Manage Options</a>
                                            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/question_form.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/delete_question.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No questions added yet. Click "Add Question" to get started.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
