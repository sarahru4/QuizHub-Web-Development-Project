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

$question_id = $_GET['question_id'];
$sql = "SELECT id, quizID, question, questionFigureFileName,
               answerA, answerB, answerC, answerD, correctAnswer
        FROM QuizQuestion
        WHERE  id = $question_id";
$res = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($res);
$quiz_id =$row['quizID'];
$qText = $row['question'];
$c1 = $row['answerA'];
$c2 = $row['answerB'];
$c3 = $row['answerC'];
$c4 = $row['answerD'];
$correct = $row['correctAnswer'];
$figure = $row['questionFigureFileName'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Edit Question</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header >
            <div class="logo">
                <img src="logo1.png" alt="QuizHub Logo" class="logo-img">QuizHub 
            </div>

        </header>

        <main>
            <form class="card" id="qForm" action="edit.php" method="post" enctype="multipart/form-data">
                <h1 class="title">Edit question</h1>
                <div class="form-container">
                   

                    <label for="qText">Question text</label>
                    <textarea id="qText" name="qText" rows="3" required><?php echo $qText ?></textarea>

                    <div class="choices-grid">
                        <div><label for="c1">Choice A</label><input id="c1" name="c1" type="text" required value="<?php echo $c1 ?>"/></div>
                        <div><label for="c2">Choice B</label><input id="c2" name="c2" type="text" required value="<?php echo $c2 ?>"/></div>
                        <div><label for="c3">Choice C</label><input id="c3" name="c3" type="text" required value="<?php echo $c3 ?>"/></div>
                        <div><label for="c4">Choice D</label><input id="c4" name="c4" type="text" required value="<?php echo $c4 ?>"/></div>
                    </div>

                    <label for="correct">Correct choice</label>
                    <select id="correct" name="correct" required>
                        <option value="A"<?= ($correct === 'A' ? 'selected' : '') ?>>A</option>
                        <option value="B"<?= ($correct === 'B' ? 'selected' : '') ?>>B</option>
                        <option value="C"<?= ($correct === 'C' ? 'selected' : '') ?>>C</option>

                        <option value="D" <?= ($correct === 'D' ? 'selected' : '') ?>>D</option>
                    </select>

                    <label for="figure">Optional figure (image)</label>
                    <input id="figure" name="figure" type="file" accept="image/*" />
                    <img id="figurePreview" alt="Preview" style="display:none; max-width:200px; margin-top:10px; border-radius:8px; border:2px solid #dee2e6;">
                    <?php if (!empty($figure)): ?>
                        <div style="margin-top:8px;">
                            <small>Current image:</small><br>
                            <img src="uploads/<?= rawurlencode($figure) ?>" alt="Current Figure"
                                 style="max-width:200px;border-radius:8px;border:2px solid #dee2e6;">
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="id" name="quiz_id" value="<?php echo $quiz_id ?>">
                    <input type="hidden" id="id" name="question_id" value="<?php echo $question_id ?>">
                    <div class="toolbar">
                        <button class="btn" type="submit">Save changes</button>
                        <a class="btn cancel" href="quiz.php?quiz_id=<?= $quiz_id ?>">Cancel</a>
                    </div>
                </div>

            </form>
        </main>
        <script>
            const figureInput = document.getElementById('figure');
            const preview = document.getElementById('figurePreview');

            figureInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block'; // show image
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none'; // hide if no file
                }
            });
        </script>
        <footer>
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


