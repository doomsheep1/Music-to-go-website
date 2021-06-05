<?php
  $conn = @new mysqli("localhost", "root", "mysql", "musicToGo");
  if ($conn -> connect_errno > 0)
    echo "<p>Connection to database musicToGo failed: " . $conn -> errno . "-" . $conn -> error . "</p><br />";
?>
