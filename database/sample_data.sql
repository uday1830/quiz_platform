
USE quiz_platform;

-- Insert a sample quiz
INSERT INTO quizzes (title, description, time_limit, created_by, is_active) VALUES
('General Knowledge Quiz', 'Test your general knowledge with this fun quiz!', 15, 1, 1);

SET @quiz_id = LAST_INSERT_ID();

-- Insert sample questions
INSERT INTO questions (quiz_id, question_text, question_order) VALUES
(@quiz_id, 'What is the capital of France?', 1),
(@quiz_id, 'Which planet is known as the Red Planet?', 2),
(@quiz_id, 'Who wrote "Romeo and Juliet"?', 3),
(@quiz_id, 'What is the largest ocean on Earth?', 4),
(@quiz_id, 'In which year did World War II end?', 5);

-- Get question IDs
SET @q1 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_order = 1);
SET @q2 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_order = 2);
SET @q3 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_order = 3);
SET @q4 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_order = 4);
SET @q5 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_order = 5);

-- Insert options for Question 1
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@q1, 'London', 0, 1),
(@q1, 'Paris', 1, 2),
(@q1, 'Berlin', 0, 3),
(@q1, 'Madrid', 0, 4);

-- Insert options for Question 2
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@q2, 'Venus', 0, 1),
(@q2, 'Mars', 1, 2),
(@q2, 'Jupiter', 0, 3),
(@q2, 'Saturn', 0, 4);

-- Insert options for Question 3
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@q3, 'Charles Dickens', 0, 1),
(@q3, 'William Shakespeare', 1, 2),
(@q3, 'Jane Austen', 0, 3),
(@q3, 'Mark Twain', 0, 4);

-- Insert options for Question 4
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@q4, 'Atlantic Ocean', 0, 1),
(@q4, 'Indian Ocean', 0, 2),
(@q4, 'Pacific Ocean', 1, 3),
(@q4, 'Arctic Ocean', 0, 4);

-- Insert options for Question 5
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@q5, '1943', 0, 1),
(@q5, '1944', 0, 2),
(@q5, '1945', 1, 3),
(@q5, '1946', 0, 4);

-- Insert another quiz
INSERT INTO quizzes (title, description, time_limit, created_by, is_active) VALUES
('Science Quiz', 'Test your scientific knowledge!', 20, 1, 1);

SET @quiz_id2 = LAST_INSERT_ID();

-- Insert science questions
INSERT INTO questions (quiz_id, question_text, question_order) VALUES
(@quiz_id2, 'What is the chemical symbol for gold?', 1),
(@quiz_id2, 'How many bones are in the human body?', 2),
(@quiz_id2, 'What is the speed of light?', 3);

-- Get question IDs for quiz 2
SET @sq1 = (SELECT id FROM questions WHERE quiz_id = @quiz_id2 AND question_order = 1);
SET @sq2 = (SELECT id FROM questions WHERE quiz_id = @quiz_id2 AND question_order = 2);
SET @sq3 = (SELECT id FROM questions WHERE quiz_id = @quiz_id2 AND question_order = 3);

-- Insert options for Science Question 1
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@sq1, 'Go', 0, 1),
(@sq1, 'Au', 1, 2),
(@sq1, 'Gd', 0, 3),
(@sq1, 'Ag', 0, 4);

-- Insert options for Science Question 2
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@sq2, '196', 0, 1),
(@sq2, '206', 1, 2),
(@sq2, '216', 0, 3),
(@sq2, '226', 0, 4);

-- Insert options for Science Question 3
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(@sq3, '299,792,458 m/s', 1, 1),
(@sq3, '300,000,000 m/s', 0, 2),
(@sq3, '250,000,000 m/s', 0, 3),
(@sq3, '350,000,000 m/s', 0, 4);

SELECT 'Sample data inserted successfully!' as message;
