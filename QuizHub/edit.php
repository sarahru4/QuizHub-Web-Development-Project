<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$connection = mysqli_connect("localhost", "root", "root", "quizhub", 8889);
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}
$quiz_id = $_POST['quiz_id'];
$question_id = $_POST['question_id'];
$qText = $_POST['qText'];
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];
$c3 = $_POST['c3'];
$c4 = $_POST['c4'];
$correct = $_POST['correct'];

$sqlUpdate = "
    UPDATE QuizQuestion
    SET question = '$qText',
        answerA  = '$c1',
        answerB  = '$c2',
        answerC  = '$c3',
        answerD  = '$c4',
        correctAnswer = '$correct'
    WHERE id = $question_id
";
mysqli_query($connection, $sqlUpdate);

if (
        isset($_FILES['figure']) && is_uploaded_file($_FILES['figure']['tmp_name'])
) {
    $resOld = mysqli_query($connection, "SELECT questionFigureFileName FROM QuizQuestion WHERE id = $question_id");
    $oldRow = mysqli_fetch_assoc($resOld);
    $oldFile = $oldRow ? $oldRow['questionFigureFileName'] : '';

    $origName = $_FILES['figure']['name'];
    $tmpPath = $_FILES['figure']['tmp_name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    $dir = __DIR__ . "/uploads/";

    $finalName = "quiz{$quiz_id}_q{$question_id}." . $ext;
    $destPath = $dir . $finalName;

    if (move_uploaded_file($tmpPath, $destPath)) {
        if (!empty($oldFile) && $oldFile !== $finalName && is_file($dir . $oldFile)) {
            unlink($dir . $oldFile);
        }
        mysqli_query(
                $connection,
                "UPDATE QuizQuestion SET questionFigureFileName = '$finalName' WHERE id = $question_id"
        );
    }
}



header('Location: quiz.php?quiz_id=' . $quiz_id);
exit;
?>
    
