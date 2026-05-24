-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2026 at 04:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz_online_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `description`, `status`, `created_at`) VALUES
(1, 'Philippines Class', 'ថ្នាក់រៀនសម្រាប់សិស្សហ្វីលីពីន', 'active', '2026-03-15 16:25:05'),
(2, 'Vietnam Class', 'ថ្នាក់រៀនសម្រាប់សិស្សវៀតណាម', 'active', '2026-03-15 16:25:05'),
(3, 'Cambodia Class', 'ថ្នាក់រៀនសម្រាប់សិស្សកម្ពុជា', 'active', '2026-03-15 16:25:05'),
(4, 'Laos Class', 'ថ្នាក់សិស្សប្រទេសឡាវ', 'active', '2026-03-15 16:25:05'),
(5, 'International Class', 'ថ្នាក់រៀនអន្តរជាតិចម្រុះ', 'active', '2026-03-15 16:25:05');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(28, 18, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(29, 23, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(30, 26, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(31, 27, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(32, 28, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(33, 29, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់របស់អ្នក!', 'គ្រូបានដាក់វិញ្ញាសាថ្មី៖ CAMBODIA', 0, '2026-03-22 12:03:28'),
(34, 24, 'វិញ្ញាសាថ្មីសម្រាប់ថ្នាក់!', 'គ្រូបានដាក់វិញ្ញាសា៖ Ch2- Quiz 1 Database ', 0, '2026-03-23 04:08:58'),
(35, 1, 'គ្រូដាក់វិញ្ញាសាថ្មី!', 'គ្រូ SAKIN BUNHON បានដាក់វិញ្ញាសាថ្មី៖ Ch2- Quiz 1 Database ', 0, '2026-03-23 04:08:58'),
(37, 17, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស KOEUN KDEB បានបញ្ចប់វិញ្ញាសា \'Quiz 1 .NET\' ជាមួយពិន្ទុ 94%។', 0, '2026-03-23 05:58:43'),
(38, 12, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស TEP KANHA  បានបញ្ចប់វិញ្ញាសា \'Using PHP with MySQL\' ជាមួយពិន្ទុ 75%។', 0, '2026-03-25 10:12:11'),
(39, 12, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស Sok Somnang  បានបញ្ចប់វិញ្ញាសា \'Using PHP with MySQL\' ជាមួយពិន្ទុ 75%។', 0, '2026-03-25 12:07:59'),
(40, 24, 'វិញ្ញាសាថ្មី!', 'មេរៀន៖ Graphic Disgn Quiz 1', 0, '2026-03-27 05:37:43'),
(41, 1, 'មានវិញ្ញាសាថ្មី!', 'លោកគ្រូ KEO DANIN បានដាក់វិញ្ញាសាថ្មី។', 0, '2026-03-27 05:37:43'),
(42, 12, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស TEP KANHA  បានបញ្ចប់វិញ្ញាសា \'Graphic Disgn Quiz 1\' ជាមួយពិន្ទុ 67%។', 0, '2026-03-27 06:18:25'),
(43, 12, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស  បានបញ្ចប់វិញ្ញាសា \'Using PHP with MySQL\' ជាមួយពិន្ទុ 100%។', 0, '2026-03-27 08:51:25'),
(44, 17, 'សិស្សបានបញ្ជូនកិច្ចការ', 'សិស្ស  បានបញ្ចប់វិញ្ញាសា \'Quiz 1 .NET\' ជាមួយពិន្ទុ 88%។', 0, '2026-03-28 07:34:20');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(25, 22, 'គេចង់ Select all columns from table staff តើគេត្រូវសរសេរ Query ដូចម្តេច?', 'a. SELECT staff.*;', ' b. SELECT staff.ALL;', ' c. SELECT staff FROM *;', 'd. SELECT * FROM staff;', 'D'),
(26, 22, 'ដើម្បីបង្ហាញដេតាបេសទាំងអស់នៅក្នុង CMD តើត្រូវ Command អ្វី?', ' a. DATABASES;', ' b. ALL DATABASES;', ' c. SHOW DATABASES;', 'd. USE DATABASES;', 'C'),
(27, 22, 'បើសិនជាគេចង់ Connect PHP ទៅកាន់ Ms. SQL Server តើត្រូវប្រើ Connection មួយណា?', ' a. mysqli Object Oriented', ' b. PDO', 'c. Localhost', 'd. Query', 'B'),
(28, 22, 'នៅក្នុង PHP និង MySQL គេប្រើ prepare() + bind_param() + execute() ដើម្បីអ្វី?', ' a. Preventing SQL injection', 'b. Run Query Faster', ' c. Creating Database', ' d. Automatic Backup', 'A'),
(29, 22, 'តើខាងក្រោមមួយណាជា​ SQL Statement សម្រាប់កែប្រែទិន្នន័យក្នុង Table?', ' a. MODIFY', ' b. ALTER', 'c. CHANGE', ' d. UPDATE', 'D'),
(30, 22, 'ពាក្យពេញ PDO', 'a. Personal Data Organization', ' b. Project Document Oriented', ' c. PHP Data Object', 'd. Project Data Object', 'C'),
(31, 22, 'យើងអាច Connect ទៅកាន់ MySQL បានប៉ុន្មានបែប?', ' a. 3', 'b. 2', ' c. 4', ' d. 5', 'A'),
(32, 22, 'ចម្លើយខាងក្រោម តើមួយណាជាការ Run Query សម្រាប់ mysqli Object Oriented', 'a. $con->query(Query-String);', 'b. $con->execute(Query-String);', 'c. $on->mysqli', ' d. mysqli_query($con, Query-String);', 'A'),
(34, 23, 'តើបច្ចេកវិទ្យា .NET បង្កើតឡើងដោយក្រុមហ៊ុនណា? ហើយ .NET Core បង្កើតនៅឆ្នាំណា?', ' a. ក្រុមហ៊ុន Google, ឆ្នាំ​ 2000', 'b. ក្រុមហ៊ុន Google, ឆ្នាំ​ 2002', 'c. ក្រុមហ៊ុន Microsoft, ឆ្នាំ​ 2014', ' d. ក្រុមហ៊ុន Microsoft, ឆ្នាំ​ 2002', 'C'),
(35, 23, 'តើប្រភេទទិន្នន័យមួយណាដែលមិនមែនជាលេខ?', ' a. String, Boolean', ' b. Float, double', ' c. sbyte, byte, short, int, long, char', ' d. Decimal', 'A'),
(36, 23, 'តើការបង្កើតអថេរ​ (declare variable) មួយណាដែលត្រឹមត្រូវ?', ' a. int i j k =10;', ' b. int i; j; k;', ' c. int i, j, k;', 'd. double', 'C'),
(37, 23, 'ចូរបំពេញចន្លោះកូដត្រឹមត្រូវ\r\n\r\nConsole. _____ (“Hello World!”);', ' a. WriteLine', 'b. Print', 'c. PrintLn', 'd. Output', 'A'),
(38, 23, 'តើកូដខាងក្រោមចេញលទ្ធផលប៉ុន្មាន?\r\n\r\nConsole.WriteLine(5 + 5);', ' a. 55', ' b. 10', ' c. 5+5', 'd. Syntax error', 'B'),
(39, 23, 'តើភាពខុសគ្នារវាង Write()​ និង WriteLine() ក្នុងភាសា C# មានន័យដូចម្ដេច?', ' a. បង្ហាញអត្ថបទបានដូចគ្នា និងមិនមានអ្វីខុសគ្នាឡើយ ', ' b. WriteLine() ប្រើសម្រាប់បង្ហាញលេខ។ Write() ប្រើសម្រាប់បង្ហាញអក្សរ។', ' c. Write() ប្រើសម្រាប់បង្ហាញអត្ថបទមិនមានចុះដើមបន្ទាត់ (without New Line)​។ WriteLine() ប្រើ​សម្រាប់អត្ថបទដែលមានចុះដើមបន្ទាត់សម្រាប់បង្ហាញអត្ថបទក្រោមបន្ទាប់ (next line after printing) ។', 'd.nothing correct answer', 'C'),
(40, 23, 'តើនិមិត្តសញ្ញាដែលប្រើសម្រាប់ single-line comment ក្នុង C# ?', ' a. /*', ' b. #', ' c. --', ' d. //', 'D'),
(41, 23, 'ចូរបំពេញចន្លោះខាងក្រោម ដើម្បីបង្កើតអថេរបាន។\r\n\r\n______myNum = 15;', ' a. double', ' b. string', ' c. int', ' d. float', 'C'),
(42, 23, 'តើប្រភេទនិន្ន័យមួយដែលប្រើសម្រាប់ផ្ទុកតម្លៃជាអក្សរក្នុង C#?', ' a. double', ' b. char', ' c. int', ' d. string', 'D'),
(43, 23, 'តើ implicit casting ក្នុង C# ជាអ្វី?', ' a. Converts a string to an integer', ' b. Automatically converting a smaller type to a larger type size', ' c. Converts an integer to a double', ' d. Manually converting one type to another.', 'B'),
(44, 23, 'Which of the following requires explicit casting?', ' a. char to int', ' b. int to double', ' c. int to long', ' d. double to int', 'D'),
(45, 23, 'តើកូដខាងក្រោមមួយណាត្រឹមត្រូវដែលប្រើ \r\nExplicit Casting​ សម្រាប់អថេរ\r\n myDouble ទៅកាន់ Integer ?\r\n\r\ndouble myDouble = 5.49;\r\nint myInt = (_________) myDouble;', 'a. long', ' b. double', ' c. int', ' d. float', 'C'),
(46, 23, 'ក្នុងភាសា C# គេប្រើ Convert.ToString(myInt) ធ្វើអ្វី?', ' a. Converts a double to a string', ' b. Converts an integer to a double', ' c. Converts a string to an integer', ' d. Converts an integer to a string', 'D'),
(47, 23, 'តើ Method មួយណាដែលក្នុងភាសា C# គេប្រើសម្រាប់ទទួលតម្លៃចេញពី Keyboard តាមរយៈ Console?', ' a. Console.GetInput()', ' b. Console.Input()', ' c. Console.WriteLine()', ' d. Console.ReadLine()', 'D'),
(48, 23, 'ជ្រើសរើសចម្លើយត្រឹមត្រូវរបស់កូដខាងក្រោម៖\r\n\r\nConsole.WriteLine(“Think of a number:”);\r\n\r\nint myNum = Convert._____(Console.______);', ' a. ToInt, Console.ReadLine()', ' b. ToInt32, ReadLine()', ' c. Parse, ReadLine()', ' d. ToDouble, ReadLine()', 'B'),
(49, 23, 'ក្នុងភាសា C# ដែលគេប្រើនិមិត្តសញ្ញាសម្រាប់កើនតម្លៃនៃអថេរ x ដោយ 1 ៖​ int x = 10;', ' a. x +=1;', ' b. x +1=;', ' c. x++;', ' d. x=11;', 'C'),
(50, 23, 'តើកូដខាងក្រោមបង្ហាញលទ្ធផលប៉ុន្មាន?\r\n\r\nint x = 5;\r\n\r\nx++;\r\n\r\nConsole.WriteLine(x);', ' a. 5', ' b. Error', ' c. 4', ' d. 6', 'D'),
(54, 27, 'Which SQL clause is used to group records that have identical values?', ' a. HAVING', 'b. JOIN', ' c. ORDER BY', ' d. GROUP BY', 'D'),
(55, 27, 'What does a \"View\" represent in a MySQL database?', ' a. A physical table stored on disk.', ' b. A virtual table based on the result-set of a query.', ' c. A stored procedure that performs database operations.', ' d. A trigger that automatically executes on certain events.', 'B'),
(56, 27, 'What does the SQL command SELECT * do?', ' a. Updates all records in a table', ' b. Deletes all records from a table', ' c. Retrieves all columns from a table', ' d. Creates a new table', 'C'),
(57, 27, 'What does the acronym SQL stand for?', ' a. Simple Query Language', ' b. Systematic Query Language', 'c. Standard Query Language', ' d. Structured Query Language', 'D'),
(58, 27, 'In SQL, which clause is used to filter records based on a condition?', ' a. GROUP BY', ' b. ORDER BY', ' c. WHERE', ' d. HAVING', 'C'),
(59, 27, 'Which SQL command is used to modify the structure of an existing table?', ' a. `UPDATE TABLE`', 'b. `ALTER TABLE`', ' c. `CHANGE TABLE`', 'd. `MODIFY TABLE`', 'B'),
(60, 27, 'Which SQL function is used to find the highest value in a column?', ' a. MIN()', ' b. AVG()', ' c. MAX()', ' d. SUM()', 'C'),
(61, 27, 'What is the purpose of a \"Stored Procedure\" in MySQL?', 'a. To automatically trigger actions based on events.', ' b. To perform calculations and return a single value.', ' c. To execute a set of SQL statements as a single unit.', ' d. To define a virtual table based on a query.', 'C'),
(62, 27, 'What is the purpose of the `JOIN` clause in SQL?', ' a. To combine rows from two or more tables based on a related column.', ' b. To sort the result set.', ' c. To group records with identical values.', ' d. To filter records based on a condition.', 'A'),
(63, 27, 'What is the purpose of an index in a database?', ' a. To speed up data retrieval', ' b. To delete duplicate records', ' c. To group related tables', ' d. To enforce foreign key constraints', 'A'),
(64, 28, 'How many colors can typically be included in an Indexed Color Mode?', ' a. Unlimited', ' b. 256', ' c. 16', ' d. 8', 'B'),
(65, 28, 'តួនាទីរបស់ Gradient Map ក្នុងកម្មវិធី Adobe Photsohop ?', ' a. សម្រាប់បន្ថែម ឬបន្ថយពន្លឺរូបភាព', 'b. កែលម្អរូបភាពពីពណ៌មិនត្រឹមត្រូវ ទៅពណ៌៌ដែរត្រឹមត្រូវ', ' c. ធ្វើឱ្យរូបភាពមានពណ៌ចម្រុះចូលគ្នា', 'all incorrect', 'C'),
(66, 28, 'តើក្នុងកម្មវិធី Adobe Photoshop 2024 មានប្រភេទពណ៌ចំនួនប៉ុន្មាន?', ' a. ចំនួន ៧', ' b. ចំនួន ៥', ' c. ចំនួន ៨', 'd. ចំនួន ៤', 'C'),
(67, 28, 'តើ Auto Tone មានតួរនាទីដូចម្ដេច?', ' a. ធ្វើការកែប្រែពន្លឺដោយស្វ័យប្រវត្តិនៃរូបភាព', ' b. ធ្វើអោយរូបមានពណ៌ដែលមិនត្រឹមត្រូវ ទៅជាពណ៌ដើមដែលមានលក្ខណស្រស់ស្អាត', ' c. សម្រាប់តែធ្វើការកែតម្រូវទៅលើពណ៌ និងពន្លឺរបស់រូបភាព', 'd.មិនមានចម្លើយត្រូវទេ​', 'B'),
(68, 28, 'តួនាទីរបស់ Canvas size ក្នុងកម្មវិធី Adobe Photoshop?', ' a. អ្នកអាចធ្វើការកែទំហំ និងកំណត់គុណភាពរូបភាព នៃឯកសារបន្ទាប់ពីបានបើកយកមកធ្វើ', ' b. ធ្វើអោយរូបមានពណ៌ដែលមិនត្រឹមត្រូវ ទៅជាពណ៌ដើមដែលមានលក្ខណស្រស់ស្អាត', ' c. ប្រើសម្រាប់កំណត់ទំហំអោយរូបភាពមួយតែវាមានលក្ខណខុសគ្នាពី Image size ដោយសារ តែការផ្លាស់ប្ដូរទំហំនេះគឺវាពង្រីកឬបង្រួមទៅលើទំហំ Background តែបណ្ណោះ', 'មិនមានចម្លើយត្រូវ', 'A'),
(69, 28, 'គេប្រើ Crop ដើម្បីធ្វើអ្វីក្នុងកម្មវិធី Adobe Photoshop?', ' a. កាត់ផ្នែកណាមួយនៃរូបភាព', ' b. ប្រើប្រាស់ដើម្បី Copy រូបភាព ឬ Layer ណាមួយ', ' c. ពង្រីក ឬបង្រួមរូបភាព', 'd. ប្រើសម្រាប់កែសម្រួលពន្លឺ', 'A'),
(70, 28, 'តួនាទីរបស់ Height ក្នុងកម្មវិធី Adobe Photoshop?', ' a. ទំហំទទឹងសន្លឹកកិច្ចការ', ' b. កំណត់ទំហំសន្លឹកកិច្ចការ', ' c. ទំហំបណ្ដោយសន្លឹកកិច្ចការ', 'nothing true', 'A'),
(71, 28, 'Rotation ក្នុងកម្មវិធី Adobe Photoshop ?', 'a. ធ្វើអោយរូបមានពណ៌ដែលមិនត្រឹមត្រូវ ទៅជាពណ៌ដើមដែលមានលក្ខណស្រស់ស្អាត', ' b. អ្នកអាចធ្វើការកែទំហំ និងកំណត់គុណភាពរូបភាព នៃឯកសារបន្ទាប់ពីបានបើកយកមកធ្វើការឌីហ្សាញ', ' c. អ្នកប្រើប្រាស់ធ្វើការបង្វិលរូបភាព ឬត្រឡប់រូបភាពបាន', 'យកមកបង្កើតចលនានៅក្នុង​ការរចនា', 'C'),
(72, 28, '\r\nc.\r\nអ្នកប្រើប្រាស់ធ្វើការបង្វិលរូបភាព ឬត្រឡប់រូបភាពបាន\r\n\r\n', ' a. Cyan, Magenta, Yellow, Black  Wrong !', 'b. Cyan, Maroon, Yellow, Key', ' c. Cyan, Magenta, Yellow, Key (Black)', 'មិនមានចម្លើយត្រឹមត្រូវ', 'C'),
(73, 28, 'តួនាទីរបស់ Width ក្នុងកម្មវិធី Adobe Photoshop?', ' a. កំណត់ទំហំសន្លឹកកិច្ចការផ្សេងៗ', 'b. កំណត់គុណភាពរូបភាព ឬ Artwork ផ្សេងៗ', ' c. កំណត់ទំហំទទឹងសន្លឹកកិច្ចការ', 'មិនមានចម្លើយត្រឹមត្រូវ', 'C'),
(74, 28, 'តួនាទីរបស់ Document Size ?', ' a. ទំហំគុណភាពនៃរូបភាព', 'b. កំណត់ទំហំសន្លឹកកិច្ចការផ្សេងៗ', ' c. ទំហំទទឹងសន្លឹកកិច្ចការ', 'កំណត់ពណ៍របស់សន្លឹកកិច្ចការ', 'B'),
(75, 28, 'យើងប្រើ Trim ដើម្បី ៖', 'a. កាត់ផ្នែកដែលនៅទំនេរចោល', ' b. អ្នកអាចធ្វើការកែទំហំ និងកំណត់គុណភាពរូបភាព', ' c. អនុញ្ញាតអោយអ្នកប្រើប្រាស់ធ្វើការបង្វិលរូបភាព ឬត្រឡប់រូបភាពទាំងមូល', 'មិនមានចម្លើយត្រឹមត្រូវ', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `attempts_limit` int(11) DEFAULT 1,
  `open_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `total_score` int(11) DEFAULT 100,
  `total_questions` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `teacher_id`, `title`, `description`, `duration`, `attempts_limit`, `open_date`, `due_date`, `total_score`, `total_questions`, `image_url`, `status`, `created_at`, `class_id`) VALUES
(22, 12, 'Using PHP with MySQL', 'please do by your self', 20, 3, '2026-03-22 15:00:00', '2026-04-02 16:00:00', 100, 0, '', 'active', '2026-03-22 08:01:05', 3),
(23, 17, 'Quiz 1 .NET', 'please do by your self', 30, 1, '2026-03-22 17:57:00', '2026-04-07 17:57:00', 100, 0, '', 'active', '2026-03-22 10:57:56', 3),
(27, 11, 'Ch2- Quiz 1 Database ', 'Please do it by your self', 20, 1, '2026-03-23 11:08:00', '2026-03-25 11:08:00', 100, 0, '', 'inactive', '2026-03-23 04:08:58', 1),
(28, 12, 'Graphic Disgn Quiz 1', 'Please do by your self ', 20, 3, '2026-03-27 12:37:00', '2026-04-02 12:37:00', 100, 0, '', 'active', '2026-03-27 05:37:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `status` enum('Passed','Failed') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `status`, `created_at`) VALUES
(21, 18, 23, 76, 17, 'Passed', '2026-03-22 11:13:18'),
(24, 28, 23, 94, 17, 'Passed', '2026-03-23 05:58:43'),
(27, 24, 28, 67, 12, 'Passed', '2026-03-27 06:18:25'),
(28, 23, 22, 100, 8, 'Passed', '2026-03-27 08:51:25'),
(29, 26, 23, 88, 17, 'Passed', '2026-03-28 07:34:20');

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_answers`
--

INSERT INTO `student_answers` (`id`, `result_id`, `question_id`, `selected_option`) VALUES
(16, 21, 34, 'C'),
(17, 21, 35, 'A'),
(18, 21, 36, 'C'),
(19, 21, 37, 'A'),
(20, 21, 38, 'B'),
(21, 21, 39, 'C'),
(22, 21, 40, 'D'),
(23, 21, 41, 'C'),
(24, 21, 42, 'B'),
(25, 21, 43, 'B'),
(26, 21, 44, 'B'),
(27, 21, 45, 'B'),
(28, 21, 46, 'A'),
(29, 21, 47, 'D'),
(30, 21, 48, 'B'),
(31, 21, 49, 'C'),
(32, 21, 50, 'D'),
(67, 24, 34, 'C'),
(68, 24, 35, 'A'),
(69, 24, 36, 'C'),
(70, 24, 37, 'A'),
(71, 24, 38, 'B'),
(72, 24, 39, 'C'),
(73, 24, 40, 'D'),
(74, 24, 41, 'C'),
(75, 24, 42, 'D'),
(76, 24, 43, 'B'),
(77, 24, 44, 'B'),
(78, 24, 45, 'C'),
(79, 24, 46, 'D'),
(80, 24, 47, 'D'),
(81, 24, 48, 'B'),
(82, 24, 49, 'C'),
(83, 24, 50, 'D'),
(100, 27, 64, 'B'),
(101, 27, 65, 'C'),
(102, 27, 66, 'C'),
(103, 27, 67, 'C'),
(104, 27, 68, 'C'),
(105, 27, 69, 'A'),
(106, 27, 70, 'C'),
(107, 27, 71, 'C'),
(108, 27, 72, 'D'),
(109, 27, 73, 'C'),
(110, 27, 74, 'B'),
(111, 27, 75, 'A'),
(112, 28, 25, 'D'),
(113, 28, 26, 'C'),
(114, 28, 27, 'B'),
(115, 28, 28, 'A'),
(116, 28, 29, 'D'),
(117, 28, 30, 'C'),
(118, 28, 31, 'A'),
(119, 28, 32, 'A'),
(120, 29, 34, 'C'),
(121, 29, 35, 'A'),
(122, 29, 36, 'C'),
(123, 29, 37, 'A'),
(124, 29, 38, 'B'),
(125, 29, 39, 'C'),
(126, 29, 40, 'D'),
(127, 29, 41, 'C'),
(128, 29, 42, 'D'),
(129, 29, 43, 'B'),
(130, 29, 44, 'B'),
(131, 29, 45, 'B'),
(132, 29, 46, 'D'),
(133, 29, 47, 'D'),
(134, 29, 48, 'B'),
(135, 29, 49, 'C'),
(136, 29, 50, 'D');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `gender` enum('M','F','Other') DEFAULT 'M',
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `full_name`, `gender`, `dob`, `address`, `profile_image`, `created_at`, `class_id`) VALUES
(1, 'cheal vichet', 'vichet.ssp2023@gmail.com', '123', 'admin', '', 'M', '2026-03-03', 'SIEM REAP ', 'profile_1_1772770214.JPG', '2026-03-04 07:33:28', NULL),
(7, 'MAKARA DARA', 'dara@gmail.com', '$2y$10$4l.E7tfVZzYF0PiuNXj9KOigmDz0t.1eLjMGpHcJyKw8WNTyYg.zW', 'teacher', 'MAKARA DARA', 'M', '0000-00-00', 'BATAMBONG', NULL, '2026-03-05 02:02:01', NULL),
(9, 'MANA SIN', 'mana@gmail.com', '123', 'student', 'MANA SIN', 'M', NULL, '', 'profile_9_1773406197.JPG', '2026-03-05 02:11:41', 5),
(10, 'VANN SAVIN', 'savin@gmail.com', '2233', 'teacher', 'VANN SAVIN', 'M', '2006-05-03', 'SIEM REAP ', 'profile_10_1772782339.jpg', '2026-03-05 02:50:06', NULL),
(11, 'SAKIN BUNHON', 'bunhon.ssp2023@gmail.com', '3333', 'teacher', 'SAKIN BUNHON', 'M', '2004-05-04', 'SIEM REAP', 'profile_11_1772782533.jpg', '2026-03-05 03:10:08', NULL),
(12, 'KEO DANIN', 'danin.nssp2023@gmail.com', '123', 'teacher', 'KEO DANIN', 'F', '2006-05-08', 'PHNOM PENH', 'profile_12_1772780216.JPG', '2026-03-05 03:21:07', NULL),
(13, 'SOTHEARITH', 'rith.nssp2023@gmail.com', '123', 'student', 'SOTHEARITH', 'M', '2026-03-12', 'SIEM REAP ', 'profile_13_1773238937.JPG', '2026-03-05 03:32:31', 2),
(17, 'SORN SOPHAL', 'sophal@gmail.com', '123', 'teacher', 'SORN SOPHAL', 'M', '2005-02-01', 'PRESH VIHEAR', 'profile_17_1773461010.JPG', '2026-03-14 03:59:40', NULL),
(18, 'NY RATHANA', 'rathana@gmail.com', '123', 'student', 'NY RATHANA', 'M', '2006-02-22', 'Phnom Penh', 'profile_18_1773467923.JPG', '2026-03-14 05:55:24', 3),
(23, 'Sok Somnang ', 'somnang@gmail.com', '123', 'student', '', 'M', '2026-03-18', 'PRESH VIHEAR', 'profile_23_1774600971.jpg', '2026-03-15 17:33:05', 3),
(24, 'TEP KANHA ', 'kanha@gmail.com', '123', 'student', 'TEP KANHA ', 'F', '2000-06-20', 'PHNOM PENH', NULL, '2026-03-15 18:04:34', 1),
(26, 'CHI LI ', 'li@gmail.com', '123', 'student', '', 'F', '2026-03-20', 'SIEM REAPP', '', '2026-03-16 09:49:04', 3),
(27, 'PISETH DARA', 'ra@gmail.com', '123', 'student', 'PISETH DARA', 'M', NULL, '', NULL, '2026-03-16 09:56:52', 3),
(28, 'KOEUN KDEB', 'kdeb@gmail.com', '123', 'student', 'KOEUN KDEB', 'M', NULL, '', NULL, '2026-03-17 05:11:26', 3),
(29, 'BORA', 'bora@gmail.com', '123', 'student', 'BORA', 'M', NULL, '', NULL, '2026-03-22 07:54:17', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quiz_class` (`class_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_class` (`class_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `results` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `manage_quiz_status_event` ON SCHEDULE EVERY 1 SECOND STARTS '2026-03-27 15:37:43' ON COMPLETION PRESERVE ENABLE DO BEGIN
    -- ១. ប្តូរទៅ 'inactive' ប្រសិនបើហួសថ្ងៃកំណត់
    UPDATE quizzes 
    SET status = 'inactive' 
    WHERE due_date < NOW() AND status != 'inactive';

    -- ២. ប្តូរទៅ 'active' ប្រសិនបើដល់ម៉ោងត្រូវប្រឡង
    UPDATE quizzes 
    SET status = 'active' 
    WHERE open_date <= NOW() AND due_date >= NOW() AND status != 'active';
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
