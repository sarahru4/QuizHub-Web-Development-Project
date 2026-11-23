<?php
session_start();


if(!isset($_SESSION['user_id']) || $_SESSION['user_type']!=='learner'){
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$conn = new mysqli('localhost','root','root','quizhub',8889);
if($conn->connect_error) die();

$topicID = $_GET['topicID'] ?? 0;

$stmt = $conn->prepare("SELECT id, firstName, lastName FROM user WHERE userType='educator' AND id IN (SELECT educatorID FROM Quiz WHERE topicID=?)");
$stmt->bind_param("i",$topicID);
$stmt->execute();
$result = $stmt->get_result();

$educators = [];
while($row = $result->fetch_assoc()){
    $educators[] = ["id"=>$row['id'], "name"=>$row['firstName']." ".$row['lastName']];
}

echo json_encode($educators);
