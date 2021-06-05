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
    <title>Admin insert</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <style>
      .headerfooter, h2 {
        background-color:lightblue;
        text-align:center;
      }

      span {
        color:red;
      }
    </style>
  </head>
  <body>
    <?php
      // functions
      function validateInsertForm($brand, $yom, $characteristics, $costReg, $costOver)
      {
        global $errorMsg;
        $error = 0;
        $currentYear = date('Y');
        if(empty($brand) || empty($yom) || empty($characteristics) || empty($costReg) || empty($costOver))
        {
          $error++;
          $errorMsg .= "<span>Some fields are empty, please check</span><br />";
        }

        if(!preg_match("/^\d{4}/", $yom) || $yom > $currentYear)
        {
          $error++;
          $errorMsg .= "<span>Invalid year entered</span><br />";
        }

        if(preg_match("/[^A-Za-z0-9,\.' ]/", $characteristics))
        {
          $error++;
          $errorMsg .= "<span>No special characters are allowed for characteristics</span><br />";
        }

        if(strlen($characteristics) > 60)
        {
          $error++;
          $errorMsg .= "<span>Your characteristics are too long, please shorten it.<br />";
        }

        if(!is_numeric($costReg))
        {
          $error++;
          $errorMsg .= "<span>Invalid regular rental cost entered</span><br />";
        }

        if(!is_numeric($costOver))
        {
          $error++;
          $errorMsg .= "<span>Invalid overdue rental cost entered</span><br />";
        }

        if($error > 0)
          return false;
        return true;
      }

      function displayForm()
      {
    ?>
          <form action="" method="GET" align="center">
            <p>Choose category:
               <select name="category">
                 <option value="string">String</option>
                 <option value="percussion">Percussion</option>
                 <option value="keyboard">Keyboard</option>
                 <option value="others">Others</option>
               </select>
            </p>
            <p>Brand: <input type="text" name="brand"/></p>
            <p>Year of manufacture: <input type="text" name="year" size="4"/></p> <!-- validate -->
            <p>Characteristics: <textarea name="characteristics" rows="4" cols="20"></textarea></p>
            <p>Cost/day - regular: <input type="text" name="regcost"/></p> <!-- validate -->
            <p>Cost/day - overdue: <input type="text" name="overcost"/></p> <!-- validate -->
            <input type="reset" name="reset" value="Reset form"/>
            <input type="submit" name="insertProduct" value ="Insert"/>
          </form>
    <?php
      }

      // variables
      $errorMsg = "<div style='text-align:center;'>";

      if(isset($_SESSION['admin']))
      {
        $adminObj = unserialize($_SESSION['admin']);
    ?>
        <h2>Welcome <?php echo $adminObj -> getName() . " " . $adminObj -> getSurname(); ?>! Your admin Id is <?php echo $adminObj -> getId(); ?></h2>
    <?php
        include ("include-links.php");
        displayForm();
        if(isset($_GET["insertProduct"]))
        {
          $category = $_GET["category"];
          $brand = trim(stripslashes($_GET["brand"]));
          $yom = trim(stripslashes($_GET["year"]));
          $characteristics = trim(stripslashes($_GET["characteristics"]));
          $costReg = trim(stripslashes($_GET['regcost']));
          $costOver = trim(stripslashes($_GET["overcost"]));
          if(validateInsertForm($brand, $yom, $characteristics, $costReg, $costOver))
          {
            $product = new product($category, $brand, $yom, $characteristics, $costReg, $costOver);
            $adminObj -> insertProduct($product);
          }
          else
            $errorMsg .= "<span>Please redo the form and fix the errors</span></div>";
            echo $errorMsg;
        }
      }
      else
        echo "Session not available, please login again.";

      include ("include-headerfooter.html");
    ?>
    <button type="button"><a href="userLogin.php">Logout</a></button>
  </body>
</html>
