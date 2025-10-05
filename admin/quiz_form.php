<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$edit_mode = false;
$quiz = ['title' => '', 'description' => '', 'time_limit' => 30, 'is_active' => 1];
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $edit_mode = true;
    $quiz_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $quiz = $result->fetch_assoc();
    } else {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time_limit = intval($_POST['time_limit']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $admin_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || $time_limit <= 0) {
        $error = 'All fields are required and time limit must be greater than 0.';
    } else {
        if ($edit_mode) {
            $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ?, time_limit = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $title, $description, $time_limit, $is_active, $_POST['quiz_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO quizzes (title, description, time_limit, is_active, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiii", $title, $description, $time_limit, $is_active, $admin_id);
        }

        if ($stmt->execute()) {
            $success = $edit_mode ? 'Quiz updated successfully!' : 'Quiz created successfully!';
            if (!$edit_mode) {
                $new_quiz_id = $stmt->insert_id;
                header("Location: " . dirname($_SERVER['PHP_SELF']) . "/questions.php?quiz_id=$new_quiz_id");
                exit();
            }
        } else {
            $error = 'Failed to save quiz.';
        }
        $stmt->close();
    }
}

$page_title = $edit_mode ? 'Edit Quiz' : 'Create Quiz';
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
                <h1 class="h2"><?php echo $edit_mode ? 'Edit Quiz' : 'Create New Quiz'; ?></h1>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/quizzes.php" class="btn btn-secondary">Back to Quizzes</a>
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
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="title" class="form-label">Quiz Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                    <input type="number" class="form-control" id="time_limit" name="time_limit" value="<?php echo $quiz['time_limit']; ?>" min="1" required>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?php echo $quiz['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active (visible to users)</label>
                                </div>

                                <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Quiz' : 'Create Quiz'; ?></button>
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
