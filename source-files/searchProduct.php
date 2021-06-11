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
        <form action='searchProduct.php' method='GET'>
          <b>Category</b>: <select name="category">
                                          <option value="string">String</option>
                                          <option value="percussion">Percussion</option>
                                          <option value="keyboard">Keyboard</option>
                                          <option value="others">Others</option>
                                          <option value="none">None</option>
                                        </select>

          <b>Brand</b>: <input type="text" name="brand"/>
          <b>Characteristics</b>: <input type="text" name="characteristics"/>
          <b>Status</b>: <select name="status">
                                          <option value="available">Available</option>
                                          <option value="not available">Not available</option>
                                          <option value="none">None</option>
                                        </select>

          <input type="submit" name="searchsubmit" value="Search"/>
        </form>
        <p>Click on product id to view searched product</p>
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
            if(isset($_GET["searchsubmit"]))
            {
              $searchTypes = array("category", "brand", "characteristics", "status");
              $adminObj -> search($searchTypes);
            }
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
        <form action='searchProduct.php' method='GET'>
          <b>Category</b>: <select name="category">
                                          <option value="string">String</option>
                                          <option value="percussion">Percussion</option>
                                          <option value="keyboard">Keyboard</option>
                                          <option value="others">Others</option>
                                          <option value="none">None</option>
                                        </select>

          <b>Brand</b>: <input type="text" name="brand"/>
          <b>Characteristics</b>: <input type="text" name="characteristics"/>
          <b>Status</b>: <select name="status">
                                          <option value="available">Available</option>
                                          <option value="not available">Not available</option>
                                          <option value="none">None</option>
                                        </select>

          <input type="submit" name="searchsubmit" value="Search"/>
        </form>
        <p>Click on product id to view searched product</p>
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
          if(isset($_GET["searchsubmit"]))
          {
            $searchTypes = array("category", "brand", "characteristics", "status");
            $clientObj -> search($searchTypes);
          }
        ?>
        </table>
    <?php
      }
      else
        echo "Session not available, please login again.";

      include ("include-headerfooter.html");
    ?>
    <button type="button"><a href="index.php">Logout</a></button>
  </body>
</html>
