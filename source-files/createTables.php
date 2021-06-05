<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Create TABLES</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  </head>
  <body>
    <?php
      $serverName = "localhost";
      $username = "root";
      $password = "mysql";
      $dbName = "musicToGo";
      // connect to database
      $conn = @new mysqli($serverName, $username, $password, $dbName);
      if($conn -> connect_errno != 0)
        echo "Connection to database $dbName failed: " . $conn -> connect_errno . "-" . $conn -> connect_error;
      else
      {
        // create admin table
        $adminTableSql = "CREATE TABLE IF NOT EXISTS admin (
          adminid INT UNSIGNED NOT NULL AUTO_INCREMENT,
          passwordmd5 VARCHAR(32) NOT NULL,
          name VARCHAR(30) NOT NULL,
          surname VARCHAR(30) NOT NULL,
          phone CHAR(8) NOT NULL,
          email VARCHAR(40) NOT NULL,
          CONSTRAINT admin_pkey PRIMARY KEY(adminid),
          CONSTRAINT admin_ckey1 UNIQUE(email),
          CONSTRAINT admin_ckey2 UNIQUE(phone)
        )";
        $qResult_admin = @$conn -> query($adminTableSql);
        if (!$qResult_admin)
          echo "<p>Creation of admin table failed: " . $conn -> errno . "-" . $conn -> error . "</p><br />";
        else
          echo "<p>Admin table created successfully<p><br />";
        /*
          create client table
          payment = paid/not paid or null, null means client has not rented anything
          if there are any unreturned products = not paid, all products returned = paid
          e.g. client rented 2 products, 1 returned, 1 not returned = not paid, both returned = paid
          rented = not paid, overdue = not paid, returned = paid
        */
        $clientTableSql = "CREATE TABLE IF NOT EXISTS client (
          clientid INT UNSIGNED NOT NULL AUTO_INCREMENT,
          passwordmd5 VARCHAR(32) NOT NULL,
          name VARCHAR(30) NOT NULL,
          surname VARCHAR(30) NOT NULL,
          phone CHAR(8) NOT NULL,
          email VARCHAR(40) NOT NULL,
          payment VARCHAR(8),
          CONSTRAINT client_pkey PRIMARY KEY(clientid),
          CONSTRAINT client_ckey1 UNIQUE(email),
          CONSTRAINT admin_ckey2 UNIQUE(phone)
        )";
        $qResult_client = @$conn -> query($clientTableSql);
        if (!$qResult_client)
          echo "<p>Creation of client table failed: " . $conn -> errno . "-" . $conn -> error . "</p><br />";
        else
          echo "<p>Client table created successfully</p><br />";
      /*
         create product table
         rentstat = rented/overdue, empty means nobody rent yet
         rentdate = date that should be returned, empty means nobody rent yet
         if nobody rent yet, status = available else not available
      */
      $productTableSql = "CREATE TABLE IF NOT EXISTS product (
        productid INT UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(30) NOT NULL,
        brand VARCHAR(30) NOT NULL,
        yom CHAR(4) NOT NULL,
        characteristics VARCHAR(60) NOT NULL,
        status VARCHAR(13) NOT NULL,
        costreg DECIMAL(19,2) NOT NULL,
        costover DECIMAL(19,2) NOT NULL,
        rentstat VARCHAR(7),
        rentdate DATE,
        daterented DATE,
        CONSTRAINT prod_pkey PRIMARY KEY(productid)
      )";
      $qResult_product = @$conn -> query($productTableSql);
      if (!$qResult_product)
        echo "<p>Creation of product table failed: ". $conn -> errno . "-" . $conn -> error . "</p><br />";
      else
        echo "<p>Product table created successfully</p><br />";
      // create client currently rented product table
      $ccrpTableSql = "CREATE TABLE IF NOT EXISTS clientCurrentProduct (
        clientid INT UNSIGNED NOT NULL,
        productid INT UNSIGNED NOT NULL,
        CONSTRAINT clientcurrprod_pkey PRIMARY KEY(productid),
        CONSTRAINT clientcurrprod_fkey1 FOREIGN KEY(productid) REFERENCES product(productid) ON DELETE CASCADE,
        CONSTRAINT clientcurrprod_fkey2 FOREIGN KEY(clientid) REFERENCES client(clientid) ON DELETE CASCADE
      )";
      $qResult_ccrp = @$conn -> query($ccrpTableSql);
      if (!$qResult_ccrp)
        echo "<p>Creation of clientCurrentProduct table failed: ". $conn -> errno . "-" . $conn -> error . "</p><br />";
      else
        echo "<p>clientCurrentProduct table created successfully</p><br />";
      /*
        create client rented product table
      */
      $crpTableSql = "CREATE TABLE IF NOT EXISTS clientRentedProduct (
        clientid INT UNSIGNED NOT NULL,
        productid INT UNSIGNED NOT NULL,
        CONSTRAINT clientrentedprod_fkey1 FOREIGN KEY(productid) REFERENCES product(productid) ON DELETE CASCADE,
        CONSTRAINT clientrentedprod_fkey2 FOREIGN KEY(clientid) REFERENCES client(clientid) ON DELETE CASCADE
      )";
      $qResult_crp = @$conn -> query($crpTableSql);
      if (!$qResult_crp)
        echo "<p>Creation of clientRentedProduct table failed: ". $conn -> errno . "-" . $conn -> error . "</p><br />";
      else
        echo "<p>clientRentedProduct table created successfully</p><br />";
      $conn -> close();
    }
    ?>
  </body>
</html>
