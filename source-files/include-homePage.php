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

      table {
        text-align:center;
      }

      .error {
        color: red;
      }
    </style>
  </head>
  <body>
    <?php
      if(isset($_SESSION['admin']))
      {
        $adminObj = unserialize($_SESSION['admin']);
    ?>
        <div>
          <h2>Welcome <?php echo $adminObj -> getName() . " " . $adminObj -> getSurname(); ?>! Your admin Id is <?php echo $adminObj -> getId(); ?></h2>
          <?php include ("include-links.php"); ?>
        </div>
        <form action="include-homePage.php" method="GET">
          <label>Choose listing type:</label>
          <select name='list'>
            <option value='all'>List all</option>
            <option value='available'>List available</option>
            <option value='overdue'>List overdue</option>
            <option value='rented'>List rented</option>
          </select>
          <input type="submit" name='listsubmit' value='Go'/>
        </form>
        <p>Click on product id to view more details on any product</p>
        <table border="1" width="75%">
          <tr>
            <th>Product Id</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Year of Manufacture</th>
            <th>Characteristics</th>
            <th>Status</th>
            <th>Cost/day - regular</th>
            <th>Cost/day - overdue</th>
          </tr>
          <?php
            if(isset($_GET['listsubmit']))
            {
              switch($_GET['list'])
              {
                case "available":
                  $adminObj -> displayAvailable();
                  break;
                case "overdue":
                  $adminObj -> displayOverdueProducts();
                  break;
                case "rented":
                  $adminObj -> displayRentedProducts();
                  break;
                case "all":
                  $adminObj -> displayAllProducts();
                  break;
              }
            }
            else
              $adminObj -> displayAllProducts();
          ?>
        </table>
    <?php
      }
      else if(isset($_SESSION['client']))
      {
        $clientObj = unserialize($_SESSION['client']);
    ?>
        <div>
          <h2>Welcome <?php echo $clientObj -> getName() . " " . $clientObj -> getSurname(); ?>! Your client Id is <?php echo $clientObj -> getId(); ?></h2>
          <?php include ("include-links.php"); ?>
        </div>
        <form action="include-homePage.php" method="GET">
          <label>Choose listing type:</label>
          <select name='list'>
            <option value='available'>List available</option>
            <option value='current'>List currently renting</option>
            <option value='past'>List rented before</option>
          </select>
          <input type="submit" name='listsubmit' value='Go'/>
        </form>
        <p>Click on product id to rent any product</p>
        <table border="1" width="75%">
          <tr>
            <th>Product Id</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Year of Manufacture</th>
            <th>Characteristics</th>
            <th>Status</th>
            <th>Cost/day - regular</th>
            <th>Cost/day - overdue</th>
          </tr>
          <?php
            if(isset($_GET['listsubmit']))
            {
              switch($_GET['list'])
              {
                case "available":
                  $clientObj -> displayAvailable();
                  break;
                case "current":
                  $clientObj -> displayCCRP();
                  break;
                case "past":
                  $clientObj -> displayCRP();
                  break;
              }
            }
            else
              $clientObj -> displayAvailable();
          ?>
        </table>
    <?php
      }
      else
        echo "Session not available, please login again.";
    ?>
      <?php include ("include-headerfooter.html"); ?>
      <button type="button"><a href="index.php">Logout</a></button>
  </body>
</html>
