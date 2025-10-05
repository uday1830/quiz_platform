# Quiz Platform

A full-stack web-based quiz platform built with core PHP, MySQL, and Bootstrap(included in project).
This Application has two modules User Modules and Admin module.
If the logged in user is not admin then the user can see the avalaible quizes and participate in the quiz and can see their responses.
The Admin can login to the system can see the results of the tests and can add more quizes or remove the quizes.


## Features

### User Features
- User registration and login
- Browse available quizzes
- Take quizzes with real-time question navigation
- Question status indicators (Answered, Unanswered, Mark for Review)
- Color-coded question palette
- Quiz timer with countdown
- Submit confirmation popup
- View quiz attempt history
- Response Sheet
- Leaderboard with user ranking and highlighting

### Admin Features
- Admin dashboard with statistics
- Create, edit, and delete quizzes
- Manage questions for each quiz
- Add, edit, and delete options for questions
- Mark correct answers
- View all user attempts and scores
- Filter attempts by quiz or user

## Technology Stack

- **Backend**: Core PHP
- **Database**: MySQL
- **Frontend**: Bootstrap 5.3.0 (included locally)
- **Server**: XAMPP (Apache + MySQL)

## Installation Instructions

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP and start Apache and MySQL services

### Step 2: Setup Database
1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Choose the file: `database/setup.sql`
4. Click "Go" to execute the SQL script

This will create:
- Database: `quiz_platform`
- All required tables
- Default admin user: `admin@quiz.com` / `admin123`
- Default test user: `user@quiz.com` / `user123`

### Step 3: Deploy Project Files
1. Copy all project files to XAMPP's `htdocs` directory:
   ```
   C:\xampp\htdocs\quiz_platform\
   ```

2. Project structure should look like:
   ```
   htdocs/quiz_platform/
   ├── admin/
   ├── assets/
   ├── config/
   ├── database/
   ├── includes/
   ├── user/
   ├── index.php
   ├── login.php
   ├── register.php
   └── logout.php
   ```

### Step 4: Configure Database Connection
1. Open `config/database.php`
2. Verify the database credentials (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'quiz_platform');
   ```

### Step 5: Access the Application
1. Open your browser and go to: [http://localhost/quiz_platform](http://localhost/quiz_platform)
2. You will be redirected to the login page

## Default Login Credentials

### Admin Account
- Email: `admin@quiz.com`
- Password: `admin123`

### Test User Account
- Email: `user@quiz.com`
- Password: `user123`

## Usage Guide

### For Admin Users

1. **Login as Admin**
   - Use admin credentials to login
   - You'll be redirected to the admin dashboard

2. **Create a Quiz**
   - Click "Manage Quizzes" in the sidebar
   - Click "Create New Quiz"
   - Fill in quiz details (title, description, time limit)
   - Click "Create Quiz"

3. **Add Questions**
   - After creating a quiz, you'll be redirected to add questions
   - Click "Add Question"
   - Enter question text
   - Click "Add Question"

4. **Add Options**
   - After adding a question, add options
   - Enter option text
   - Check "This is the correct answer" for the correct option
   - Add at least 2 options per question

5. **View Attempts**
   - Click "View Attempts" to see all user quiz attempts
   - Filter by quiz or user
   - View scores, time taken, and completion dates

### For Regular Users

1. **Register/Login**
   - Register a new account or login with existing credentials
   - You'll be redirected to the user dashboard

2. **Take a Quiz**
   - Click "Start Quiz" on any available quiz
   - Answer questions by selecting options
   - Use question number buttons to navigate
   - Click "Mark for Review" to flag questions

3. **Question Status Colors**
   - **Grey**: Unanswered
   - **Green**: Answered
   - **Orange**: Marked for review

4. **Submit Quiz**
   - Click "Submit Quiz" when ready
   - Confirm submission in the popup
   - View your score and leaderboard

5. **View Leaderboard**
   - See your rank compared to other users
   - Your row will be highlighted in yellow
   - Top 3 ranks shown with gold, silver, bronze badges

6. **View Response**
   - If answer correct option highlighted in green
   - If false it will be highlighted in the red 
   - If no answer then the correct answer will be highlighted

## Features Explained

### Quiz Taking Interface
- **Question Palette**: Grid of all question numbers with color status
- **Real-time Stats**: Shows total, answered, unanswered, and review counts
- **Timer**: Countdown timer with color warnings
- **Navigation**: Previous/Next buttons and direct question navigation
- **Mark for Review**: Toggle button to flag questions

### Leaderboard System
- Ranks users by score (highest first)
- Secondary ranking by time taken (fastest first)
- Current user's row highlighted
- Shows top 100 scores

### Admin Panel
- Dashboard with statistics
- Complete CRUD operations for quizzes, questions, and options
- View all user attempts with filtering
- Toggle quiz active/inactive status

## Security Features

- Role-based access control (User vs Admin)
- Session management with secure cookies
- SQL injection prevention with prepared statements
- Password hashing (MD5)
- Protected admin routes



### Tables
- **users**: User accounts and roles
- **quizzes**: Quiz definitions
- **questions**: Quiz questions
- **options**: Question options
- **attempts**: Quiz attempts
- **attempt_answers**: User answers with review status

## Support

For issues or questions:
1. Check XAMPP Apache and MySQL are running
2. Verify database is imported correctly
3. Check file permissions
4. Review Apache error logs

## Test Case(User interface)
1.Click on register 
2.Enter the email and the password
3.After registering click on login
4.Enter the registered email and password.
5.The application already contains 2 quizes.
6.Select the Science Quiz or the General Knowledge Quiz 
7.See the time wheather it is ticking or not.
8.select the option (Single Option type)
9.Move to the next question or choose from the question pallete 
10.Click on Submit Quiz to submit the result
11.Click Confirm submit to move forward.
12.You can see the leaderboard.
13.Click on view response to see the correct answers of the Quiz

## (Admin interface)
1.Enter the existing admin credentials while logging in.
2.Admin dashboard containing all the tests will be visible
3.In Manage Quizes we can add/edit/delete the quiz
4.Try to add the quiz and its questions and options make it active and login as the normal user wheather it redirects or not





