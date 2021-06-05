<?php
  session_start();
  require_once("include-classMusicToGo.php");
  date_default_timezone_set('Asia/Singapore');
  // functions
  function validateRegistration($name, $surname, $phone, $email, $password, $cfmPassword)
  {
    $errorCount = 0;
    global $errorMsg;
    if(empty($name) || empty($surname) || empty($phone) || empty($email) || empty($password) || empty($cfmPassword))
    {
      $errorCount++;
      $errorMsg .= "<span class='error'>Some fields are empty, please check.</span><br />";
    }
    // check name only has letters, white space allowed
    if(!preg_match("/^[A-Za-z ]*$/", $name))
    {
      $errorCount++;
      $errorMsg .= "<span class='error'>Name field can only have letters.</span><br />";
    }
    // check surname only has letters, no space allowed
    if(!preg_match("/^[A-Za-z]*$/", $name))
    {
      $errorCount++;
      $errorMsg .= "<span class='error'>Name field can only have letters.</span><br />";
    }
    // check phone, singaporean numbers only
    if(!preg_match("/^(8|9)[\d]{7}$/", $phone))
    {
      $errorCount++;
      $errorMsg .= "<span class='error'>Not a valid phone number, only Singaporean numbers allowed.</span><br />";
    }
    // check email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
      $errorCount++;
      $errorMsg .= "<span class='error'>Invalid email format</span><br />";
    }
    // check password
    if(!empty($password) && !empty($cfmPassword))
    {
      if(strlen($password) < 8)
      {
        $errorCount++;
        $errorMsg .= "<span class='error'>Password fields must have at least 8 characters</span><br />";
      }
      if($password <> $cfmPassword)
      {
        $errorCount++;
        $errorMsg .= "<span class='error'>Password field and confirm password field do not match</span><br />";
      }
      if(preg_match("/\s/", $password))
      {
        $errorCount++;
        $errorMsg .= "<span class='error'>Password fields cannot have white space characters</span><br />";
      }
    }

    if($errorCount > 0)
      return false;

    return true;
  }

  // variables
  $errorMsg = "";

  if(isset($_POST["register"]))
  {
    $name = trim(stripslashes($_POST["name"]));
    $surname = trim(stripslashes($_POST["surname"]));
    $phone = trim(stripslashes($_POST["phone"]));
    $email = trim(stripslashes($_POST["email"]));
    $userType = $_POST["usertype"];
    $password = trim(stripslashes($_POST["password"]));
    $cfmPassword = trim(stripslashes($_POST["cfmpassword"]));
    if(validateRegistration($name, $surname, $phone, $email, $password, $cfmPassword))
    {
      if($userType == "admin")
      {
        $newAdmin = new admin($name, $surname, $password, $phone, $email);
        $insertionError = $newAdmin -> checkDuplicates();
        if($insertionError != "")
        {
          $errorMsg .= $insertionError;
          include ("include-stickyRegister.php");
        }
        else
        {
          $newAdmin -> registerAdmin();
          $_SESSION['adminid'] = $newAdmin -> getId();
        }

      }
      else if ($userType == "client")
      {
        $newClient = new client($name, $surname, $password, $phone, $email);
        $insertionError = $newClient -> checkDuplicates();
        if($insertionError != "")
        {
          $errorMsg .= $insertionError;
          include ("include-stickyRegister.php");
        }
        else
        {
          $newClient -> registerClient();
          $_SESSION['clientid'] = $newClient -> getId();
        }
      }
    }
    else
      include ("include-stickyRegister.php");
  }
?>
