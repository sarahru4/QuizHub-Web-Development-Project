<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost','root','root','quizhub',8889);
if ($conn->connect_error) { die("Connection failed: ".$conn->connect_error); }


function get_quiz_info(mysqli $conn, int $quiz_id): ?array {
    $sql = "SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName
            FROM Quiz 
            JOIN Topic ON Quiz.topicID = Topic.id 
            JOIN User ON Quiz.educatorID = User.id 
            WHERE Quiz.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function get_correct_answer(mysqli $conn, int $question_id): ?string {
    $stmt = $conn->prepare("SELECT correctAnswer FROM QuizQuestion WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->bind_result($correct);
    $ok = $stmt->fetch();
    $stmt->close();
    return $ok ? $correct : null;
}


$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');
$has_answers = $is_post && isset($_POST['question_ids']) && !isset($_POST['rating']); 
$has_feedback = $is_post && isset($_POST['rating']);                                   

$quiz_id = null;
$topicName = $eduFName = $eduLName = '--';
$percentage = null;
$reactionVideo = 'images/good.gif';
$comments_value = '';


if ($has_answers) {
    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    if ($quiz_id <= 0) {
        header("Location: learner_homepage.php");
        exit();
    }

 
    if ($info = get_quiz_info($conn, $quiz_id)) {
        $topicName = $info['topicName'] ?? '--';
        $eduFName = $info['firstName'] ?? '--';
        $eduLName = $info['lastName'] ?? '';
    }

   
    $question_ids = array_map('intval', $_POST['question_ids'] ?? []);
    $total = count($question_ids);
    $score = 0;

    foreach ($question_ids as $qid) {
        $user_answer = strtoupper(trim($_POST['answer_'.$qid] ?? ''));
        $correct = strtoupper(trim(get_correct_answer($conn, $qid) ?? ''));
        if ($user_answer !== '' && $user_answer === $correct) {
            $score++;
        }
    }

    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
    $reactionVideo = ($percentage >= 50) ? 'images/good.gif' : 'images/bad.gif';

   
}

elseif ($has_feedback) {
    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    $rating  = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    $comments = trim($_POST['comments'] ?? '');

   
    if ($quiz_id <= 0 || $rating === null || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Invalid feedback submission.";
        header("Location: learner_homepage.php");
        exit();
    }

    
    $stmt = $conn->prepare("INSERT INTO QuizFeedback (quizID, rating, comments, date) VALUES (?,?,?,NOW())");
    $stmt->bind_param("iis", $quiz_id, $rating, $comments);
    $stmt->execute();
    $stmt->close();

    

    header("Location: learner_homepage.php");
    exit();
}

else {
    
    $quiz_id = intval($_GET['quiz_id'] ?? 0);
    if ($quiz_id <= 0) {
        header("Location: learner_homepage.php");
        exit();
    }
    if ($info = get_quiz_info($conn, $quiz_id)) {
        $topicName = $info['topicName'] ?? '--';
        $eduFName = $info['firstName'] ?? '--';
        $eduLName = $info['lastName'] ?? '';
    }
    
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quiz Feedback - QuizHub</title>
<link rel="stylesheet" href="style1.css">
</head>
<body>
<header id="main-header">
  <div class="logo">
    <img src="images/logo1.png" alt="QuizHub Logo" class="logo-img">QuizHub
  </div>
</header>

<div class="feedback-box">
  <h2>Quiz in <?php echo htmlspecialchars($topicName); ?></h2>
  <p><strong>Educator:</strong> <?php echo htmlspecialchars($eduFName.' '.$eduLName); ?></p>

  <?php if ($percentage !== null): ?>
    <p><strong>Quiz Score:</strong> <?php echo htmlspecialchars($percentage); ?>%</p>
    <img id="reactionVideo" src="<?php echo htmlspecialchars($reactionVideo); ?>" alt="Reaction" class="feedback-gif">
  <?php endif; ?>

  <h3>Feedback about Quiz:</h3>

 
  <form id="feedbackForm" method="POST" action="quizFeedback.php">
    <input type="hidden" name="quiz_id" value="<?php echo htmlspecialchars($quiz_id ?? 0); ?>">

    <?php
    
    if (!empty($_POST['question_ids']) && is_array($_POST['question_ids'])) {
        foreach ($_POST['question_ids'] as $qid) {
            echo '<input type="hidden" name="question_ids[]" value="'.htmlspecialchars($qid).'">';
        }
    }
   
    if ($percentage !== null) {
        echo '<input type="hidden" name="percentage" value="'.htmlspecialchars($percentage).'">';
    }
    ?>

    <label for="rating">Rating (out of 5):</label>
    <select id="rating" name="rating" required>
      <option value="" disabled selected>Choose...</option>
      <option value="1">1</option><option value="2">2</option>
      <option value="3">3</option><option value="4">4</option>
      <option value="5">5</option>
    </select>

    <label for="comments">Comments:</label>
    <textarea id="comments" name="comments"><?php echo htmlspecialchars($comments_value); ?></textarea>

    <button type="submit" class="btn">Submit Feedback</button>
  </form>
</div>

<footer>
  <div class="footer">
    <div class="contacts">
      <h3>Contact us:</h3>
      <div class="phone">
        <p>+9665563822982</p>
      </div>
      <div class="email">
        <a href="mailto:QuizHub@gmail.com">QuizHub@gmail.com</a>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; 2025 <strong>QuizHub.</strong> All rights reserved.</p>
    </div>
  </div>
</footer>
</body>
</html>
