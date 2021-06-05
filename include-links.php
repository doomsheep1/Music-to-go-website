<?php
  if(isset($_SESSION['admin']))
    echo "<pre><h2><a href='include-homePage.php'>List products</a>        <a href='insertProduct.php'>Insert product</a>        <a href='searchProduct.php'>Search products</a></h2></pre>";
  else if (isset($_SESSION['client']))
    echo "<pre><h2><a href='include-homePage.php'>List products</a>        <a href='searchProduct.php'>Search Products</a></h2></pre>";
?>
