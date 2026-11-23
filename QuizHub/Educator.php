<?php
session_start();

// ===================================
//  LOGIN VERIFICATION
// ===================================
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// ==========================================
//REQUIREMENT 6a: EDUCATOR ACCESS VERIFICATION
// ==========================================
// Verify that the user is an educator
if ($_SESSION['user_type'] !== 'educator') {
    $_SESSION['error'] = "Access denied. Educators only.";
    header("Location: logIn.php");
    exit();
}

require_once 'db_config.php';
$conn = getDBConnection();

// ====================================
//  FETCH USER INFORMATION
// ====================================
// Get educator user details from database
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM User WHERE id = ? AND userType = 'educator'";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    session_destroy();
    header("Location: logIn.php");
    exit();
}

$user = $user_result->fetch_assoc();
$stmt->close();

// ================================================
//  FETCH ALL EDUCATOR'S QUIZZES
// ================================================
// Get all quizzes created by this educator with statistics
$sql_quizzes = "
    SELECT 
        q.id as quiz_id,
        t.id as topic_id,
        t.topicName,
        (SELECT COUNT(*) FROM QuizQuestion WHERE quizID = q.id) as question_count,
        (SELECT COUNT(*) FROM TakenQuiz WHERE quizID = q.id) as taken_count,
        (SELECT AVG(score) FROM TakenQuiz WHERE quizID = q.id) as avg_score,
        (SELECT AVG(rating) FROM QuizFeedback WHERE quizID = q.id) as avg_rating,
        (SELECT COUNT(*) FROM QuizFeedback WHERE quizID = q.id) as feedback_count
    FROM Quiz q
    JOIN Topic t ON q.topicID = t.id
    WHERE q.educatorID = ?
    ORDER BY t.topicName
";

$stmt = $conn->prepare($sql_quizzes);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quizzes_result = $stmt->get_result();
$stmt->close();

// ==========================================================
// FETCH PENDING QUESTION RECOMMENDATIONS
// ==========================================================
// Get recommended questions with 'pending' status for review
$sql_recommendations = "
    SELECT 
        rq.*,
        t.topicName,
        u.firstName as learner_firstName,
        u.lastName as learner_lastName,
        u.photoFileName as learner_photo
    FROM RecommendedQuestion rq
    JOIN Quiz q ON rq.quizID = q.id
    JOIN Topic t ON q.topicID = t.id
    JOIN User u ON rq.learnerID = u.id
    WHERE q.educatorID = ? AND rq.status = 'pending'
    ORDER BY rq.id DESC
";

$stmt = $conn->prepare($sql_recommendations);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recommendations_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator - QuizHub</title>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8eaf6 50%, #d1c4e9 100%);
            min-height: 100vh;
            padding: 20px 20px 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
            background-size: 300% 300%;
            animation: gradientFlow 8s ease-in-out infinite;
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .welcome-text {
            font-size: 28px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 24px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: white;
            transform: translateY(-2px);
        }

        .content {
            padding: 40px;
        }

        .user-info {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8eaf6 100%);
            border-radius: 15px;
            padding: 35px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            border: 1px solid #e1e5e9;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .user-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .user-details h3 {
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 24px;
        }

        .user-details p {
            color: #495057;
            margin-bottom: 6px;
            font-size: 16px;
        }

        .section-title {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 4px solid #667eea;
            padding-bottom: 15px;
        }

        .quiz-table, .recommendations-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .quiz-table th, .recommendations-table th {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 18px;
            text-align: left;
            font-weight: 700;
            font-size: 17px;
        }

        .quiz-table td, .recommendations-table td {
            padding: 20px 18px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
            font-size: 15px;
        }

        .quiz-table tr:hover, .recommendations-table tr:hover {
            background-color: #f8f9ff;
        }

        .topic-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
        }

        .topic-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .comments-link {
            color: #28a745;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .comments-link:hover {
            text-decoration: underline;
        }

        .learner-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .learner-photo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
        }

        .learner-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .question-content {
            background: #f8f9fa;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 12px;
            color: #2c3e50;
            font-size: 15px;
        }

        .answer-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .answer-option {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        .correct-answer {
            background: #d4edda;
            color: #155724;
            font-weight: 700;
            border: 2px solid #c3e6cb;
        }

        .incorrect-answer {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .review-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .review-form textarea {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            resize: vertical;
            min-height: 70px;
            font-family: inherit;
            font-size: 14px;
        }

        .review-form textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .review-buttons {
            display: flex;
            gap: 12px;
        }

        .approve-btn, .disapprove-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .approve-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .approve-btn:hover {
            background: linear-gradient(45deg, #218838, #1ba085);
            transform: translateY(-2px);
        }

        .disapprove-btn {
            background: linear-gradient(45deg, #dc3545, #fd7e88);
            color: white;
        }

        .disapprove-btn:hover {
            background: linear-gradient(45deg, #c82333, #e85a6a);
            transform: translateY(-2px);
        }

        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 30px;
            font-size: 16px;
        }

        .question-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 2px solid #dee2e6;
        }

        .rating {
            color: #ffc107;
            font-weight: 700;
        }

        .footer {
            width: 100%;
            padding: 20px 0;
            text-align: left;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
            margin-top: 40px;
        }

        .contacts {
            padding-left: 20px;
            padding-bottom: 10px;
        }

        .copyright {
            text-align: center;
            padding: 20px;
        }

        /* AJAX Loading & Alert Styles */
        .processing {
            opacity: 0.6;
            pointer-events: none;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        /* Row fade out animation */
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .removing {
            animation: fadeOut 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- ========================================================= -->
            <!-- DISPLAY USER'S FIRST NAME IN WELCOME MSG -->
            <!-- ========================================================= -->
            <div class="welcome-text">Welcome <?php echo htmlspecialchars($user['firstName']); ?></div>
            
            <!-- ================================ -->
            <!--  LOGOUT LINK -->
            <!-- ================================ -->
            <a href="logout.php" class="logout-btn">Log-out</a>
        </div>

        <div class="content">
            <!-- Alert Messages -->
            <div id="alertMessage" class="alert"></div>

            <!-- ==================================== -->
            <!--  DISPLAY USER INFO -->
            <!-- ==================================== -->
            <div class="user-info">
                <?php 
                $photo_path = !empty($user['photoFileName']) ? 'uploads/' . $user['photoFileName'] : 'uploads/default-user.png';
                ?>
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo" class="user-photo">
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3>
                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['firstName']); ?></p>
                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['lastName']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['emailAddress']); ?></p>
                </div>
            </div>

            <!-- ================================== -->
            <!--  QUIZZES TABLE -->
            <!-- ================================== -->
            <h2 class="section-title">Your Quizzes</h2>
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Number of Questions</th>
                        <th>Quiz Statistics</th>
                        <th>Quiz Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($quizzes_result->num_rows > 0) {
                        while ($quiz = $quizzes_result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td>
                            <!--  Link to quiz page -->
                            <a href="quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="topic-link">
                                <?php echo htmlspecialchars($quiz['topicName']); ?>
                            </a>
                        </td>
                        <td><?php echo $quiz['question_count']; ?></td>
                        <td>
                            <?php 
                            if ($quiz['taken_count'] > 0) {
                                echo "Number of Quiz Takers: " . $quiz['taken_count'] . "<br>";
                                echo "Average Score: " . number_format($quiz['avg_score'], 1) . "%";
                            } else {
                                echo "Quiz not taken yet";
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($quiz['feedback_count'] > 0) {
                                echo '<span class="rating">Average Rating: ' . number_format($quiz['avg_rating'], 1) . '/5</span><br>';
                                echo '<a href="comments.php?quiz_id=' . $quiz['quiz_id'] . '" class="comments-link">Comments</a>';
                            } else {
                                echo "No feedback yet";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="4" class="no-data">No quizzes available</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <!-- ============================================== -->
            <!-- QUESTION RECOMMENDATIONS TABLE -->
            <!-- ============================================== -->
            <h2 class="section-title">Question Recommendations</h2>
            <table class="recommendations-table">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Learner</th>
                        <th>Question</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody id="recommendationsTableBody">
                    <?php 
                    if ($recommendations_result->num_rows > 0) {
                        while ($rec = $recommendations_result->fetch_assoc()) {
                    ?>
                    <tr id="recommendation-row-<?php echo $rec['id']; ?>">
                        <td><?php echo htmlspecialchars($rec['topicName']); ?></td>
                        <td>
                            <div class="learner-info">
                                <?php 
                                $learner_photo = !empty($rec['learner_photo']) ? 'uploads/' . $rec['learner_photo'] : 'uploads/default-user.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($learner_photo); ?>" alt="Learner Photo" class="learner-photo">
                                <span class="learner-name"><?php echo htmlspecialchars($rec['learner_firstName'] . ' ' . $rec['learner_lastName']); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="question-content">
                                <?php if (!empty($rec['questionFigureFileName'])) { ?>
                                    <img src="uploads/<?php echo htmlspecialchars($rec['questionFigureFileName']); ?>" alt="Question Image" class="question-image">
                                <?php } ?>
                                <div class="question-text"><?php echo htmlspecialchars($rec['question']); ?></div>
                                <div class="answer-options">
                                    <div class="answer-option <?php echo $rec['correctAnswer'] === 'A' ? 'correct-answer' : 'incorrect-answer'; ?>">
                                        A) <?php echo htmlspecialchars($rec['answerA']); ?>
                                    </div>
                                    <div class="answer-option <?php echo $rec['correctAnswer'] === 'B' ? 'correct-answer' : 'incorrect-answer'; ?>">
                                        B) <?php echo htmlspecialchars($rec['answerB']); ?>
                                    </div>
                                    <div class="answer-option <?php echo $rec['correctAnswer'] === 'C' ? 'correct-answer' : 'incorrect-answer'; ?>">
                                        C) <?php echo htmlspecialchars($rec['answerC']); ?>
                                    </div>
                                    <div class="answer-option <?php echo $rec['correctAnswer'] === 'D' ? 'correct-answer' : 'incorrect-answer'; ?>">
                                        D) <?php echo htmlspecialchars($rec['answerD']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <!-- ================================== -->
                            <!-- AJAX REVIEW FORM -->
                            <!-- ================================== -->
                            <div class="review-form">
                                <textarea class="review-comments" placeholder="Write a comment for the learner..."></textarea>
                                <div class="review-buttons">
                                    <button type="button" class="approve-btn" onclick="reviewQuestion(<?php echo $rec['id']; ?>, 'approve')">Approve</button>
                                    <button type="button" class="disapprove-btn" onclick="reviewQuestion(<?php echo $rec['id']; ?>, 'disapprove')">Disapprove</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr id="no-recommendations"><td colspan="4" class="no-data">No pending question recommendations</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="contacts">
                <h3>Contact us:</h3>
                <div class="phone">
                    <p>+9665563822982</p>
                </div>
                <div class="email">
                    <a href="mailto:QuizHub@gmail.com">QuizHub@gmail.com</a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 <strong>QuizHub.</strong> All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ================================================ -->
    <!-- AJAX JAVASCRIPT FOR REVIEWING RECOMMENDATIONS -->
    <!-- ================================================ -->
    <script>
        function reviewQuestion(recommendationId, action) {
            // Get the comment from the textarea in the same row
            const row = $('#recommendation-row-' + recommendationId);
            const comments = row.find('.review-comments').val();
            
            // Disable buttons during processing
            row.find('.approve-btn, .disapprove-btn').prop('disabled', true).addClass('processing');
            
            // Make AJAX request
            $.ajax({
                url: 'review_question_ajax.php',
                type: 'POST',
                data: {
                    recommendation_id: recommendationId,
                    action: action,
                    comments: comments
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success === true) {
                        // Show success message
                        showAlert('Question ' + action + 'd successfully!', 'success');
                        
                        // Add fade out animation
                        row.addClass('removing');
                        
                        // Remove the row after animation completes
                        setTimeout(function() {
                            row.remove();
                            
                            // Check if there are any recommendations left
                            const remainingRows = $('#recommendationsTableBody tr').length;
                            if (remainingRows === 0) {
                                $('#recommendationsTableBody').html(
                                    '<tr id="no-recommendations"><td colspan="4" class="no-data">No pending question recommendations</td></tr>'
                                );
                            }
                        }, 500);
                    } else {
                        // Show error message
                        showAlert('Error: ' + (response.message || 'Failed to process recommendation'), 'error');
                        // Re-enable buttons
                        row.find('.approve-btn, .disapprove-btn').prop('disabled', false).removeClass('processing');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('Error: Failed to communicate with server', 'error');
                    // Re-enable buttons
                    row.find('.approve-btn, .disapprove-btn').prop('disabled', false).removeClass('processing');
                }
            });
        }
        
        function showAlert(message, type) {
            const alertDiv = $('#alertMessage');
            alertDiv.removeClass('alert-success alert-error')
                   .addClass('alert-' + type)
                   .text(message)
                   .fadeIn();
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                alertDiv.fadeOut();
            }, 5000);
        }
    </script>
</body>
</html>
<?php
closeDBConnection($conn);
?>