<?php
  session_start();
  require_once("include-classMusicToGo.php");
  date_default_timezone_set('Asia/Singapore');

  // functions
  function retrieveAdmin($email)
  {
    include ("include-dbConnect.php");
    $sql = "SELECT name, surname, passwordmd5, phone, email FROM admin WHERE email='$email'";
    $res = @$conn -> query($sql);
    $adminLogin = NULL;
    if($res -> num_rows > 0)
    {
      $row = $res -> fetch_assoc();
      $adminName = $row['name'];
      $adminSurname = $row['surname'];
      $adminPassword = $row['passwordmd5'];
      $adminPhone = $row['phone'];
      $adminEmail = $row['email'];
      $adminLogin = new admin ($adminName, $adminSurname, $adminPassword, $adminPhone, $adminEmail);
    }
    else
      echo "There are no users with the registered email, use the BACK button to retry.";
    $res -> free_result();
    $conn -> close();
    return $adminLogin;
  }

  function retrieveClient($email)
  {
    include ("include-dbConnect.php");
    $sql = "SELECT * FROM client WHERE email='$email'";
    $res = @$conn -> query($sql);
    $clientLogin = NULL;
    if($res -> num_rows > 0)
    {
      $row = $res -> fetch_assoc();
      $clientName = $row['name'];
      $clientSurname = $row['surname'];
      $clientPassword = $row['passwordmd5'];
      $clientPhone = $row['phone'];
      $clientEmail = $row['email'];
      $clientLogin = new client ($clientName, $clientSurname, $clientPassword, $clientPhone, $clientEmail);
    }
    else
      echo "There are no users with the registered email, use the BACK button to retry.";
    $res -> free_result();
    $conn -> close();
    return $clientLogin;
  }

  // variables

  if(isset($_POST["login"]))
  {
    $inputEmail = trim(stripslashes($_POST["email"]));
    $inputPass = md5(trim(stripslashes($_POST["password"])));
    $inputUserType = $_POST["usertype"];
    if($inputUserType == "admin")
    {
      $adminObj = retrieveAdmin($inputEmail);
      if($adminObj)
      {
        if($adminObj -> verifyAdminLogin($inputEmail, $inputPass))
        {
          $_SESSION['admin'] = serialize($adminObj);
          include ("include-homePage.php");
        }
        else
          echo "Admin login failed, check email or password, use the BACK button to retry.";
      }
    }
    else if($inputUserType == "client")
    {
      $clientObj = retrieveClient($inputEmail);
      if($clientObj)
      {
        if($clientObj -> verifyClientLogin($inputEmail, $inputPass))
        {
          $_SESSION['client'] = serialize($clientObj);
          include ("include-homePage.php");
        }
        else
          echo "Client login failed, check email or password, use the BACK button to retry";
      }
    }
  }
?>
