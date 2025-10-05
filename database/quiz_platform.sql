-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 12:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

CREATE DATABASE IF NOT EXISTS `quiz_platform` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `quiz_platform`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `attempts`
--

CREATE TABLE `attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `status` enum('in_progress','completed') DEFAULT 'in_progress',
  `score` decimal(5,2) DEFAULT 0.00,
  `total_questions` int(11) DEFAULT 0,
  `correct_answers` int(11) DEFAULT 0,
  `time_taken` int(11) DEFAULT NULL COMMENT 'Time taken in seconds',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `remaining_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answers`
--

CREATE TABLE `attempt_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `marked_for_review` tinyint(1) DEFAULT 0,
  `time_spent` int(11) DEFAULT 0 COMMENT 'Time spent in seconds',
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `option_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `question_id`, `option_text`, `is_correct`, `option_order`, `created_at`) VALUES
(1, 1, 'London', 0, 1, '2025-10-04 18:07:23'),
(2, 1, 'Paris', 1, 2, '2025-10-04 18:07:23'),
(3, 1, 'Berlin', 0, 3, '2025-10-04 18:07:23'),
(4, 1, 'Madrid', 0, 4, '2025-10-04 18:07:23'),
(5, 2, 'Venus', 0, 1, '2025-10-04 18:07:23'),
(6, 2, 'Mars', 1, 2, '2025-10-04 18:07:23'),
(7, 2, 'Jupiter', 0, 3, '2025-10-04 18:07:23'),
(8, 2, 'Saturn', 0, 4, '2025-10-04 18:07:23'),
(9, 3, 'Charles Dickens', 0, 1, '2025-10-04 18:07:23'),
(10, 3, 'William Shakespeare', 1, 2, '2025-10-04 18:07:23'),
(11, 3, 'Jane Austen', 0, 3, '2025-10-04 18:07:23'),
(12, 3, 'Mark Twain', 0, 4, '2025-10-04 18:07:23'),
(13, 4, 'Atlantic Ocean', 0, 1, '2025-10-04 18:07:23'),
(14, 4, 'Indian Ocean', 0, 2, '2025-10-04 18:07:23'),
(15, 4, 'Pacific Ocean', 1, 3, '2025-10-04 18:07:23'),
(16, 4, 'Arctic Ocean', 0, 4, '2025-10-04 18:07:23'),
(17, 5, '1943', 0, 1, '2025-10-04 18:07:23'),
(18, 5, '1944', 0, 2, '2025-10-04 18:07:23'),
(19, 5, '1945', 1, 3, '2025-10-04 18:07:23'),
(20, 5, '1946', 0, 4, '2025-10-04 18:07:23'),
(21, 6, 'Go', 0, 1, '2025-10-04 18:07:23'),
(22, 6, 'Au', 1, 2, '2025-10-04 18:07:23'),
(23, 6, 'Gd', 0, 3, '2025-10-04 18:07:23'),
(24, 6, 'Ag', 0, 4, '2025-10-04 18:07:23'),
(25, 7, '196', 0, 1, '2025-10-04 18:07:23'),
(26, 7, '206', 1, 2, '2025-10-04 18:07:23'),
(27, 7, '216', 0, 3, '2025-10-04 18:07:23'),
(28, 7, '226', 0, 4, '2025-10-04 18:07:23'),
(29, 8, '299,792,458 m/s', 1, 1, '2025-10-04 18:07:23'),
(30, 8, '300,000,000 m/s', 0, 2, '2025-10-04 18:07:23'),
(31, 8, '250,000,000 m/s', 0, 3, '2025-10-04 18:07:23'),
(32, 8, '350,000,000 m/s', 0, 4, '2025-10-04 18:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `question_order`, `created_at`) VALUES
(1, 1, 'What is the capital of France?', 1, '2025-10-04 18:07:23'),
(2, 1, 'Which planet is known as the Red Planet?', 2, '2025-10-04 18:07:23'),
(3, 1, 'Who wrote \"Romeo and Juliet\"?', 3, '2025-10-04 18:07:23'),
(4, 1, 'What is the largest ocean on Earth?', 4, '2025-10-04 18:07:23'),
(5, 1, 'In which year did World War II end?', 5, '2025-10-04 18:07:23'),
(6, 2, 'What is the chemical symbol for gold?', 1, '2025-10-04 18:07:23'),
(7, 2, 'How many bones are in the human body?', 2, '2025-10-04 18:07:23'),
(8, 2, 'What is the speed of light?', 3, '2025-10-04 18:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `time_limit` int(11) NOT NULL COMMENT 'Time limit in minutes',
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `description`, `time_limit`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'General Knowledge Quiz', 'Test your general knowledge with this fun quiz!', 15, 1, 1, '2025-10-04 18:07:23', '2025-10-04 18:07:23'),
(2, 'Science Quiz', 'Test your scientific knowledge!', 20, 1, 1, '2025-10-04 18:07:23', '2025-10-04 18:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@quiz.com', '0192023a7bbd73250516f069df18b500', 'admin', '2025-10-04 18:07:07'),
(2, 'Test User', 'user@quiz.com', '6ad14ba9986e3615423dfca256d04e3f', 'user', '2025-10-04 18:07:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attempts`
--
ALTER TABLE `attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `idx_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attempt_question` (`attempt_id`,`question_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `selected_option_id` (`selected_option_id`),
  ADD KEY `idx_attempt` (`attempt_id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attempts`
--
ALTER TABLE `attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attempts`
--
ALTER TABLE `attempts`
  ADD CONSTRAINT `attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD CONSTRAINT `attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `options` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
