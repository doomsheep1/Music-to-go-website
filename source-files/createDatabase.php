<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Create DATABASE</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  </head>
  <body>
    <?php
      $serverName = "localhost";
      $userName = "root";
      $password = "mysql";
      $dbName = "musicToGo";
      // connect to phpmyadmin account
      $conn = @new mysqli($serverName, $userName, $password);
      if($conn -> connect_errno != 0)
        echo "Connection to phpmyadmin server failed: " . $conn -> connect_errno . "-" . $conn -> connect_error;
      else
      {
        // create database music-to-go
        $sql = "CREATE DATABASE IF NOT EXISTS $dbName";
        $qResult = @$conn -> query($sql);
        if(!$qResult)
          echo "Database creation of musicToGo failed: " . $conn -> errno . "-" . $conn -> error;
        else
          echo "Database musicToGo has been created successfully";
      }
      $conn -> close();
    ?>
  </body>
</html>
