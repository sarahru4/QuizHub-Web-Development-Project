-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 30, 2025 at 08:23 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quizhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `educatorID` int(11) NOT NULL,
  `topicID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `educatorID`, `topicID`) VALUES
(1, 15, 1),
(2, 15, 2),
(3, 15, 3);

-- --------------------------------------------------------

--
-- Table structure for table `quizfeedback`
--

CREATE TABLE `quizfeedback` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text,
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `quizfeedback`
--

INSERT INTO `quizfeedback` (`id`, `quizID`, `rating`, `comments`, `date`) VALUES
(3, 1, 5, 'Excellent quiz! Very informative questions.', '2024-10-20 10:30:00'),
(4, 1, 4, 'Good quiz, but some questions were too difficult.', '2024-10-21 14:15:00'),
(5, 1, 5, 'Great learning experience!', '2024-10-22 09:45:00'),
(6, 2, 4, 'Helpful for understanding cybersecurity basics.', '2024-10-23 11:20:00'),
(7, 2, 4, 'Well-structured quiz.', '2024-10-24 16:00:00'),
(8, 1, 5, '', '2025-11-20 00:19:41');

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `question` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `questionFigureFileName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answerA` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerB` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerC` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerD` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correctAnswer` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quizID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`) VALUES
(1, 1, 'What is the primary goal of machine learning?', 'quiz1_q1.png', 'To replace human intelligence completely', 'To enable computers to learn and improve from experience', 'To create robots that look like humans', 'To make computers faster', 'B'),
(2, 1, 'Which algorithm is commonly used for supervised learning classification?', 'quiz1_q2.png', 'K-means clustering', 'Decision Tree', 'DBSCAN', 'Apriori algorithm', 'B'),
(3, 1, 'What is the function of an activation function in neural networks?', 'quiz1_q3.jpg', 'To store network weights', 'To introduce non-linearity into the network', 'To connect different layers', 'To reduce computation time', 'B'),
(4, 1, 'What does NLP stand for in artificial intelligence?', 'quiz1_q4.png', 'Neural Learning Process', 'Natural Language Processing', 'Network Layer Protocol', 'Numerical Linear Programming', 'B'),
(5, 1, 'Which technique is used to prevent overfitting in deep learning models?', 'quiz1_q5.png', 'Increasing learning rate', 'Dropout regularization', 'Adding more layers', 'Using larger datasets only', 'B'),
(6, 2, 'What is the primary purpose of a firewall?', NULL, 'Speed up internet', 'Filter network traffic', 'Store passwords', 'Encrypt files', 'B'),
(7, 2, 'What does VPN stand for?', NULL, 'Virtual Private Network', 'Very Private Network', 'Virtual Public Network', 'Verified Private Network', 'A'),
(8, 2, 'Which of the following is a strong password practice?', NULL, 'Using the same password everywhere', 'Using a mix of letters, numbers, and symbols', 'Writing passwords on sticky notes', 'Sharing passwords with colleagues', 'B'),
(9, 3, 'Which communication protocol is commonly used in IoT devices for low-power applications?', 'quiz3_q9.png', 'HTTP', 'FTP', 'MQTT', 'SMTP', 'C'),
(10, 3, 'What does IP stand for in networking?', NULL, 'Internet Protocol', 'Internal Protocol', 'Interface Protocol', 'Integrated Protocol', 'A'),
(12, 3, 'In the OSI model which layer is responsible for end-to-end communication and error recovery?', 'quiz3_q12.png', 'Transport', 'Network', 'Data Link', 'Physical', 'A'),
(14, 3, 'What technology is commonly used by IoT devices to connect wirelessly at home?', NULL, 'USB', 'HDMI', 'Bluetooth', 'Ethernet only', 'C'),
(15, 3, 'Which device is used to connect different networks together?', NULL, 'Switch', 'Router', 'Repeater', 'Hub', 'B'),
(16, 1, 'Which field of AI focuses on teaching machines to learn from data?', NULL, 'Robotics', 'Machine Learning', 'Networking', 'Cryptography', 'B'),
(17, 1, 'Which programming language is widely used in AI development?', NULL, 'HTML', 'CSS', 'Python', 'SQL', 'C'),
(18, 2, 'Which of these is the safest way to connect to public Wi-Fi?', NULL, 'Using a VPN', 'By disabling antivirus', 'Without any protection', 'Using incognito mode', 'A'),
(19, 2, 'How often should you change your passwords for important accounts?', NULL, 'Every 5 years', 'Only if you forget it', 'Regularly (every few months)', 'Never', 'C'),
(20, 3, 'Which wireless protocol is commonly used in smart home devices?', 'qfig_6913d0dd1c68c.png', 'HTTP', 'FTP', 'Zigbee', 'SMTP', 'C');

-- --------------------------------------------------------

--
-- Table structure for table `recommendedquestion`
--

CREATE TABLE `recommendedquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `learnerID` int(11) NOT NULL,
  `question` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `questionFigureFileName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answerA` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerB` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerC` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answerD` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correctAnswer` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recommendedquestion`
--

INSERT INTO `recommendedquestion` (`id`, `quizID`, `learnerID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`, `status`, `comments`) VALUES
(5, 1, 13, 'Which algorithm is commonly used for training neural networks?', 'qfig_6913d066b2f6e.png', 'Linear Search', 'Backpropagation', 'Bubble Sort', 'Binary Search', 'B', 'pending', ''),
(6, 3, 13, 'Which wireless protocol is commonly used in smart home devices?', 'qfig_6913d0dd1c68c.png', 'HTTP', 'FTP', 'Zigbee', 'SMTP', 'C', 'approved', ''),
(7, 2, 14, 'What is the primary purpose of encryption in cybersecurity?', '', 'Speed up internet', 'Protect data confidentiality', 'Store passwords', 'Monitor network traffic', 'B', 'pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `takenquiz`
--

CREATE TABLE `takenquiz` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `takenquiz`
--

INSERT INTO `takenquiz` (`id`, `quizID`, `score`) VALUES
(1, 1, '85.00'),
(2, 1, '90.00'),
(3, 1, '75.00'),
(4, 1, '80.00'),
(5, 1, '88.00'),
(6, 2, '78.00'),
(7, 2, '82.00'),
(8, 2, '70.00'),
(9, 2, '73.00');

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(11) NOT NULL,
  `topicName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `topicName`) VALUES
(1, 'AI'),
(2, 'CS'),
(3, 'IOT');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emailAddress` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photoFileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userType` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`, `userType`) VALUES
(13, 'Hoor', 'Talal', 'H@gmail.com', '$2y$10$kaMx0eWLOwTlfG4lGiBek.y9GslY./zMCc.qDXOd1PKRR9KpK4V62', 'profile1.jpg', 'learner'),
(14, 'Huda', 'Ali', 'U@gmail.com', '$2y$10$9QfTmNwlc4a3xvtayvMmmOivctUwQYXJVBB5LB/8x0Wa.WfQLL5fa', 'profile2.jpg', 'learner'),
(15, 'Ahmed', 'Saud', 'A@gmail.com', '$2y$10$a0z6nBQ7LvqZ8oBf7mLpHOpk/V.RPrWYklI9rD0mboPVSHrGoNO2G', 'profile3.jpg', 'educator');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educatorID` (`educatorID`),
  ADD KEY `topicID` (`topicID`);

--
-- Indexes for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`),
  ADD KEY `learnerID` (`learnerID`);

--
-- Indexes for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `takenquiz`
--
ALTER TABLE `takenquiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
