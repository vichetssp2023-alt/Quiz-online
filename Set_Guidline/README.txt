Project Setup Guide / ការណែនាំអំពីការដំឡើង
1. Prerequisite / តម្រូវការជាមុន
Web Server: XAMPP, WampServer, or Laragon (PHP 8.x recommended).
Database: MySQL.
Editor: VS Code or Sublime Text (optional).

2. Database Installation / ការដំឡើងមូលដ្ឋានទិន្នន័យ
Open phpMyAdmin (http://localhost/phpmyadmin).
Create a new database named: quiz_online_db.
Click the Import tab and select the file quiz_online_db (1).sql from the project folder.
Click Go to execute. This will create all tables, including users, questions, and results etc;

3. Project Configuration / ការកំណត់ក្នុង Project
Extract the ASSIGNMENT.rar folder and move it to your server directory (e.g., C:/xampp/htdocs/online_quiz/).
Open the database connection file (usually db_connect.php or config.php).
Verify the connection settings:
Host: localhost
User: root
Password: "" (empty)
DB Name: quiz_online_db

4. How to Use / របៀបប្រើប្រាស់
Start Apache and MySQL in XAMPP.
Open your browser and go to: http://localhost/online_quiz/
Test Accounts / គណនីសាកល្បង:
Admin: username / Password: 123
User/Student: username / Password: 123
Teacher: username / password : "";