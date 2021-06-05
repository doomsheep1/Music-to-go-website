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
    <title>Home</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <style>
      .headerfooter, h2 {
        background-color:lightblue;
        text-align:center;
      }

      .hyperlinks {
        background-color:yellow;
        font-size:30px;
        text-align:center;
      }
    </style>
  </head>
  <body>
    <?php
      if(isset($_SESSION['client']))
      {
        $clientObj = unserialize($_SESSION['client']);
      ?>
        <h2>Welcome <?php echo $clientObj -> getName() . " " . $clientObj -> getSurname(); ?>! Your client Id is <?php echo $clientObj -> getId(); ?></h2>
        <?php include ("include-links.php");
        if(isset($_GET['productid']))
        {
          echo "<div align='center'>";
          $productid = trim(stripslashes($_GET['productid']));
          if(isset($_GET['rentproduct']))
          {
            $rentDuration = $_GET['rentduration'];
            $clientObj -> rentProduct($productid, $rentDuration);
          }
          else if(isset($_GET['returnproduct']))
            $clientObj -> returnProduct($productid);
        }
        else
          echo "<p>Product does not exist anymore</div></p>";
        echo "</div>";
      }
      else if (isset($_SESSION['admin']))
      {
        $adminObj = unserialize($_SESSION['admin']);
      ?>
        <h2>Welcome <?php echo $adminObj -> getName() . " " . $adminObj -> getSurname(); ?>! Your client Id is <?php echo $adminObj -> getId(); ?></h2>
        <?php include ("include-links.php");
        if(isset($_GET['productid']))
        {
          echo "<div align='center'>";
          $productid = trim(stripslashes($_GET['productid']));
          if(isset($_GET['saveproduct']))
          {
            $status = $_GET['status'];
            $payment = $_GET['payment'];
            if($payment == "not rented")
              $payment = NULL;
            //if($status =)
            $adminObj -> saveProduct($productid, $status, $payment);
          }
        }
        else
          echo "<p>Product does not exist anymore</div></p>";
        echo "</div>";
      }
    ?>
    <?php
      include ("include-headerfooter.html");
    ?>
    <button type="button"><a href="userLogin.php">Logout</a></button>
  </body>
</html>
