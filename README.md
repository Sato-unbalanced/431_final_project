# 431_final_project

login flow: login_form.php -> handle_login.php -> member.php (eventually our main page later) (2 files + our roster/team pages)

registering a user flow: register_user.php -> process_signup.php -> signup_success.php (3 files)

resetting password flow: reset_password.php -> change_password.php -> process_password_change.php -> password_change_success.php (4 files)


Setting up DB:
-ddl file is basically a list of sql commands
  command: sudo /opt/lampp/bin/mysql to enter sql   
            -you can then enter each command to build the database table by table 
            -OR you can pipe in the file via: sudo /opt/lampp/bin/mysql < /opt/lampp/htdocs/directoryTO/soccer_db.ddl

You replicate these files:
  - Set up mail() function to be able to send emails
  - your local db will be empty
  - you can create a user
  - whatever email you used to create a user will be the email account that will receive a temp password to reset a password
  - localhost/phpMyAdmin is usefull for seeing the entire DB
          

