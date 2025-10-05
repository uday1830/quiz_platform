<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($quiz_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$quiz_query = "SELECT title FROM quizzes WHERE id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$quiz = $quiz_result->fetch_assoc();

$leaderboard_query = "SELECT a.*, u.name, u.email,
                      RANK() OVER (ORDER BY a.score DESC, a.time_taken ASC) as ranking
                      FROM attempts a
                      JOIN users u ON a.user_id = u.id
                      WHERE a.quiz_id = ? AND a.status = 'completed'
                      ORDER BY a.score DESC, a.time_taken ASC
                      LIMIT 100";
$stmt = $conn->prepare($leaderboard_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$leaderboard_result = $stmt->get_result();

$page_title = 'Leaderboard';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php">Quiz Platform</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">Welcome,
                        <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        href="<?php echo dirname(dirname($_SERVER['PHP_SELF'])); ?>/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Leaderboard</h2>
            <p class="text-muted">
                <?php echo htmlspecialchars($quiz['title']); ?>
            </p>
        </div>
        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php" class="btn btn-primary">Back to
            Dashboard</a>
    </div>

    <?php if ($leaderboard_result->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover leaderboard-table">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Rank</th>
                            <th>Name</th>
                             <th>Response Sheet</th>
                            <th>Score</th>
                            <th>Correct Answers</th>
                            <th>Total Questions</th>
                            <th>Time Taken</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $leaderboard_result->fetch_assoc()): ?>
                        <tr class="<?php echo $row['user_id'] == $user_id ? 'current-user' : ''; ?>">
                            <td>
                                <?php
                                        $rank = $row['ranking'];
                                        $badge_class = 'default';
                                        if ($rank == 1) $badge_class = 'gold';
                                        elseif ($rank == 2) $badge_class = 'silver';
                                        elseif ($rank == 3) $badge_class = 'bronze';
                                        ?>
                                <span class="rank-badge <?php echo $badge_class; ?>">
                                    <?php echo $rank; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($row['user_id'] == $user_id): ?>
                                <span class="badge bg-success ms-2">You</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_response.php?attempt_id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-primary">View Response</a>
                            </td>
                            <td><strong>
                                    <?php echo number_format($row['score'], 2); ?>%
                                </strong></td>
                            <td>
                                <?php echo $row['correct_answers']; ?>
                            </td>
                            <td>
                                <?php echo $row['total_questions']; ?>
                            </td>
                            <td>
                                <?php echo gmdate("i:s", $row['time_taken']); ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y H:i', strtotime($row['submitted_at'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No attempts yet for this quiz.</div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>