<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$connection = mysqli_connect("localhost", "root", "root", "quizhub", 8889);
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}

$quiz_id = $_POST['quiz_id'];
$qText = $_POST['qText'];
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];
$c3 = $_POST['c3'];
$c4 = $_POST['c4'];
$correct = $_POST['correct'];

$sqlInsert = "INSERT INTO QuizQuestion(quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
  VALUES('$quiz_id', '$qText', NULL, '$c1', '$c2', '$c3', '$c4', '$correct')";
mysqli_query($connection, $sqlInsert);
$question_id = mysqli_insert_id($connection);

//Image upload
if (isset($_FILES['figure'])) {

    //directory
    $target_dir = __DIR__ ."/uploads/";
    $target_file = $target_dir . basename($_FILES["figure"]["name"]);
    //Lowercased file extension
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Unique file name
    $finalName = "quiz{$quiz_id}_q{$question_id}" . ($imageFileType ? "." . $imageFileType : "");
    $destPath = $target_dir . $finalName;
    // Move the uploaded file from PHP's temp folder to Upload
    if (move_uploaded_file($_FILES["figure"]["tmp_name"], $destPath)) {
        // Store only the file name in the database
        $sqlUpdate = "
                UPDATE QuizQuestion
                SET questionFigureFileName = '$finalName'
                WHERE id = $question_id
            ";
        mysqli_query($connection, $sqlUpdate);
    }
}

header('Location: quiz.php?quiz_id=' . $quiz_id);
exit();
?>

