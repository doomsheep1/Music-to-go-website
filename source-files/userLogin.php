<?php
  session_start();
  $_SESSION = array();
  session_destroy();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Music To Go Login/Register</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <style>
      .headerfooter {
        background-color:lightblue;
        text-align:center;
      }
    </style>
  </head>
  <body>
    <?php include ("include-headerfooter.html"); ?>
    <h2>Hello! Welcome to Music To Go!</h2>
    <p>If you do not have an account, please register.</p>
    <p>If you already have an account, please login.</p>
    <hr />
    <h3>New account registration</h3>
    <form action="registerUser.php" method="POST">
      <p>Please enter your full name: Name: <input type="text" name="name"/>
         Surname: <input type="text" name="surname"/>
      </p>
      <p>Please enter your phone number: <input type="text" name="phone" size="8"/></p>
      <p>Please enter your email: <input type="text" name="email"/></p>
      <p>Please choose what you are registering as:
        <select name="usertype">
          <option value="admin">Admin</option>
          <option value="client">Client</option>
        </select>
      </p>
      <p>Please enter a password of your choice: <input type="password" name="password"/></p>
      <p>Please confirm your password: <input type="password" name="cfmpassword"/></p>
      <p>(<i>Only Singaporean phone numbers are allowed</i>)</p>
      <p>(<i>Passwords are case sensitive and must be at least 8 characters long</i>)</p>
      <input type="reset" name="reset" value="Reset fields"/>
      <input type="submit" name="register" value="Register"/>
    </form>
    <hr />
    <h3>Login</h3>
    <form action="loginUser.php" method="POST">
      <p>Enter email: <input type="text" name="email"/></p>
      <p>Enter password: <input type="password" name="password"/></p>
      <p>Login as:
        <select name="usertype">
          <option value="admin">Admin</option>
          <option value="client">Client</option>
        </select>
      </p>
      <input type="reset" name="reset" value="Reset fields"/>
      <input type="submit" name="login" value="Login"/>
    </form>
    <?php include ("include-headerfooter.html"); ?>
  </body>
</html>
