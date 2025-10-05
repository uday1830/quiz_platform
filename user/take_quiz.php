<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if ($attempt_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$attempt_query = "SELECT a.*, q.title, q.time_limit
                  FROM attempts a
                  JOIN quizzes q ON a.quiz_id = q.id
                  WHERE a.id = ? AND a.user_id = ? AND a.status = 'in_progress'";
$stmt = $conn->prepare($attempt_query);
$stmt->bind_param("ii", $attempt_id, $user_id);
$stmt->execute();
$attempt_result = $stmt->get_result();

if ($attempt_result->num_rows === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$attempt = $attempt_result->fetch_assoc();
$remaining_seconds = $attempt['remaining_time'];
if ($remaining_seconds === null) {
    $remaining_seconds = $attempt['time_limit'] * 60;  // default full time
}

$questions_query = "SELECT q.*, aa.selected_option_id, aa.marked_for_review
                    FROM questions q
                    JOIN attempt_answers aa ON q.id = aa.question_id
                    WHERE aa.attempt_id = ?
                    ORDER BY q.question_order, q.id";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$questions_result = $stmt->get_result();

$questions = [];
while ($question = $questions_result->fetch_assoc()) {
    $options_query = "SELECT * FROM options WHERE question_id = ? ORDER BY option_order, id";
    $stmt_opt = $conn->prepare($options_query);
    $stmt_opt->bind_param("i", $question['id']);
    $stmt_opt->execute();
    $options_result = $stmt_opt->get_result();

    $question['options'] = [];
    while ($option = $options_result->fetch_assoc()) {
        $question['options'][] = $option;
    }

    $questions[] = $question;
    $stmt_opt->close();
}

$page_title = $attempt['title'];
include __DIR__ . '/../includes/header.php';
?>

<style>
.quiz-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}
</style>

<div class="quiz-container">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($attempt['title']); ?></h5>

                    <div class="timer-display mb-3" id="timer">
                        <span id="timer-display"><?php echo $attempt['time_limit']; ?>:00</span>
                    </div>

                    <div class="stats-card">
                        <div class="row text-center">
                            <div class="col-6 stats-item">
                                <span class="number" id="total-questions">0</span>
                                <span class="label">Total</span>
                            </div>
                            <div class="col-6 stats-item">
                                <span class="number" id="answered-count">0</span>
                                <span class="label">Answered</span>
                            </div>
                            <div class="col-6 stats-item">
                                <span class="number" id="unanswered-count">0</span>
                                <span class="label">Unanswered</span>
                            </div>
                            <div class="col-6 stats-item">
                                <span class="number" id="review-count">0</span>
                                <span class="label">For Review</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="legend-item">
                            <div class="legend-box answered"></div>
                            <span>Answered</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box unanswered"></div>
                            <span>Unanswered</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box review"></div>
                            <span>Review</span>
                        </div>
                    </div>

                    <button class="btn btn-success w-100 mt-3" id="submit-quiz-btn">Submit Quiz</button>
                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php" class="btn btn-secondary w-100 mt-2">Exit (without saving)</a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Question Navigation</h5>
                    <div class="question-palette" id="question-palette">

                    </div>
                </div>
            </div>

            <div class="card" id="question-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Question <span id="current-question-number">1</span> of <span id="total-questions-text">0</span></h5>
                        <button class="btn btn-mark-review" id="mark-review-btn">
                            <span id="mark-review-text">Mark for Review</span>
                        </button>
                    </div>
                    

                    <div class="question-text" id="question-text"></div>

                    <div id="options-container"></div>
                        <button class="btn btn-outline-secondary mt-3" id="clear-option-btn">Clear Selected Option</button>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary" id="prev-btn" disabled>Previous</button>
                        <button class="btn btn-primary" id="next-btn">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit the quiz?</p>
                <p class="text-danger"><strong>You can't change answers after submission.</strong></p>
                <div id="submit-summary" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirm-submit-btn">Confirm Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
const attemptId = <?php echo $attempt_id; ?>;
const timeLimit = <?php echo $attempt['time_limit'] * 60; ?>;
const questions = <?php echo json_encode($questions); ?>;

let currentQuestionIndex = 0;
let startTime = Date.now();
let timerInterval;
let timeRemaining = <?php echo $remaining_seconds; ?>;

function initQuiz() {
    document.getElementById('total-questions').textContent = questions.length;
    document.getElementById('total-questions-text').textContent = questions.length;

    renderQuestionPalette();
    renderQuestion();
    updateStats();
    startTimer();
}

function renderQuestionPalette() {
    const palette = document.getElementById('question-palette');
    palette.innerHTML = '';

    questions.forEach((q, index) => {
        const btn = document.createElement('button');
        btn.className = 'question-btn';
        btn.textContent = index + 1;
        btn.onclick = () => goToQuestion(index);
        palette.appendChild(btn);
    });

    updatePaletteColors();
}

function updatePaletteColors() {
    const buttons = document.querySelectorAll('.question-btn');

    buttons.forEach((btn, index) => {
        btn.classList.remove('answered', 'unanswered', 'review', 'current');

        const question = questions[index];

        if (index === currentQuestionIndex) {
            btn.classList.add('current');
        }

        if (question.marked_for_review == 1) {
            btn.classList.add('review');
        } else if (question.selected_option_id) {
            btn.classList.add('answered');
        } else {
            btn.classList.add('unanswered');
        }
    });
}

function renderQuestion() {
    const question = questions[currentQuestionIndex];

    document.getElementById('current-question-number').textContent = currentQuestionIndex + 1;
    document.getElementById('question-text').textContent = question.question_text;

    const optionsContainer = document.getElementById('options-container');
    optionsContainer.innerHTML = '';

    question.options.forEach(option => {
        const div = document.createElement('div');
        div.className = 'option-item';

        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'option';
        input.id = 'option-' + option.id;
        input.value = option.id;
        input.style.display = 'none';

        if (question.selected_option_id == option.id) {
            input.checked = true;
        }

        input.onchange = () => selectOption(option.id);

        const label = document.createElement('label');
        label.className = 'option-label';
        label.htmlFor = 'option-' + option.id;
        label.textContent = option.option_text;

        div.appendChild(input);
        div.appendChild(label);
        optionsContainer.appendChild(div);
    });

    const markBtn = document.getElementById('mark-review-btn');
    if (question.marked_for_review == 1) {
        markBtn.classList.add('active');
        document.getElementById('mark-review-text').textContent = 'Marked for Review';
    } else {
        markBtn.classList.remove('active');
        document.getElementById('mark-review-text').textContent = 'Mark for Review';
    }

    document.getElementById('prev-btn').disabled = currentQuestionIndex === 0;
    //document.getElementById('next-btn').textContent = currentQuestionIndex === questions.length - 1 ? 'Finish' : 'Next';
     document.getElementById('next-btn').textContent =  'Next';
   

        document.getElementById('next-btn').disabled=currentQuestionIndex === questions.length - 1;



    updatePaletteColors();
}

function selectOption(optionId) {
    questions[currentQuestionIndex].selected_option_id = optionId;
    saveAnswer();
    updateStats();
    updatePaletteColors();
}

function toggleMarkReview() {
    const question = questions[currentQuestionIndex];
    question.marked_for_review = question.marked_for_review == 1 ? 0 : 1;

    const markBtn = document.getElementById('mark-review-btn');
    if (question.marked_for_review == 1) {
        markBtn.classList.add('active');
        document.getElementById('mark-review-text').textContent = 'Marked for Review';
    } else {
        markBtn.classList.remove('active');
        document.getElementById('mark-review-text').textContent = 'Mark for Review';
    }

    saveAnswer();
    updateStats();
    updatePaletteColors();
}

function saveAnswer() {
    const question = questions[currentQuestionIndex];

    const formData = new FormData();
    formData.append('attempt_id', attemptId);
    formData.append('question_id', question.id);
    formData.append('option_id', question.selected_option_id || '');
    formData.append('marked_for_review', question.marked_for_review);
    formData.append('remaining_time', timeRemaining);

    fetch('<?php echo dirname($_SERVER['PHP_SELF']); ?>/save_answer.php', {
        method: 'POST',
        body: formData
    });
}

function updateStats() {
    let answered = 0;
    let review = 0;

    questions.forEach(q => {
        if (q.marked_for_review == 1) {
            review++;
        } else if (q.selected_option_id) {
            answered++;
        }
    });

    const unanswered = questions.length - answered - review;

    document.getElementById('answered-count').textContent = answered;
    document.getElementById('unanswered-count').textContent = unanswered;
    document.getElementById('review-count').textContent = review;
}

function goToQuestion(index) {
    currentQuestionIndex = index;
    renderQuestion();
}

function nextQuestion() {
    if (currentQuestionIndex < questions.length - 1) {
        currentQuestionIndex++;
        renderQuestion();
    }
}

function prevQuestion() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        renderQuestion();
    }
}

function startTimer() {
    updateTimerDisplay();

    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            submitQuiz();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    const timerEl = document.getElementById('timer');
    const displayEl = document.getElementById('timer-display');
    displayEl.textContent = display;

    timerEl.classList.remove('warning', 'danger');
    if (timeRemaining <= 60) {
        timerEl.classList.add('danger');
    } else if (timeRemaining <= 300) {
        timerEl.classList.add('warning');
    }
}

function showSubmitModal() {
    let answered = 0;
    let review = 0;

    questions.forEach(q => {
        if (q.marked_for_review == 1) {
            review++;
        } else if (q.selected_option_id) {
            answered++;
        }
    });

    const unanswered = questions.length - answered - review;

    const summary = `
        <div class="alert alert-info">
            <strong>Quiz Summary:</strong><br>
            Total Questions: ${questions.length}<br>
            Answered: ${answered}<br>
            Unanswered: ${unanswered}<br>
            Marked for Review: ${review}
        </div>
    `;

    document.getElementById('submit-summary').innerHTML = summary;

    const modal = new bootstrap.Modal(document.getElementById('submitModal'));
    modal.show();
}
function disableBeforeUnload() {
    window.onbeforeunload = null;
}
function submitQuiz() {
     disableBeforeUnload();
    clearInterval(timerInterval);

    const timeTaken = Math.floor((Date.now() - startTime) / 1000);

    const formData = new FormData();
    formData.append('attempt_id', attemptId);
    formData.append('time_taken', timeTaken);

    fetch('<?php echo dirname($_SERVER['PHP_SELF']); ?>/submit_quiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo dirname($_SERVER['PHP_SELF']); ?>/leaderboard.php?quiz_id=' + data.quiz_id;
        } else {
            alert('Error submitting quiz');
        }
    });
}

document.getElementById('next-btn').onclick = nextQuestion;
document.getElementById('prev-btn').onclick = prevQuestion;
document.getElementById('mark-review-btn').onclick = toggleMarkReview;
document.getElementById('submit-quiz-btn').onclick = showSubmitModal;
document.getElementById('confirm-submit-btn').onclick = submitQuiz;
document.getElementById('clear-option-btn').addEventListener('click', () => {
    const question = questions[currentQuestionIndex];
    question.selected_option_id = null;

    // Uncheck all radio buttons for current question
    document.querySelectorAll('input[name="option"]').forEach(input => {
        input.checked = false;
    });

    // Save the cleared state to server
    saveAnswer();

    // Update UI and stats
    updateStats();
    updatePaletteColors();
});


// window.onbeforeunload = function() {
//     return "Are you sure you want to leave? Your progress will be saved but the timer will continue.";
// };
// window.addEventListener('onbeforeunload', () => {
//     navigator.sendBeacon('<?php echo dirname($_SERVER['PHP_SELF']); ?>/save_time.php', new URLSearchParams({
//         attempt_id: attemptId,
//         remaining_time: timeRemaining
//     }).toString());
// });

window.addEventListener('beforeunload', () => {
    const data = new URLSearchParams({
        attempt_id: attemptId,
        remaining_time: timeRemaining
    });

    navigator.sendBeacon('<?php echo dirname($_SERVER['PHP_SELF']); ?>/save_time.php', data);
});
setInterval(() => {
    const data = new URLSearchParams({
        attempt_id: attemptId,
        remaining_time: timeRemaining
    });
    navigator.sendBeacon('<?php echo dirname($_SERVER['PHP_SELF']); ?>/save_time.php', data);
}, 10000);

initQuiz();
</script>

<?php
$stmt->close();
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
