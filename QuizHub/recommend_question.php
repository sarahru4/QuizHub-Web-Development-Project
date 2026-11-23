<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

$conn = new mysqli('localhost','root','root','quizhub',8889);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

if(!isset($_SESSION['user_id']) || $_SESSION['user_type']!=='learner'){
    $_SESSION['error_message']="Access denied! Learners only.";
    header("Location: logIn.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$success = false;

$topics = $conn->query("SELECT id, topicName FROM Topic ORDER BY topicName")->fetch_all(MYSQLI_ASSOC);
$educators = $conn->query("SELECT id, firstName, lastName FROM user WHERE userType='educator' ORDER BY firstName")->fetch_all(MYSQLI_ASSOC);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $topicID=$_POST['topic']??null;
    $educatorID=$_POST['educator']??null;
    $question=trim($_POST['qtext']??'');
    $choice1=trim($_POST['choice1']??'');
    $choice2=trim($_POST['choice2']??'');
    $choice3=trim($_POST['choice3']??'');
    $choice4=trim($_POST['choice4']??'');
    $correct=$_POST['correct']??'';
    $valid=['1','2','3','4'];

    if(!$topicID||!$educatorID||!$question||!$choice1||!$choice2||!$choice3||!$choice4||!in_array($correct,$valid)){
        $message="️ Please fill all required fields correctly.";
    }else{
        $map=['1'=>'A','2'=>'B','3'=>'C','4'=>'D'];
        $correctAnswer=$map[$correct];
        $figureFile='';
        if(isset($_FILES['figure'])&&$_FILES['figure']['error']===UPLOAD_ERR_OK){
            $dir='uploads/';
            if(!is_dir($dir)) mkdir($dir,0755,true);
            $ext=pathinfo($_FILES['figure']['name'],PATHINFO_EXTENSION);
            $unique='qfig_'.uniqid().'.'.$ext;
            if(move_uploaded_file($_FILES['figure']['tmp_name'],$dir.$unique)) $figureFile=$unique;
        }

        if(empty($message)){
            $stmt=$conn->prepare("SELECT id FROM Quiz WHERE topicID=? AND educatorID=?");
            $stmt->bind_param("ii",$topicID,$educatorID);
            $stmt->execute();$stmt->bind_result($quizID);$found=$stmt->fetch();$stmt->close();

            if(!$found){
                $stmt=$conn->prepare("INSERT INTO Quiz(educatorID,topicID) VALUES(?,?)");
                $stmt->bind_param("ii",$educatorID,$topicID);
                $stmt->execute();
                $quizID=$stmt->insert_id;
                $stmt->close();
            }

            $stmt=$conn->prepare("INSERT INTO RecommendedQuestion 
              (quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status, comments)
              VALUES(?,?,?,?,?,?,?,?,?,'pending','')");
            $stmt->bind_param("iisssssss",$quizID,$user_id,$question,$figureFile,$choice1,$choice2,$choice3,$choice4,$correctAnswer);
            if($stmt->execute()){
                $success=true;
                $message="Question recommended successfully!";
                header("Location: learner_homepage.php");
                exit;
            }else{
                $message="Database error: ".$stmt->error;
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recommend a Question</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#f8f9ff,#e8eaf6,#d1c4e9);margin:0;padding:20px;}
.container{max-width:720px;margin:40px auto;background:white;border-radius:15px;box-shadow:0 20px 40px rgba(0,0,0,0.15);padding:30px 40px;}
.back-btn,.submit-btn{background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:25px;padding:10px 20px;font-weight:600;text-decoration:none;cursor:pointer;}
label{display:block;margin-top:12px;margin-bottom:4px;font-weight:600;color:#2c3e50;}
input[type=text],select,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;box-sizing:border-box;}
.success{background:#e8f5e9;border-left:5px solid #43a047;padding:10px;margin-bottom:15px;}
.error{background:#ffebee;border-left:5px solid #e53935;padding:10px;margin-bottom:15px;}
</style>

<script>
function loadEducators(topicID){
    if(topicID === "") return;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_educators.php?topicID=" + topicID, true);
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4 && xhr.status === 200){
            var select = document.querySelector("select[name='educator']");
            select.innerHTML = '<option value="">-- Choose Educator --</option>';
            var data = JSON.parse(xhr.responseText);
            data.forEach(function(item){
                var opt = document.createElement("option");
                opt.value = item.id;
                opt.textContent = item.name;
                select.appendChild(opt);
            });
        }
    };
    xhr.send();
}
</script>

</head>
<body>
<div class="container">
  <a href="learner_homepage.php" class="back-btn">← Back to Learner Homepage</a>
  <h2>Recommend a New Question</h2>
  <?php if($message): ?>
    <div class="<?php echo $success?'success':'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <?php if(!$success): ?>
  <form method="POST" enctype="multipart/form-data">

    <label>Topic:</label>
    <select name="topic" required onchange="loadEducators(this.value)">
      <option value="">-- Choose Topic --</option>
      <?php foreach($topics as $t): ?>
        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['topicName']); ?></option>
      <?php endforeach; ?>
    </select>

    <label>Educator:</label>
    <select name="educator" required>
      <option value="">-- Choose Educator --</option>
    </select>

    <label>Question Text:</label>
    <textarea name="qtext" required></textarea>
    <label>Choices:</label>
    <input type="text" name="choice1" placeholder="Choice 1" required>
    <input type="text" name="choice2" placeholder="Choice 2" required>
    <input type="text" name="choice3" placeholder="Choice 3" required>
    <input type="text" name="choice4" placeholder="Choice 4" required>
    <label>Correct Choice:</label>
    <select name="correct" required>
      <option value="1">Choice 1</option>
      <option value="2">Choice 2</option>
      <option value="3">Choice 3</option>
      <option value="4">Choice 4</option>
    </select>
    <label>Question Figure (optional):</label>
    <input type="file" name="figure" accept="image/*">
    <button type="submit" class="submit-btn">Submit Question</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
