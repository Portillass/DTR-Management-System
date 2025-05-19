📌 DTR Management System (Lechon Organization)
This is a web-based Daily Time Record (DTR) Management System developed using PHP, MySQL, Bootstrap, CSS, and JavaScript. The system is designed specifically for lechon organizations to efficiently manage staff attendance and automate record-keeping. It features two user roles: Admin and Staff.

✅ Features
👨‍💼 Admin
Dashboard overview

Manage staff accounts

View, edit, and delete attendance records

Generate daily/weekly/monthly attendance reports

Manage time-in/time-out rules

👷 Staff
Log in/out (Time-in / Time-out)

View personal attendance history

Update profile information

🛠️ Technologies Used
PHP - Backend scripting

MySQL - Database

Bootstrap 5 - UI styling

CSS3 & JavaScript - Client-side behavior

HTML5 - Structure

🗃️ Database Schema (MySQL)
Tables:

users (id, name, email, password, role)

attendance (id, user_id, date, time_in, time_out, status)

🚀 Installation
Clone the project

bash
Copy
Edit
git clone https://github.com/yourusername/lechon-dtr-system.git
Import the SQL file

Open phpMyAdmin

Create a database dtr_system

Import the provided dtr_system.sql file

Configure Database

In /config/db.php, update:

php
Copy
Edit
$host = 'localhost';
$dbname = 'dtr_system';
$username = 'root';
$password = '';
Run the system

Open your browser and go to:

perl
Copy
Edit
http://localhost/lechon-dtr-system/
🔑 Default Credentials
Role	Email	Password
Admin	admin@lechon.com	admin123
Staff	staff@lechon.com	staff123

## 📸 Preview

![Form Preview](image.png)

---