<?php
session_start(); 
if (empty($_SESSION['user_id'])) {
    header('Location: index.php?err=login_required'); 
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$connection = mysqli_connect("localhost", "root", "root", "quizhub", 8889);
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}
$quiz_id = $_GET['quiz_id'];
$sql = "
  SELECT q.id, t.topicName
  FROM Quiz q
  JOIN Topic t ON t.id = q.topicID
  WHERE q.id = $quiz_id
";
$Res = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($Res);
$topicName = $row['topicName'];

$sqlCom = "
  SELECT rating, comments, date
  FROM QuizFeedback
  WHERE quizID = $quiz_id
  ORDER BY date DESC
";
$cRes = mysqli_query($connection, $sqlCom);

function stars($n) {
    $out = '';
    for ($i = 1;
            $i <= 5;
            $i++) {
        $out .= ($i <= $n) ? '★ ' : '☆ ';
    }
    return trim($out);
}
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Quiz Comments</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="style.css">


    </head>
    <body>

        <header>
            <div class="logo">
                <img src="logo1.png" alt="QuizHub Logo" class="logo-img">QuizHub
            </div>
            <a href="Educator.php" class="btn back" id="back-btn">← Back to Homepage</a>
        </header>

        <main>
            <section>
                <div class="comments-header">
                    <h2>Comments</h2>
                    <div class="stats">
                        <span class="chip">Quiz: <?php echo $topicName ?></span>
                    </div>
                </div>

                <div class="comment-list">
                    <?php if (mysqli_num_rows($cRes) === 0) { ?>
                        <article class="c-card">
                            <p class="c-body">No feedback yet.</p>
                        </article>
                    <?php } else { ?>
                        <?php while ($c = mysqli_fetch_assoc($cRes)) { ?>
                            <article class="c-card">
                                <div class="c-head">
                                    <span class="c-badge">Anonymous</span>
                                    <span class="stars"><?php echo stars($c['rating']) ?></span>
                                </div>
                                <p class="c-body"><?php echo $c['comments'] ?></p>
                                <div class="c-foot">
                                    <span><?php echo$c['date'] ?></span>
                                </div>
                            </article>
                        <?php } ?>
                    <?php } ?>



                </div>
            </section>
        </main>
        <footer >
            <div class="footer">
                <div class="contacts">
                    <h3>Conact us:</h3>
                    <div class="phone">
                        <img src="images/phone-icon.png" id="phone-icon" alt="phone">
                        <p>+9665563822982</p>
                    </div>
                    <div class="email">
                        <a href="mailto:QuizHub@gmail.com"> <img src="images/mail.png" id="email-icon"
                                                                 alt="email"> </a>
                        <a href="mailto:QuizHub@gmail.com"> QuizHub@gmail.com</a>
                    </div>

                    <div class="social-media">
                        <a href="https://www.snapchat.com" target="_blank" > <img src="images/snapchat.png" id="snapchat-icon"
                                                                                  alt="snapchat"></a>
                        <a href="https://www.instagram.com" target="_blank" ><img src="images/instagram.png" id="instagram-icon"
                                                                                  alt="instagram"> </a>
                        <a href="https://www.twitter.com" target="_blank"><img src="images/twitter.png" id="twitter-icon"
                                                                               alt="twitter"></a>
                    </div>
                </div>
                <div class="copyright">
                    <p>&copy 2025 <strong>QuizHub.</strong> All rights reserved.</p>
                </div>
            </div>

        </footer>
    </body>
</html>

