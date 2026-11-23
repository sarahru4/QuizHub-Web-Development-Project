# QuizHub

**QuizHub** is a dynamic web-based quiz platform where **educators** build and manage quizzes on technical topics, and **learners** take quizzes, get instant scores, leave feedback, and recommend new questions for educators to review.

Built as a Web Development course project using **PHP**, **MySQL**, **JavaScript (AJAX / jQuery)**, **HTML**, and **CSS**.

---

## Features

### Authentication
- Sign up as a **Learner** or an **Educator**, with a profile photo upload
- Educators choose the topics they teach at sign-up (AI, CS, IoT) — a quiz is created automatically for each topic
- Secure login with hashed passwords (`password_hash` / `password_verify`)
- Session-based access control: each page checks the user's role and redirects unauthorized users

### Learner
- Homepage listing all available quizzes with the educator's name and photo
- **Filter quizzes by topic** without reloading the page (AJAX)
- Take a quiz and get an instant score
- Rate a quiz and leave written feedback
- **Recommend a question** (with an optional figure) to an educator, and track its status (pending / approved / disapproved) along with the educator's comments

### Educator
- Dashboard showing their quizzes with statistics (number of takers, average score, feedback)
- **Add, edit, and delete** quiz questions, including images
- **Review recommended questions** from learners: approve (adds the question to the quiz) or disapprove, with comments — handled via AJAX
- View learners' comments and ratings for each quiz

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, jQuery, AJAX (Fetch API) |
| Backend | PHP (MySQLi, sessions) |
| Database | MySQL |
| Local server | MAMP |
| Hosting | InfinityFree |

---

## Database Schema

The database (`quizhub.sql`) contains the following tables:

| Table | Description |
|---|---|
| `user` | Learners and educators (name, email, hashed password, photo, user type) |
| `topic` | Quiz topics: AI, CS, IoT |
| `quiz` | Links each educator to a topic |
| `quizquestion` | Multiple-choice questions (4 options, correct answer, optional figure) |
| `takenquiz` | Scores of completed quizzes |
| `quizfeedback` | Ratings and comments on quizzes |
| `recommendedquestion` | Questions suggested by learners, with review status and educator comments |

---

## Project Structure

```
QuizHub-Web-Development-Project/
├── quizhub.sql                  # Database dump (schema + sample data)
└── QuizHub/
    ├── index.php                # Landing page
    ├── signUp.php               # Registration (learner / educator)
    ├── logIn.php                # Login
    ├── logout.php
    ├── db_config.php            # Database connection settings
    │
    ├── learner_homepage.php     # Learner dashboard
    ├── fetch_quizzes.php        # AJAX: filter quizzes by topic
    ├── get_educators.php        # AJAX: list educators for a topic
    ├── TakeQuiz.php             # Take a quiz
    ├── quiz.php                 # Quiz page
    ├── quizFeedback.php         # Score + rating & feedback
    ├── recommend_question.php   # Recommend a question
    │
    ├── Educator.php             # Educator dashboard
    ├── add-question.php / add.php
    ├── edit-question.php / edit.php
    ├── delete-question.php
    ├── review_question.php      # Review learners' recommendations
    ├── review_question_ajax.php # AJAX: approve / disapprove
    ├── Comments.php             # Quiz comments
    │
    ├── style.css / style1.css
    ├── logo1.png
    ├── images/                  # Site assets (logo, icons, backgrounds, illustrations)
    └── uploads/                 # User profile photos and question figures
```

---

## Getting Started

### Prerequisites
- [MAMP](https://www.mamp.info/) (or XAMPP / any PHP + MySQL server)
- PHP 7.4 or later
- MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/sarahru4/QuizHub-Web-Development-Project.git
   ```

2. **Move the project folder** into your server's web root
   - MAMP: `/Applications/MAMP/htdocs/`
   - XAMPP: `C:\xampp\htdocs\`

3. **Create the database**
   - Open phpMyAdmin
   - Create a database named `quizhub`
   - Import `quizhub.sql`

4. **Configure the connection** in `QuizHub/db_config.php` if needed. The defaults are set for MAMP:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'root');
   define('DB_NAME', 'quizhub');
   // MySQL port: 8889 (MAMP default). Use 3306 for XAMPP.
   ```

5. **Run the app** by visiting:
   ```
   http://localhost:8888/QuizHub/index.php
   ```

---

## How It Works

```
Sign up / Log in
      │
      ├── Learner ──► Browse & filter quizzes ──► Take quiz ──► Score + feedback
      │                       │
      │                       └──► Recommend a question ──┐
      │                                                    ▼
      └── Educator ──► Manage questions ◄── Approve / disapprove recommendations
```
