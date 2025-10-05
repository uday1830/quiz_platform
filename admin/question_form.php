<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$edit_mode = false;
$question = ['question_text' => '', 'question_order' => 0];
$error = '';
$success = '';

if ($quiz_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

if (isset($_GET['id'])) {
    $edit_mode = true;
    $question_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->bind_param("ii", $question_id, $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();
    } else {
        header("Location: " . dirname($_SERVER['PHP_SELF']) . "/questions.php?quiz_id=$quiz_id");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text']);
    $question_order = intval($_POST['question_order']);

    if (empty($question_text)) {
        $error = 'Question text is required.';
    } else {
        if ($edit_mode) {
            $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_order = ? WHERE id = ?");
            $stmt->bind_param("sii", $question_text, $question_order, $_POST['question_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, question_order) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $quiz_id, $question_text, $question_order);
        }

        if ($stmt->execute()) {
            $success = $edit_mode ? 'Question updated successfully!' : 'Question added successfully!';
            if (!$edit_mode) {
                $new_question_id = $stmt->insert_id;
                header("Location: " . dirname($_SERVER['PHP_SELF']) . "/options.php?question_id=$new_question_id");
                exit();
            }
        } else {
            $error = 'Failed to save question.';
        }
        $stmt->close();
    }
}

$page_title = $edit_mode ? 'Edit Question' : 'Add Question';
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
                <h1 class="h2"><?php echo $edit_mode ? 'Edit Question' : 'Add New Question'; ?></h1>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary">Back to Questions</a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="question_text" class="form-label">Question Text</label>
                                    <textarea class="form-control" id="question_text" name="question_text" rows="4" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="question_order" class="form-label">Question Order</label>
                                    <input type="number" class="form-control" id="question_order" name="question_order" value="<?php echo $question['question_order']; ?>" min="0">
                                    <small class="form-text text-muted">Leave as 0 for default ordering</small>
                                </div>

                                <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Question' : 'Add Question'; ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
