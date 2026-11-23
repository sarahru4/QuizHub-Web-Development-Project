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

$sqlQuiz1 = "SELECT topicID FROM Quiz WHERE id = $quiz_id";
$quizRes1 = mysqli_query($connection, $sqlQuiz1);
$quizRow1 = mysqli_fetch_assoc($quizRes1);

$topic_id = $quizRow1['topicID'];

$sqlQuiz2 = "SELECT topicName FROM Topic WHERE id = $topic_id";
$quizRes2 = mysqli_query($connection, $sqlQuiz2);
$quizRow2 = mysqli_fetch_assoc($quizRes2);
$topicName = $quizRow2['topicName'];

$sqlQ = "
  SELECT id, question, questionFigureFileName,
         answerA, answerB, answerC, answerD, correctAnswer
  FROM QuizQuestion
  WHERE quizID = $quiz_id
  ORDER BY id ASC
";
$questionsRes = mysqli_query($connection, $sqlQ);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Quiz </title>
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
            <div class="toolbar">
                <div class="topic">
                    Topic:
                    <strong> <?php echo $topicName ?></strong>
                </div>
                <a class="btn" href="add-question.php?quiz_id=<?php echo $quiz_id ?>">＋ Add new question</a>
            </div>
            <h2>Questions in this quiz</h2>

            <?php
            $sqlcount = "SELECT count(id) as total FROM QuizQuestion WHERE quizID =  $quiz_id";
            $countRes = mysqli_query($connection, $sqlcount);
            $countRow = mysqli_fetch_assoc($countRes);
            $count = $countRow['total'];

            if ($count == 0) {
                ?>
                <section class="card">
                    <p>No questions have been added to this quiz yet.</p>
                </section>
            <?php } else { ?>
                <section class="card">
                    <div id="tableQuiz">
                        <table>
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Choices</th>
                                    <th>Figure</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rows">
                                <?php while ($row = mysqli_fetch_assoc($questionsRes)) { ?>
                                    <tr>
                                        <td> <?php echo $row['question'] ?> </td>
                                        <td>
                                            <?php
                                            $choices = [
                                                'A' => $row['answerA'],
                                                'B' => $row['answerB'],
                                                'C' => $row['answerC'],
                                                'D' => $row['answerD'],
                                            ];
                                            foreach ($choices as $letter => $text) {
                                                $label = $letter . '. ' . $text;
                                                $isCorrect = ($letter === $row['correctAnswer']);
                                                if ($isCorrect) {
                                                    echo '<p class="correct answer">' . $label . '</p>';
                                                } else {
                                                    echo '<p class="answer">' . $label . '</p>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['questionFigureFileName'])) { ?>
                                                <?php
                                                $imgUrl = "uploads/" . $row['questionFigureFileName'];
                                                ?>
                                                <img src="<?php echo $imgUrl ?>"
                                                     class="Figure" alt="Figure">
                                                 <?php } else { ?>
                                                <span class="empty">&ndash;</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a href="edit-question.php?question_id=<?php echo $row['id'] ?>"
                                               style="color:#2563eb;margin-right:10px;">Edit</a>
                                            <a href="#"
                                               class="js-delete"
                                               data-question-id="<?php echo $row['id']; ?>"
                                               data-quiz-id="<?php echo $quiz_id; ?>"
                                               style="color:#8C1007;">Delete</a>

                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div id="commentArea" style="color: green; margin: 15px 0;"></div>

                    </div>
                </section>
            <?php } ?>
        </main>

        <footer>
            <div class="footer">
                <div class="contacts">
                    <h3>Conact us:</h3>
                    <div class="phone">
                        <img src="images/phone-icon.png" id="phone-icon" alt="phone">
                        <p>+9665563822982</p>
                    </div>
                    <div class="email">
                        <a href="mailto:QuizHub@gmail.com"> <img src="images/mail.png" id="email-icon" alt="email"> </a>
                        <a href="mailto:QuizHub@gmail.com"> QuizHub@gmail.com</a>
                    </div>
                    <div class="social-media">
                        <a href="https://www.snapchat.com" target="_blank" > <img src="images/snapchat.png" id="snapchat-icon" alt="snapchat"></a>
                        <a href="https://www.instagram.com" target="_blank" ><img src="images/instagram.png" id="instagram-icon" alt="instagram"> </a>
                        <a href="https://www.twitter.com" target="_blank"><img src="images/twitter.png" id="twitter-icon" alt="twitter"></a>
                    </div>
                </div>
                <div class="copyright">
                    <p>&copy 2025 <strong>QuizHub.</strong> All rights reserved.</p>
                </div>
            </div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            $(document).on('click', '.js-delete', function (e) {
                e.preventDefault();

                const $link = $(this);
                const questionId = $link.data('question-id');
                const quizId = $link.data('quiz-id');
                $.post(
                        'delete-question.php',
                        {question_id: questionId, quiz_id: quizId},
                        function (resp) {
                            if (resp == 'true') {
                                $link.closest('tr').remove();
                                $('#commentArea').text('Question deleted successfully.');
                            } else {
                                $('#commentArea').text('Delete failed.');
                            }
                        }
                );
            });
        </script>

    </body>
</html>
