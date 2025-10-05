<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;

if ($question_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

$question_query = "SELECT q.*, qz.id as quiz_id, qz.title as quiz_title
                   FROM questions q
                   JOIN quizzes qz ON q.quiz_id = qz.id
                   WHERE q.id = ?";
$stmt = $conn->prepare($question_query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_result = $stmt->get_result();

if ($question_result->num_rows === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

$question = $question_result->fetch_assoc();

$options_query = "SELECT * FROM options WHERE question_id = ? ORDER BY option_order, id";
$stmt = $conn->prepare($options_query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$options_result = $stmt->get_result();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $option_text = trim($_POST['option_text']);
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;
    $option_order = intval($_POST['option_order']);

    if (empty($option_text)) {
        $error = 'Option text is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $question_id, $option_text, $is_correct, $option_order);

        if ($stmt->execute()) {
            $success = 'Option added successfully!';
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/options.php?question_id=$question_id");
            exit();
        } else {
            $error = 'Failed to add option.';
        }
        $stmt->close();
    }
}

$page_title = 'Manage Options';
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
                    <h1 class="h2">Manage Options</h1>
                    <p class="text-muted mb-0"><strong>Quiz:</strong> <?php echo htmlspecialchars($question['quiz_title']); ?></p>
                    <p class="text-muted"><strong>Question:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                </div>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/questions.php?quiz_id=<?php echo $question['quiz_id']; ?>" class="btn btn-secondary">Back to Questions</a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Add New Option</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="option_text" class="form-label">Option Text</label>
                                    <textarea class="form-control" id="option_text" name="option_text" rows="2" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="option_order" class="form-label">Option Order</label>
                                    <input type="number" class="form-control" id="option_order" name="option_order" value="0" min="0">
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_correct" name="is_correct">
                                    <label class="form-check-label" for="is_correct">This is the correct answer</label>
                                </div>

                                <button type="submit" name="add_option" class="btn btn-primary">Add Option</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Existing Options</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($options_result->num_rows > 0): ?>
                                <div class="list-group">
                                    <?php $option_number = 1; ?>
                                    <?php while ($option = $options_result->fetch_assoc()): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div style="flex: 1;">
                                                    <h6>Option <?php echo $option_number++; ?>
                                                        <?php if ($option['is_correct']): ?>
                                                            <span class="badge bg-success">Correct Answer</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="mb-0"><?php echo htmlspecialchars($option['option_text']); ?></p>
                                                </div>
                                                <div class="ms-3">
                                                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/option_form.php?id=<?php echo $option['id']; ?>&question_id=<?php echo $question_id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/delete_option.php?id=<?php echo $option['id']; ?>&question_id=<?php echo $question_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this option?')">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No options added yet. Please add at least one option.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
