<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;
$option_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($question_id === 0 || $option_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM options WHERE id = ? AND question_id = ?");
$stmt->bind_param("ii", $option_id, $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/options.php?question_id=$question_id");
    exit();
}

$option = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $option_text = trim($_POST['option_text']);
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;
    $option_order = intval($_POST['option_order']);

    if (empty($option_text)) {
        $error = 'Option text is required.';
    } else {
        $stmt = $conn->prepare("UPDATE options SET option_text = ?, is_correct = ?, option_order = ? WHERE id = ?");
        $stmt->bind_param("siii", $option_text, $is_correct, $option_order, $option_id);

        if ($stmt->execute()) {
            $success = 'Option updated successfully!';
            $option['option_text'] = $option_text;
            $option['is_correct'] = $is_correct;
            $option['option_order'] = $option_order;
        } else {
            $error = 'Failed to update option.';
        }
        $stmt->close();
    }
}

$page_title = 'Edit Option';
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
                <h1 class="h2">Edit Option</h1>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/options.php?question_id=<?php echo $question_id; ?>" class="btn btn-secondary">Back to Options</a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
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
                                    <textarea class="form-control" id="option_text" name="option_text" rows="2" required><?php echo htmlspecialchars($option['option_text']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="option_order" class="form-label">Option Order</label>
                                    <input type="number" class="form-control" id="option_order" name="option_order" value="<?php echo $option['option_order']; ?>" min="0">
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_correct" name="is_correct" <?php echo $option['is_correct'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_correct">This is the correct answer</label>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Option</button>
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
