<?php
session_start();
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$conn = new mysqli('localhost','root','root','quizhub',8889);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$errorMsg = '';

//  (Access denied)
if (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $errorMsg = "Access denied. Please log in as a learner.";
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id,password,userType FROM User WHERE emailAddress=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows===1){
        $stmt->bind_result($user_id,$hashedPassword,$user_type);
        $stmt->fetch();
        if(password_verify($password,$hashedPassword)){
            $_SESSION['user_id']=$user_id;
            $_SESSION['user_type']=$user_type;
            header($user_type==='learner'?"Location: learner_homepage.php":"Location: Educator.php");
            exit;
        } else $errorMsg="Email or password is incorrect.";
    } else $errorMsg="Email or password is incorrect.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Log In - QuizTech</title>
<link rel="stylesheet" href="style1.css">
</head>
<body>
<header id="main-header">
  <div class="logo">
    <img src="images/logo1.png" alt="QuizHub Logo" class="logo-img">QuizHub
  </div>
</header>

<div class="signup-box">
<h2>Log In</h2>
<form action="logIn.php" method="POST">
  <input type="text" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <?php if($errorMsg) echo "<p style='color:red;font-size:0.9em;margin-top:5px;'>$errorMsg</p>"; ?>
  <button type="submit">Log In</button>
</form>
<p class="signup-text">Don’t have an account? <a href="signUp.php">Sign up</a></p>
</div>
</body>
</html>
