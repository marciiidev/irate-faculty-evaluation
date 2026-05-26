Faculty Evaluation System - PHP Version
======================================

How to run on Localhost (XAMPP):

1. Open XAMPP Control Panel and start Apache and MySQL.
2. Go to your XAMPP installation folder (usually C:\xampp\htdocs).
3. Create a folder named 'faculty-evaluation-system' inside htdocs.
4. Copy all the PHP files and folders from this project into that folder.
5. Open your browser and go to http://localhost/phpmyadmin.
6. Create a new database named 'evaluation_db'.
7. Click on the 'Import' tab and select the file 'evaluation_db/evaluation_db.sql' from the project folder.
8. Click 'Go' to import the database schema and initial data.
9. Now you can access the system at http://localhost/faculty-evaluation-system/index.php.

Default Login Credentials:
-------------------------
SuperAdmin:
Email/ID: SuperAdmin01
Password: password

Admin:
Email/ID: Admin01
Password: password

Faculty:
Email/ID: Faculty01
Password: password

Student:
Email/ID: Student01
Password: password

Folder Structure:
----------------
/superadmin/      - Pages for SuperAdmin role
/admin/           - Pages for Admin role
/faculty/         - Pages for Faculty role
/student/         - Pages for Student role
/evaluation_db/   - Database connection and SQL schema
index.php         - Login page
logout.php        - Logout script
