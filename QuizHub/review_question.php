
        <?php
       
session_start();

// ===================================
//  LOGIN VERIFICATION
// ===================================
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// ==========================================
// EDUCATOR ACCESS VERIFICATION
// ==========================================
if ($_SESSION['user_type'] !== 'educator') {
    $_SESSION['error'] = "Access denied. Educators only.";
    header("Location: logIn.php");
    exit();
}

// ==========================================
// VALIDATE POST REQUEST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: educator.php");
    exit();
}

// ==========================================
// VALIDATE REQUIRED FIELDS
// ==========================================
if (!isset($_POST['recommendation_id']) || !isset($_POST['action'])) {
    $_SESSION['error'] = "Invalid request. Missing required fields.";
    header("Location: educator.php");
    exit();
}

require_once 'db_config.php';
$conn = getDBConnection();

$recommendation_id = intval($_POST['recommendation_id']);
$action = $_POST['action']; // 'approve' or 'disapprove'
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : null;
$educator_id = $_SESSION['user_id'];

// ==========================================
// VERIFY RECOMMENDATION BELONGS TO EDUCATOR
// ==========================================
$sql_verify = "
    SELECT rq.*, q.educatorID, q.id as quiz_id
    FROM recommendedquestion rq
    JOIN quiz q ON rq.quizID = q.id
    WHERE rq.id = ? AND q.educatorID = ? AND rq.status = 'pending'
";

$stmt = $conn->prepare($sql_verify);
$stmt->bind_param("ii", $recommendation_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Recommendation not found or already processed.";
    $stmt->close();
    closeDBConnection($conn);
    header("Location: educator.php");
    exit();
}

$recommendation = $result->fetch_assoc();
$stmt->close();

// ==========================================
// PROCESS REVIEW BASED ON ACTION
// ==========================================

if ($action === 'approve') {
    // ==============================================
    // APPROVE: Update status and add to quiz
    // ==============================================
   
    // Start transaction
    $conn->begin_transaction();
   
    try {
        // 1. Update recommendation status to 'approved'
        $sql_update_status = "
            UPDATE recommendedquestion
            SET status = 'approved', comments = ?
            WHERE id = ?
        ";
        $stmt = $conn->prepare($sql_update_status);
        $stmt->bind_param("si", $comments, $recommendation_id);
        $stmt->execute();
        $stmt->close();
       
        // 2. Add question to the quiz
        $sql_add_question = "
            INSERT INTO quizquestion
            (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
       
        $stmt = $conn->prepare($sql_add_question);
        $stmt->bind_param(
            "isssssss",
            $recommendation['quizID'],
            $recommendation['question'],
            $recommendation['questionFigureFileName'],
            $recommendation['answerA'],
            $recommendation['answerB'],
            $recommendation['answerC'],
            $recommendation['answerD'],
            $recommendation['correctAnswer']
        );
        $stmt->execute();
        $stmt->close();
       
        // Commit transaction
        $conn->commit();
       
        $_SESSION['success'] = "Question approved and added to your quiz successfully!";
       
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Error processing approval: " . $e->getMessage();
    }
   
} elseif ($action === 'disapprove') {// DISAPPROVE: Update status only
    // ==============================================
   
    $sql_update_status = "
        UPDATE recommendedquestion
        SET status = 'disapproved', comments = ?
        WHERE id = ?
    ";
   
    $stmt = $conn->prepare($sql_update_status);
    $stmt->bind_param("si", $comments, $recommendation_id);
   
    if ($stmt->execute()) {
        $_SESSION['success'] = "Question disapproved successfully.";
    } else {
        $_SESSION['error'] = "Error processing disapproval: " . $stmt->error;
    }
   
    $stmt->close();
   
} else {
    // Invalid action
    $_SESSION['error'] = "Invalid action specified.";
}

// ==========================================
// REDIRECT BACK TO EDUCATOR HOMEPAGE
// ==========================================
closeDBConnection($conn);
header("Location: educator.php");
exit();
?>
    // ==============================================