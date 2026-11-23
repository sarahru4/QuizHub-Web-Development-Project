<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$connection = mysqli_connect("localhost", "root", "root", "quizhub", 8889);
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}


$quiz_id = $_POST['quiz_id'];
$question_id = $_POST['question_id'];

$sqlSel = "SELECT questionFigureFileName FROM QuizQuestion WHERE id = $question_id AND quizID = $quiz_id";
$res = mysqli_query($connection, $sqlSel);
$row = mysqli_fetch_assoc($res);
$oldImg = $row ? $row['questionFigureFileName'] : '';

$sqlDel = "DELETE FROM QuizQuestion WHERE id = $question_id AND quizID = $quiz_id";
mysqli_query($connection, $sqlDel);

if (mysqli_affected_rows($connection) > 0) {
    if (!empty($oldImg)) {
        $dir = __DIR__ . '/uploads/';
        $path = $dir . $oldImg;
        if (is_file($path)) {
            @unlink($path);
        }
    }
    echo 'true';
} else {
    echo 'false';
}


