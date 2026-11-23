<?php
session_start();
header('Content-Type: application/json');

// ===================================
//  AUTHENTICATION CHECK
// ===================================
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'educator') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// ===================================
//  VALIDATE REQUEST METHOD
// ===================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// ===================================
//  VALIDATE INPUT DATA
// ===================================
if (!isset($_POST['recommendation_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$recommendation_id = intval($_POST['recommendation_id']);
$action = $_POST['action'];
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

// Validate action
if (!in_array($action, ['approve', 'disapprove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

require_once 'db_config.php';
$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();
    
    // ====================================================
    //  FETCH RECOMMENDATION DETAILS
    // ====================================================
    // Verify recommendation exists and belongs to this educator's quiz
    $sql_check = "
        SELECT rq.*, q.educatorID, q.id as quiz_id
        FROM RecommendedQuestion rq
        JOIN Quiz q ON rq.quizID = q.id
        WHERE rq.id = ? AND q.educatorID = ? AND rq.status = 'pending'
    ";
    
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ii", $recommendation_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Recommendation not found or already processed');
    }
    
    $recommendation = $result->fetch_assoc();
    $stmt->close();
    
    // ====================================================
    //  UPDATE RECOMMENDATION STATUS
    // ====================================================
    $new_status = ($action === 'approve') ? 'approved' : 'disapproved';
    
    $sql_update = "
        UPDATE RecommendedQuestion 
        SET status = ?, comments = ?
        WHERE id = ?
    ";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssi", $new_status, $comments, $recommendation_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update recommendation status');
    }
    $stmt->close();
    
    // ====================================================
    //  IF APPROVED, ADD QUESTION TO QUIZ
    // ====================================================
    if ($action === 'approve') {
        $sql_add_question = "
            INSERT INTO QuizQuestion (
                quizID, 
                question, 
                answerA, 
                answerB, 
                answerC, 
                answerD, 
                correctAnswer, 
                questionFigureFileName
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($sql_add_question);
        $stmt->bind_param(
            "isssssss",
            $recommendation['quizID'],
            $recommendation['question'],
            $recommendation['answerA'],
            $recommendation['answerB'],
            $recommendation['answerC'],
            $recommendation['answerD'],
            $recommendation['correctAnswer'],
            $recommendation['questionFigureFileName']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add question to quiz');
        }
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // ====================================================
    //  RETURN SUCCESS RESPONSE
    // ====================================================
    echo json_encode([
        'success' => true,
        'message' => 'Recommendation processed successfully',
        'action' => $action
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

closeDBConnection($conn);
?>