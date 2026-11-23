<?php
session_start();


// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// =========================================
// QUIZ ID VERIFICATION
// =========================================
// Check if quiz_id exists in query string
if (!isset($_GET['quiz_id']) || empty($_GET['quiz_id'])) {
    header("Location: learner_homepage.php");
    exit();
}

require_once 'db_config.php';
$conn = getDBConnection();

$quiz_id = intval($_GET['quiz_id']);

// ==================================================
//  FETCH QUIZ INFORMATION
// ==================================================
// Get quiz details (topic and educator information)
$sql_quiz_info = "
    SELECT 
        q.id as quiz_id,
        t.topicName,
        u.firstName as educator_firstName,
        u.lastName as educator_lastName,
        u.photoFileName as educator_photo
    FROM Quiz q
    JOIN Topic t ON q.topicID = t.id
    JOIN User u ON q.educatorID = u.id
    WHERE q.id = ?
";

$stmt = $conn->prepare($sql_quiz_info);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    $_SESSION['error'] = "Quiz not found.";
    header("Location: learner.php");
    exit();
}

$quiz_info = $quiz_result->fetch_assoc();
$stmt->close();

// ==========================================
//FETCH ALL QUIZ QUESTIONS
// ==========================================
// Get all questions for this quiz
$sql_questions = "
    SELECT 
        id,
        question,
        questionFigureFileName,
        answerA,
        answerB,
        answerC,
        answerD
    FROM QuizQuestion
    WHERE quizID = ?
    ORDER BY RAND()
";

$stmt = $conn->prepare($sql_questions);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

// Store questions in array
$all_questions = [];
while ($question = $questions_result->fetch_assoc()) {
    $all_questions[] = $question;
}
$stmt->close();

// ==============================================
//  SELECT RANDOM QUESTIONS
// ==============================================
// Select 5 random questions (or all if ≤ 5)
$total_questions = count($all_questions);
if ($total_questions <= 5) {
    $selected_questions = $all_questions;
} else {
    // Select first 5 questions from randomized array
    $selected_questions = array_slice($all_questions, 0, 5);
}

// Check if there are no questions
if (empty($selected_questions)) {
    $_SESSION['error'] = "This quiz has no questions yet.";
    header("Location: learner.php");
    exit();
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - QuizHub</title>
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
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
            background-size: 300% 300%;
            animation: gradientFlow 8s ease-in-out infinite;
            color: white;
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .quiz-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmerEffect 4s infinite;
        }

        @keyframes shimmerEffect {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .quiz-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .educator-section {
            position: relative;
            z-index: 2;
        }

        .educator-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            text-align: left;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 8px;
            display: block;
        }

        .educator-info {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 15px 20px;
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .educator-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }

        .educator-details h3 {
            margin-bottom: 5px;
            font-size: 18px;
        }

        .content {
            padding: 40px;
        }

        .quiz-section-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .question-counter {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .progress-bar {
            background: #e9ecef;
            height: 6px;
            border-radius: 3px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 3px;
        }

        .question-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .question-container:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .question-header {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .question-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .question-text {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .answer-option {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .answer-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateX(5px);
        }

        .answer-option input[type="radio"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .answer-option label {
            cursor: pointer;
            font-size: 15px;
            color: #495057;
            font-weight: 500;
            flex: 1;
        }

        .answer-option input[type="radio"]:checked ~ label {
            color: #667eea;
            font-weight: 600;
        }

        .answer-option.selected {
            border-color: #667eea;
            background: #f8f9ff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-container {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }

        .submit-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .footer {
            width: 100%;
            padding: 20px 0;
            text-align: left;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
            margin-top: 40px;
            padding-bottom: 0;
        }

        .contacts {
            padding-left: 20px;
            padding-bottom: 10px;
        }

        .copyright {
            text-align: center;
            padding: 20px;
        }

        footer {
            width: calc(100% + 40px);
            margin-left: -20px;
            margin-right: -20px;
        }

        .footer {
            border-radius: 0;
        }

        .footer .email a {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ============================================== -->
        <!-- DISPLAY QUIZ TOPIC & EDUCATOR -->
        <!-- ============================================== -->
        <div class="quiz-header">
            <h1 class="quiz-title">Quiz in <?php echo htmlspecialchars($quiz_info['topicName']); ?></h1>

            <div class="educator-section">
                <div class="educator-title">Educator:</div>
                <div class="educator-info">
                    <?php 
                    $educator_photo = !empty($quiz_info['educator_photo']) ? 'uploads/' . $quiz_info['educator_photo'] : 'images/default-user.png';
                    ?>
                    <img src="<?php echo htmlspecialchars($educator_photo); ?>" alt="Educator Photo" class="educator-photo">
                    <div class="educator-details">
                        <h3><?php echo htmlspecialchars($quiz_info['educator_firstName'] . ' ' . $quiz_info['educator_lastName']); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="question-counter">
                <span id="currentQuestion">0</span> of <?php echo count($selected_questions); ?> Questions
            </div>

            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <h2 class="quiz-section-title">Quiz Questions:</h2>

            <!-- ================================================ -->
            <!--  DISPLAY QUESTIONS IN FORM -->
            <!-- ================================================ -->
            <form id="quizForm" method="POST" action="quizFeedback.php" onsubmit="return validateForm()">
                
                <!-- REQUIREMENT 12d: Hidden input for quiz_id -->
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                
                <?php 
                $question_number = 1;
                foreach ($selected_questions as $question) {
                ?>
                
                <!--  Hidden input for question IDs -->
                <input type="hidden" name="question_ids[]" value="<?php echo $question['id']; ?>">
                
                <div class="question-container">
                    <div class="question-header">Question <?php echo $question_number; ?></div>
                    
                    <!-- Display question image if exists -->
                    <?php if (!empty($question['questionFigureFileName'])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($question['questionFigureFileName']); ?>" 
                             alt="Question Image" class="question-image">
                    <?php } ?>
                    
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['question']); ?>
                    </div>
                    
                    <div class="answer-options">
                        <div class="answer-option" onclick="selectAnswer(this)">
                            <input type="radio" 
                                   id="q<?php echo $question_number; ?>a" 
                                   name="answer_<?php echo $question['id']; ?>" 
                                   value="A" 
                                   onchange="updateProgress()">
                            <label for="q<?php echo $question_number; ?>a">
                                A) <?php echo htmlspecialchars($question['answerA']); ?>
                            </label>
                        </div>
                        
                        <div class="answer-option" onclick="selectAnswer(this)">
                            <input type="radio" 
                                   id="q<?php echo $question_number; ?>b" 
                                   name="answer_<?php echo $question['id']; ?>" 
                                   value="B" 
                                   onchange="updateProgress()">
                            <label for="q<?php echo $question_number; ?>b">
                                B) <?php echo htmlspecialchars($question['answerB']); ?>
                            </label>
                        </div>
                        
                        <div class="answer-option" onclick="selectAnswer(this)">
                            <input type="radio" 
                                   id="q<?php echo $question_number; ?>c" 
                                   name="answer_<?php echo $question['id']; ?>" 
                                   value="C" 
                                   onchange="updateProgress()">
                            <label for="q<?php echo $question_number; ?>c">
                                C) <?php echo htmlspecialchars($question['answerC']); ?>
                            </label>
                        </div>
                        
                        <div class="answer-option" onclick="selectAnswer(this)">
                            <input type="radio" 
                                   id="q<?php echo $question_number; ?>d" 
                                   name="answer_<?php echo $question['id']; ?>" 
                                   value="D" 
                                   onchange="updateProgress()">
                            <label for="q<?php echo $question_number; ?>d">
                                D) <?php echo htmlspecialchars($question['answerD']); ?>
                            </label>
                        </div>
                    </div>
                </div>
                
                <?php 
                    $question_number++;
                } 
                ?>

                <!-- ============================================= -->
                <!--  SUBMIT BUTTON TO RESULTS PAGE -->
                <!-- ============================================= -->
                <div class="submit-container">
                    <button type="submit" class="submit-btn" id="submitBtn" disabled>
                        Done
                    </button>
                </div>
            </form>
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

    <script>
        // =========================================
        // UPDATE PROGRESS BAR AND QUESTION COUNTER
        // =========================================
        function updateProgress() {
            const totalQuestions = <?php echo count($selected_questions); ?>;
            let answeredQuestions = 0;

            // Count answered questions
            const allRadios = document.querySelectorAll('input[type="radio"]');
            const questionNames = new Set();
            
            allRadios.forEach(radio => {
                if (radio.checked) {
                    questionNames.add(radio.name);
                }
            });
            
            answeredQuestions = questionNames.size;

            // Update counter
            document.getElementById('currentQuestion').textContent = answeredQuestions;

            // Update progress bar
            const progressPercentage = (answeredQuestions / totalQuestions) * 100;
            document.getElementById('progressFill').style.width = progressPercentage + '%';

            // Enable/disable submit button
            const submitBtn = document.getElementById('submitBtn');
            if (answeredQuestions === totalQuestions) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
            }
        }

        // =======================================
        // SELECT ANSWER WHEN CLICKING ON OPTION
        // =======================================
        function selectAnswer(element) {
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Remove selected class from all options in this question
            const container = element.closest('.answer-options');
            container.querySelectorAll('.answer-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            updateProgress();
        }

        // ================================================
        // VALIDATE ALL QUESTIONS ANSWERED BEFORE SUBMIT
        // ================================================
        function validateForm() {
            const totalQuestions = <?php echo count($selected_questions); ?>;
            const allRadios = document.querySelectorAll('input[type="radio"]');
            const questionNames = new Set();
            
            allRadios.forEach(radio => {
                if (radio.checked) {
                    questionNames.add(radio.name);
                }
            });
            
            if (questionNames.size < totalQuestions) {
                alert('Please answer all questions before submitting.');
                return false;
            }
            
            return true;
        }

        // ===========================
        // INITIALIZE PAGE ON LOAD
        // ===========================
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
        });
    </script>
</body>
</html>