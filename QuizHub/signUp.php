<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost','root','root','quizhub',8889);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$default_avatar = 'default.png';
$errorMessage = '';
$userType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    
    $stmt = $conn->prepare("SELECT id FROM User WHERE emailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMessage = "This email already exists. Please try another one.";
    } else {
        $stmt->close();

        $avatarFileName = $default_avatar;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $targetDir = 'uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir);
            $avatarFileName = basename($_FILES['avatar']['name']);
            move_uploaded_file($_FILES['avatar']['tmp_name'], $targetDir . $avatarFileName);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO User (firstName,lastName,emailAddress,password,photoFileName,userType) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $fname, $lname, $email, $hashedPassword, $avatarFileName, $userType);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $userType;

        if ($userType === 'educator' && isset($_POST['topics'])) {
            foreach ($_POST['topics'] as $topicName) {
                $stmt = $conn->prepare("SELECT id FROM Topic WHERE topicName = ?");
                $stmt->bind_param("s", $topicName);
                $stmt->execute();
                $stmt->bind_result($topic_id);
                $stmt->fetch();
                $stmt->close();

                if ($topic_id) {
                    $stmt = $conn->prepare("INSERT INTO Quiz (educatorID, topicID) VALUES (?,?)");
                    $stmt->bind_param("ii", $user_id, $topic_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        header($userType==='learner' ? "Location: learner_homepage.php" : "Location: Educator.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - QuizHub</title>
<link rel="stylesheet" href="style1.css">
<style>.hidden { display:none; } .signup-box{text-align:center;margin-top:50px;}</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const learnerRadio = document.querySelector('input[value="learner"]');
  const educatorRadio = document.querySelector('input[value="educator"]');
  const learnerForm = document.getElementById("learnerForm");
  const educatorForm = document.getElementById("educatorForm");

  learnerRadio.addEventListener("change", ()=>{learnerForm.classList.remove("hidden");educatorForm.classList.add("hidden");});
  educatorRadio.addEventListener("change", ()=>{educatorForm.classList.remove("hidden");learnerForm.classList.add("hidden");});

  const selectedType = "<?php echo $userType; ?>";
  if(selectedType==="learner") learnerForm.classList.remove("hidden");
  else if(selectedType==="educator") educatorForm.classList.remove("hidden");
});
</script>
</head>
<body>
<header id="main-header">
  <div class="logo">
    <img src="images/logo1.png" alt="QuizHub Logo" class="logo-img">QuizHub
  </div>
</header>

<div class="signup-box">
  <h2>Create an account</h2>
  <?php if(!empty($errorMessage)) echo "<p style='color:red;'>$errorMessage</p>"; ?>

  <p><strong>Select user type:</strong></p>
  <label><input type="radio" name="userType" value="learner" <?php if($userType==='learner') echo 'checked'; ?> required> Learner</label>
  <label><input type="radio" name="userType" value="educator" <?php if($userType==='educator') echo 'checked'; ?>> Educator</label>

  <form id="learnerForm" class="hidden" action="signup.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="userType" value="learner">
    <input type="text" name="fname" placeholder="First Name" required>
    <input type="text" name="lname" placeholder="Last Name" required>
    <label>Profile image (optional):</label>
    <input type="file" name="avatar" accept="image/*">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
  </form>

  <form id="educatorForm" class="hidden" action="signup.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="userType" value="educator">
    <input type="text" name="fname" placeholder="First Name" required>
    <input type="text" name="lname" placeholder="Last Name" required>
    <label>Profile image (optional):</label>
    <input type="file" name="avatar" accept="image/*">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <p><strong>Select Topics:</strong></p>
    <label><input type="checkbox" name="topics[]" value="AI"> AI</label><br>
    <label><input type="checkbox" name="topics[]" value="CS"> CS</label><br>
    <label><input type="checkbox" name="topics[]" value="IOT"> IOT</label><br>
    <button type="submit">Register</button>
  </form>
</div>
</body>
</html>
