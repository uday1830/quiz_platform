<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/admin/dashboard.php');
    } else {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/user/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (md5($password) === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/admin/dashboard.php');
                } else {
                    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/user/dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }

        $stmt->close();
        $conn->close();
    }
}

$page_title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center" style="margin-top: 80px;">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Quiz Platform</h2>
                    <h4 class="text-center mb-4">Login</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                    </form>

                    <div class="text-center">
                        <p>Don't have an account? <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/register.php">Register here</a></p>
                    </div>

                    <hr>

                    <div class="alert alert-info mt-3">
                        <strong>Demo Credentials:</strong><br>
                        Admin: <a href="mailto:admin@quiz.com">admin@quiz.com</a> / admin123<br>
                        User: <a href="mailto:user@quiz.com">user@quiz.com</a> / user123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
