<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli('localhost', 'root', 'root', 'quizhub', 8889);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'learner') {
    header("Location: logIn.php?error=access_denied");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$user_stmt = $mysqli->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM `user` WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($firstName, $lastName, $email, $photo);
$user_stmt->fetch();
$user_stmt->close();

// topics list
$topics = [];
if ($res = $mysqli->query("SELECT topicName FROM Topic ORDER BY topicName")) {
    while ($r = $res->fetch_assoc()) {
        $topics[] = $r['topicName'];
    }
    $res->free();
}

// Recommended Questions
$recs = [];
$r_stmt = $mysqli->prepare("SELECT rq.question, rq.questionFigureFileName, rq.answerA, rq.answerB, rq.answerC, rq.answerD, rq.correctAnswer, rq.status, rq.comments,
    t.topicName, u.firstName, u.lastName, u.photoFileName 
    FROM RecommendedQuestion rq
    JOIN Quiz q ON rq.quizID = q.id
    JOIN Topic t ON q.topicID = t.id
    JOIN `user` u ON q.educatorID = u.id
    WHERE rq.learnerID = ?");
$r_stmt->bind_param("i", $user_id);
$r_stmt->execute();
$res = $r_stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recs[] = $row;
}
$r_stmt->close();

$mysqli->close();

function choiceClass($label, $correct) {
    return $label === $correct ? 'choice marked' : 'choice';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Learner Homepage</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
*{margin:0; padding:0; box-sizing:border-box;}
body {
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg,#f8f9ff 0%,#e8eaf6 50%,#d1c4e9 100%);
    min-height:100vh;
    padding:20px 20px 0;
}
.container {
    max-width:1400px;
    margin:0 auto;
    background:white;
    border-radius:15px;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
    overflow:hidden;
}
.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
    padding:30px 40px;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:relative;
}
.header .logo {
    font-weight:700;
    font-size:28px;
    display:flex;
    align-items:center;
    gap:12px;
}
.header .logo img {
    max-height:45px;
}
.welcome-text {
    font-size:28px;
    font-weight:700;
}
.logout-btn {
    background: rgba(255,255,255,0.2);
    color:white;
    padding:12px 24px;
    border-radius:25px;
    text-decoration:none;
    font-weight:600;
}
.content {
    padding:40px;
}
.user-info {
    background: linear-gradient(135deg,#f8f9ff 0%,#e8eaf6 100%);
    border-radius:15px;
    padding:35px;
    margin-bottom:40px;
    display:flex;
    align-items:center;
    gap:30px;
    border:1px solid #e1e5e9;
}
.user-photo {
    width:100px; height:100px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #667eea;
}
.user-details h3 {
    color:#2c3e50;
    margin-bottom:12px;
    font-size:24px;
}
.section-title {
    font-size:28px;
    color:#2c3e50;
    margin-bottom:30px;
    border-bottom:4px solid #667eea;
    padding-bottom:15px;
    position:relative;
}
.recommend-btn {
    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding:10px 25px;
    border-radius:25px;
    color:white;
    text-decoration:none;
    font-weight:600;
    margin-bottom:20px;
    display:inline-block;
}
.quiz-table, .recommended-table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:40px;
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}
.quiz-table th, .recommended-table th {
    background: linear-gradient(90deg,#667eea 0%,#764ba2 100%);
    color:white;
    padding:20px 18px;
    text-align:left;
    font-weight:700;
}
.quiz-table td, .recommended-table td {
    padding:20px 18px;
    border-bottom:1px solid #e9ecef;
    vertical-align:top;
    font-size:15px;
}
.topic-link {
    color:#667eea;
    text-decoration:none;
    font-weight:600;
}
.choice {
    padding:8px 12px;
    border-radius:6px;
    margin-bottom:6px;
    font-size:14px;
    font-weight:500;
    background:#f1f1f1;
    border-left:4px solid #667eea;
}
.choice.marked {
    background-color:#667eea !important;
    color:white !important;
    border-left-color:#764ba2 !important;
}
.no-data {
    text-align:center;
    font-style:italic;
    padding:30px;
    color:#6c757d;
    font-size:16px;
}
.question-img {
    max-width:180px;
    max-height:120px;
    margin-bottom:8px;
    border-radius:8px;
    object-fit:cover;
}
.footer{
    width: 100%;
    padding: 20px 0;
    text-align: left;
    color: #fff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
    margin-top: 40px;
    padding-bottom:0;
}
.contacts{
    padding-left:20px;
    padding-bottom:10px;
}
.phone {
    display: flex;
    align-items: center;
    padding-top:10px;
    padding-bottom:10px;
}
#phone-icon {
    height: 1.25em;
    width: 1.25em;
    margin-right: 0.1875em;
}
.email {
    display: flex;
    align-items: center;
}
#email-icon {
    height: 1.25em;
    width: 1.25em;
    margin-right: 0.3125em;
}
.social-media {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 15px;
}
.social-media img {
    width: 30px;
    height: auto;
}
.copyright {
    text-align: center;
    padding: 20px;
}
</style>

</head>
<body>
<div class="container">

    <header class="header">
        <div class="logo">
            <img src="images/logo1.png" alt="Logo" />
            QuizHub
        </div>
        <div class="welcome-text">Welcome <?php echo htmlspecialchars($firstName); ?></div>
        <a href="logout.php" class="logout-btn">Log-out</a>
    </header>

    <main class="content">

        <section class="user-info">
            <?php if (!empty($photo)) : ?>
                <img src="uploads/<?php echo htmlspecialchars($photo); ?>" alt="User Photo" class="user-photo" />
            <?php else :
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)); ?>
                <div class="user-photo" style="display:flex; justify-content:center; align-items:center; font-size:30px; color:#667eea; font-weight:bold;">
                    <?php echo $initials; ?>
                </div>
            <?php endif; ?>

            <div class="user-details">
                <h3><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h3>
                <p><strong>First Name:</strong> <?= htmlspecialchars($firstName); ?></p>
                <p><strong>Last Name:</strong> <?= htmlspecialchars($lastName); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email); ?></p>
            </div>
        </section>

        <!-- QUIZZES SECTION -->
        <section>
            <h2 class="section-title">Available Quizzes</h2>

            <label for="topicFilter">Filter by topic:</label>
            <select id="topicFilter" name="topicFilter">
                <option value="all">All Topics</option>
                <?php foreach ($topics as $topic): ?>
                    <option value="<?= htmlspecialchars($topic); ?>"><?= htmlspecialchars($topic); ?></option>
                <?php endforeach; ?>
            </select>

            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Educator</th>
                        <th># Questions</th>
                        <th>Take Quiz</th>
                    </tr>
                </thead>
                <tbody id="quizTableBody">
                </tbody>
            </table>
        </section>

        <section>
            <h2 class="section-title">My Recommended Questions</h2>
            <a href="recommend_question.php" class="recommend-btn">+ Recommend a New Question</a>

            <?php if (empty($recs)) : ?>
                <div class="no-data">No recommended questions yet.</div>
            <?php else : ?>
                <table class="recommended-table">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Educator</th>
                            <th>Question</th>
                            <th>Status</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recs as $r) : ?>
                            <tr>
                                <td><?= htmlspecialchars($r['topicName']); ?></td>
                                <td>
                                    <img src="uploads/<?= htmlspecialchars($r['photoFileName']); ?>" 
                                         style="width:40px; height:40px; border-radius:50%;" />
                                    <?= htmlspecialchars($r['firstName'] . ' ' . $r['lastName']); ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['questionFigureFileName'])) : ?>
                                        <img src="uploads/<?= htmlspecialchars($r['questionFigureFileName']); ?>" class="question-img">
                                    <?php endif; ?>

                                    <p>Q: <?= htmlspecialchars($r['question']); ?></p>

                                    <div class="<?= choiceClass('A', $r['correctAnswer']); ?>">A. <?= htmlspecialchars($r['answerA']); ?></div>
                                    <div class="<?= choiceClass('B', $r['correctAnswer']); ?>">B. <?= htmlspecialchars($r['answerB']); ?></div>
                                    <div class="<?= choiceClass('C', $r['correctAnswer']); ?>">C. <?= htmlspecialchars($r['answerC']); ?></div>
                                    <div class="<?= choiceClass('D', $r['correctAnswer']); ?>">D. <?= htmlspecialchars($r['answerD']); ?></div>
                                </td>
                                <td><?= ucfirst(htmlspecialchars($r['status'])); ?></td>
                                <td><?= htmlspecialchars($r['comments'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </main>

</div>

<!-- AJAX SCRIPT -->
<script>
$(document).ready(function () {

    function loadQuizzes(topic) {
        $.ajax({
            url: "fetch_quizzes.php",
            type: "POST",
            data: { topic: topic },
            dataType: "json",
            success: function (data) {
                let html = "";

                if (data.length === 0) {
                    html = `<tr><td colspan='4' class='no-data'>No quizzes available.</td></tr>`;
                } else {
                    data.forEach(q => {
                        html += `
                        <tr>
                            <td>${q.topicName}</td>
                            <td>
                                <img src="uploads/${q.photoFileName}" style="width:40px; height:40px; border-radius:50%; margin-right:8px;">
                                ${q.firstName} ${q.lastName}
                            </td>
                            <td>${q.questionCount}</td>
                            <td>
                                ${q.questionCount > 0 
                                    ? `<a href="TakeQuiz.php?quiz_id=${q.id}" class="topic-link">Start</a>` 
                                    : `<span style='color:#888'>No questions yet</span>`}
                            </td>
                        </tr>`;
                    });
                }

                $("#quizTableBody").html(html);
            }
        });
    }

    loadQuizzes("all");

    $("#topicFilter").on("change", function () {
        loadQuizzes($(this).val());
    });

});
</script>

</body>
</html>