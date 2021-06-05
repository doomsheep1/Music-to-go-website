<?php
  if(!isset($_SESSION))
  {
    session_start();
    require_once("include-classMusicToGo.php");
    date_default_timezone_set('Asia/Singapore');
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Product</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <style>
      .headerfooter, h2 {
        background-color:lightblue;
        text-align:center;
      }
    </style>
  </head>
  <body>
    <?php
      if(isset($_SESSION['admin']))
      {
        $adminObj = unserialize($_SESSION['admin']);
    ?>
        <h2>Welcome <?php echo $adminObj -> getName() . " " . $adminObj -> getSurname(); ?>! Your admin Id is <?php echo $adminObj -> getId(); ?></h2>
        <?php include ("include-links.php"); ?>
    <?php
        if(isset($_GET['productid']))
        {
          echo "<div align='center'><p>Please only use the save button if you wish to change the status or record payment of product.</p>";
          $productid = trim(stripslashes($_GET['productid']));
          $adminObj -> displayIndiProduct($productid);
        }
        else
          echo "<p>Product does not exist anymore.</p>";
        echo "</div>";
      }
      else if(isset($_SESSION['client']))
      {
        $clientObj = unserialize($_SESSION['client']);
    ?>
        <h2>Welcome <?php echo $clientObj -> getName() . " " . $clientObj -> getSurname(); ?>! Your client Id is <?php echo $clientObj -> getId(); ?></h2>
        <?php include ("include-links.php"); ?>
   <?php
        if(isset($_GET['productid']))
        {
          echo "<div align='center'>";
          $productid = trim(stripslashes($_GET['productid']));
          $clientObj -> displayIndiProduct($productid);
        }
        else
          echo "<p>Product does not exist anymore</div></p>";
        echo "</div>";
      }
      else
        echo "Session not available, please login again.";
    ?>
    <?php include ("include-headerfooter.html"); ?>
    <button type="button"><a href="userLogin.php">Logout</a></button>
  </body>
</html>
