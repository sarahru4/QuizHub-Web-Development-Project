<?php
header("Content-Type: application/json");
session_start();

$mysqli = new mysqli('localhost', 'root', 'root', 'quizhub', 8889);
if ($mysqli->connect_error) {
    echo json_encode([]);
    exit;
}

$topic = $_POST['topic'] ?? 'all';

$sql = "SELECT q.id, t.topicName, u.firstName, u.lastName, u.photoFileName,
        COUNT(qq.id) AS questionCount
        FROM Quiz q
        JOIN Topic t ON q.topicID = t.id
        JOIN `user` u ON q.educatorID = u.id
        LEFT JOIN QuizQuestion qq ON q.id = qq.quizID";

$params = [];
$types = "";

if ($topic !== "all") {
    $sql .= " WHERE t.topicName = ?";
    $params[] = $topic;
    $types = "s";
}

$sql .= " GROUP BY q.id ORDER BY t.topicName";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$quizzes = [];
while ($row = $res->fetch_assoc()) {
    $quizzes[] = $row;
}

echo json_encode($quizzes);
?>