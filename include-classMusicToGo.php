<?php
  class user
  {
    private $conn = NULL;
    private $name = "";
    private $surname = "";
    private $password = "";
    private $phone = "";
    private $email = "";
    private $type = "";

    function __construct ($name, $surname, $password, $phone, $email)
    {
      $this -> name = $name;
      $this -> surname = $surname;
      $this -> password = $password;
      $this -> phone = $phone;
      $this -> email = $email;
      include("include-dbConnect.php");
      $this -> conn = $conn;
    }

    // mutator functions
    public function setName ($name)
    {
      $this -> name = $name;
    }

    public function setSurname ($surname)
    {
      $this -> surname = $surname;
    }

    protected function setPassword ($password)
    {
      $this -> password = $password;
    }

    public function setPhone ($phone)
    {
      $this -> phone = $phone;
    }

    public function setEmail ($email)
    {
      $this -> email = $email;
    }

    public function getName()
    {
      return ($this -> name);
    }

    public function getSurname ()
    {
      return ($this -> surname);
    }

    protected function getPassword ()
    {
      return ($this -> password);
    }

    public function getPhone ()
    {
      return ($this -> phone);
    }

    public function getEmail ()
    {
      return ($this -> email);
    }

    public function getConn ()
    {
      return ($this -> conn);
    }

    protected function checkOverdue($productid, $rentDate)
    {
      $rentDate = strtotime($rentDate);
      $today = strtotime(date('Y-m-d', time()));
      if($today > $rentDate)
      {
        $sql = "UPDATE product SET rentstat=? WHERE productid=?";
        if($stmt = @$this -> getConn() -> prepare($sql))
        {
            $stmt -> bind_param("si", $rentStat, $productId);
            $rentStat = "overdue";
            $productId = $productid;
            if($stmt -> execute() === false)
              echo "<p>Failed to change rent status: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
        }
        else
          echo "<p>Could not find the product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      }
    }

    // shared functions
    protected function getProduct($productid) // for retrieving individual products
    {
      $sql = "SELECT category, brand, yom, characteristics, status, costreg, costover, rentstat, rentdate, daterented FROM product WHERE productid='$productid'";
      $res = @$this -> getConn() -> query($sql);
      $prodArray = array();
      if($res -> num_rows > 0)
      {
        $row = $res -> fetch_assoc();
        if(!empty($row['rentdate']))
          $this -> checkOverdue($productid, $row['rentdate']);
        $resUpdated = @$this -> getConn() -> query($sql);
        if($resUpdated -> num_rows > 0)
        {
          $rowUpdated = $resUpdated -> fetch_assoc();
          $product = new product ($rowUpdated['category'], $rowUpdated['brand'], $rowUpdated['yom'], $rowUpdated['characteristics'], $rowUpdated['costreg'], $rowUpdated['costover']);
          $prodArray['generalDetails'] = $product;
          $prodArray['prodStatus'] = $rowUpdated['status'];
          $prodArray['prodRentStat'] = $rowUpdated['rentstat'];
          $prodArray['prodRentDate'] = $rowUpdated['rentdate'];
          $prodArray['prodDateRented'] = $rowUpdated['daterented'];
        }
        else
          echo "<p>Failed to retrieve product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p>";
      }
      else
        echo "<p>Failed to retrieve product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p>";
      $res -> free_result();
      $resUpdated -> free_result();
      return $prodArray;
    }

    private function retrieveAvailable()
    {
      $status = "available";
      $sql = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product WHERE status='$status' ORDER BY productid";
      $res = @$this -> getConn() -> query($sql);
      $product = array();
      if($res -> num_rows == 0)
        echo "<p><span class='error'>There are no available products.</span></p>";
      else
      {
        while (($row = $res -> fetch_assoc()) != false)
        {
          $product[] = $row;
        }
      }
      $res -> free_result();
      return $product;
    }

    public function displayAvailable()
    {
      $productArray = $this -> retrieveAvailable(); // arrays containing assoc arrays
      foreach($productArray as $product)
      {
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                "<td>" . htmlentities($product['category']) . "</td>" .
                "<td>" . htmlentities($product['brand']) . "</td>" .
                "<td>" . htmlentities($product['yom']) . "</td>" .
                "<td>" . htmlentities($product['characteristics']) . "</td>" .
                "<td>" . htmlentities($product['status']) . "</td>" .
                "<td>" . htmlentities($product['costreg']) . "</td>" .
                "<td>" . htmlentities($product['costover']) . "</td>" .
              "</tr>";
      }
      echo "<p>These are the currently available products.</p>";
    }

    protected function retrieveIndiProduct($productid) // with client payment information
    {
      $product = $this -> getProduct($productid);
      $sqlClient = "SELECT payment FROM client WHERE clientid IN (SELECT clientCurrentProduct.clientid FROM client JOIN clientCurrentProduct
                                                                  ON client.clientid = clientCurrentProduct.clientid
                                                                  WHERE productid='$productid')";
      $resClient = @$this -> getConn() -> query($sqlClient);
      $rowClient = NULL;
      $row = array();
      $row['prodDetail'] = $product;
      if($resClient -> num_rows > 0) // add payment status to row
      {
        if(($rowClient = $resClient -> fetch_assoc()) === false)
          echo "<p>Error retrieving client payment status from array: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
        else
          $row['payment'] = $rowClient['payment'];
      }
      else
        $row['payment'] = "";
      $resClient -> free_result();
      return $row;
    }

    public function displayIndiProduct($productid)
    {
      $row = $this -> retrieveIndiProduct($productid); // row is an assoc array containing 1 set of product details with payment status
      $genProduct = $row['prodDetail']['generalDetails']; // product object formed with constructor
      $rentStatus = $row['prodDetail']['prodRentStat'];
      $rentDate = $row['prodDetail']['prodRentDate'];
      $status = $row['prodDetail']['prodStatus'];
      $payment = $row['payment'];
      $selectStatus = "";
      $selectStatusClient = "";
      $selectPayment = "";
      $selectPaymentClient = "";
      $classType = get_Class($this);
      $rentDateClient = "";
      $submitType = "";
      if(empty($rentStatus) && empty($rentDate))
      {
        $rentStatus = "not rented";
        $rentDate = "not rented";
        $rentDateClient = "<b>Rent duration</b>: <select name='rentduration'>
                            <option value='1'>1 day</option>
                            <option value='7'>7 days</option>
                            <option value='28'>28 days</option>
                           </select>";
       $submitType = "<input type='submit' name='rentproduct' value='Rent'/>";
      }
      else
      {
        $rentDateClient = "<b>Rent due date: </b>" . $rentDate;
        $submitType ="<input type='submit' name='returnproduct' value='Return'/>";
      }


      if($status == "available")
      {
        $selectStatus .= "<select name='status'><option value='available' selected='selected'>Available</option>
                          <option value='not available'>Not available</option></select>";
        $selectStatusClient .= "Available";
      }
      else
      {
        $selectStatus .= "<select name='status'><option value='not available' selected='selected'>Not Available</option>
                          <option value='available'>Available</option></select>";
        $selectStatusClient .= "Not available";
      }

      if(!empty($payment))
      {
        if($payment == "paid")
          $selectPayment .= "<select name='payment'><option value='paid' selected ='selected'>Paid</option>
                             <option value='not paid'>Not paid</option>
                             <option value='not rented'>Not Rented</option></select>";
        else
          $selectPayment .= "<select name='payment'><option value='not paid' selected ='selected'>Not paid</option>
                             <option value='paid'>Paid</option>
                             <option value='not rented'>Not rented</option></select>";
      }
      else
          $selectPayment .= "<select name='payment'><option value='not rented' selected ='selected'>Not rented</option>
                             <option value='paid'>Paid</option>
                             <option value='not paid'>Not paid</option></select>";

      if($classType == "admin")
      {
        echo "<form action='notification.php' method='GET'>" .
                "<input type='hidden' name='productid' value='" . htmlentities($productid) . "'/>" .
                "<p><b>Category</b>: <label>" . $genProduct -> getCategory() . "</label></p>" .
                "<p><b>Brand</b>: <label>" . $genProduct -> getBrand() . "</label></p>" .
                "<p><b>Year of manufacture</b>: <label>" . $genProduct -> getYOM() . "</label></p>" .
                "<p><b>Characteristics</b>: <label>" . $genProduct -> getChar() . "</label></p>" .
                "<p><b>Status</b>: " . $selectStatus .
                "<p><b>Cost/day - regular</b>: <label>" . $genProduct -> getRegCost() . "</label></p>" .
                "<p><b>Cost/day - overdue</b>: <label>" . $genProduct -> getOverCost() . "</label></p>" .
                "<p><b>Rent status</b>: <label>" . $rentStatus . "</label></p>" .
                "<p><b>Rent due date</b>: <label>" . $rentDate . "</label></p>" .
                "<p><b>Payment status</b>: " . $selectPayment . "</p>" .
              "<input type='submit' name='saveproduct' value='Save'/>" .
              "</form>";
      }
      else if($classType = "client")
      {
          echo "<form action='notification.php' method='GET'>" .
                  "<input type='hidden' name='productid' value='" . htmlentities($productid) . "'/>" .
                  "<p><b>Category</b>: <label>" . $genProduct -> getCategory() . "</label></p>" .
                  "<p><b>Brand</b>: <label>" .$genProduct -> getBrand() . "</label></p>" .
                  "<p><b>Year of manufacture</b>: <label>" . $genProduct -> getYOM() . "</label></p>" .
                  "<p><b>Characteristics</b>: <label>" . $genProduct -> getChar() . "</label></p>" .
                  "<p><b>Status</b>: <label>" . $selectStatusClient . "</label></p>" .
                  "<p><b>Cost/day - regular</b>: <label>" . $genProduct -> getRegCost() . "</label></p>" .
                  "<p><b>Cost/day - overdue</b>: <label>" . $genProduct -> getOverCost() . "</label></p>" .
                  "<p><b>Rent status</b>: <label>" . $rentStatus . "</label></p>" .
                  "<p>" . $rentDateClient . "</p>" .
                  $submitType .
                "</form>";
        }
    }

    // search functions
    private function retrieveSearch($searchTypes) // currently only accounts for category and status TOGETHER
    {
      $sqlArray = array();
      $product = array();
      foreach($searchTypes as $type)
      {
        if(isset($_GET[$type]) && !empty($_GET[$type]))
        {
          if($type == "status" && ($_GET[$type] == "available" || $_GET[$type] == "not available"))
          {
            $searchData = trim(stripslashes($_GET[$type]));
            $sqlArray[] = "$type = '$searchData'";
          }
          else if($_GET[$type] != "none")
          {
            $searchData = trim(stripslashes($_GET[$type]));
            $sqlArray[] = "$type LIKE '%$searchData%'";
          }
        }
      }
      $sql = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product";
      if(count($sqlArray) > 0)
      {
        $sql .= " WHERE " . implode(" AND ", $sqlArray) . "ORDER BY productid";
        $res = @$this -> getConn() -> query($sql);
        if($res -> num_rows > 0)
        {
          while(($row = $res -> fetch_assoc()) != false)
          {
            $product[] = $row;
          }
        }
        else
          echo "<p><span class='error'>No matches found</span></p>";
      }
      else
        echo "<p><span class='error'>You did not input any search conditions</span><p>";
      return $product;
    }

    public function search($searchTypes)
    {
      $productArray = $this -> retrieveSearch($searchTypes);
      $classType = get_Class($this);
      if($classType == "admin")
      {
        foreach($productArray as $product)
        {
            echo "<tr>" .
                    "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                    "<td>" . htmlentities($product['category']) . "</td>" .
                    "<td>" . htmlentities($product['brand']) . "</td>" .
                    "<td>" . htmlentities($product['yom']) . "</td>" .
                    "<td>" . htmlentities($product['characteristics']) . "</td>" .
                    "<td>" . htmlentities($product['status']) . "</td>" .
                    "<td>" . htmlentities($product['costreg']) . "</td>" .
                    "<td>" . htmlentities($product['costover']) . "</td>" .
                  "</tr>";
        }
      }
      else if($classType == "client")
      {
        foreach($productArray as $product)
        {
          if($product['status'] == "not available")
          {
            echo "<tr>" .
                    "<td>" . htmlentities($product['productid']) . "</td>" .
                    "<td>" . htmlentities($product['category']) . "</td>" .
                    "<td>" . htmlentities($product['brand']) . "</td>" .
                    "<td>" . htmlentities($product['yom']) . "</td>" .
                    "<td>" . htmlentities($product['characteristics']) . "</td>" .
                    "<td>" . htmlentities($product['status']) . "</td>" .
                    "<td>" . htmlentities($product['costreg']) . "</td>" .
                    "<td>" . htmlentities($product['costover']) . "</td>" .
                  "</tr>";
          }
          else
          {
            echo "<tr>" .
                    "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                    "<td>" . htmlentities($product['category']) . "</td>" .
                    "<td>" . htmlentities($product['brand']) . "</td>" .
                    "<td>" . htmlentities($product['yom']) . "</td>" .
                    "<td>" . htmlentities($product['characteristics']) . "</td>" .
                    "<td>" . htmlentities($product['status']) . "</td>" .
                    "<td>" . htmlentities($product['costreg']) . "</td>" .
                    "<td>" . htmlentities($product['costover']) . "</td>" .
                  "</tr>";
          }
        }
      }
     }

    function __wakeup () {
  		include("include-dbConnect.php");
  		$this -> conn = $conn;
  	}

    function __destruct () {
  		if (!$this -> conn -> connect_error)
  			@$this -> conn -> close();
  	}
  }

  class admin extends user
  {
    private $adminId = 0;

    // mutator functions
    public function setId($id)
    {
      $this -> adminId = $id;
    }

    // accessor functions
    public function getId()
    {
      $email = $this -> getEmail();
      $sql = "SELECT adminid FROM admin WHERE email='$email'";
      $res = @$this -> getConn() -> query($sql);
      if($res -> num_rows > 0)
      {
        $row = $res -> fetch_assoc();
        $adminId = $row['adminid'];
      }
      else
        echo "<p>Failed to retrieve admin id: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p>";
      $res -> free_result();
      return $adminId;
    }

    // site functionality functions for admin
    public function checkDuplicates()
    {
      $email = $this -> getEmail();
      $phone = $this -> getPhone();
      $errorMsg = "";
      $sqlPhone = "SELECT phone FROM admin WHERE phone='$phone'";
      $sqlEmail = "SELECT email FROM admin WHERE email='$email'";
      $resPhone = @$this -> getConn() -> query($sqlPhone);
      $resEmail = @$this -> getConn() -> query($sqlEmail);
      if($resPhone -> num_rows > 0)
        $errorMsg .= "<span class='error'>This phone number has already been taken</span>";
      else if($resEmail -> num_rows > 0)
        $errorMsg .= "<span class='error'>This email has already been taken</span>";
      $resPhone -> free_result();
      $resEmail -> free_result();
      return $errorMsg;
    }

    public function registerAdmin ()
    {
      $insertSql = "INSERT INTO admin (passwordmd5, name, surname, phone, email) VALUES (?, ?, ?, ?, ?)";
      if ($stmt = @$this -> getConn() -> prepare($insertSql))
      {
        $stmt -> bind_param("sssss", $password, $name, $surname, $phone, $email);
        $password = md5($this -> getPassword());
        $name = $this -> getName();
        $surname = $this -> getSurname();
        $phone = $this -> getPhone();
        $email = $this -> getEmail();
        if($stmt -> execute())
        {
          $this -> setId($this -> getId());
          echo "<p>Successfully created new admin account.</p>";
        }
        else
          echo "<p>Failed to register: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      }
      else
        echo "<p>Failed to insert new admin: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      $stmt -> close();
    }

    public function verifyAdminLogin($email, $pass)
    {
      $actualEmail = $this -> getEmail();
      $actualPass = $this -> getPassword();
      if($email == $actualEmail && $pass == $actualPass)
        return true;
      return false;
    }

    private function retrieveOverdueProducts()
    {
      $sqlRented = "SELECT productid, rentdate FROM product WHERE rentstat='rented'";
      $resRented = @$this -> getConn() -> query($sqlRented);
      if($resRented -> num_rows == 0)
      {
        $sqlOverdue = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product WHERE rentstat='overdue' ORDER BY productid";
        $resOverdue = @$this -> getConn() -> query($sqlOverdue);
        $product = array();
        if($resOverdue -> num_rows == 0)
          echo "<p><span class='error'>There are no products that are overdue.</span></p>";
        else
        {
          while(($rowOverdue = $resOverdue -> fetch_assoc()) != false)
          {
            $product[] = $rowOverdue;
          }
        }
      }
      else
      {
        while(($rowRented = $resRented -> fetch_assoc()) != false)
        {
          $rentDueDate = $rowRented['rentdate'];
          $productId = $rowRented['productid'];
          $this -> checkOverdue($productId, $rentDueDate);
        }
        $sqlOverdue = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product WHERE rentstat='overdue' ORDER BY productid";
        $resOverdue = @$this -> getConn() -> query($sqlOverdue);
        $product = array();
        if($resOverdue -> num_rows == 0)
          echo "<p><span class='error'>There are no products that are overdue.</span></p>";
        else
        {
          while(($rowOverdue = $resOverdue -> fetch_assoc()) != false)
          {
            $product[] = $rowOverdue;
          }
        }
      }
      $resRented -> free_result();
      $resOverdue -> free_result();
      return $product;
    }

    private function retrieveAllProducts()
    {
      $sql = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product ORDER BY productid";
      $res = @$this -> getConn() -> query($sql);
      $product = array();
      if($res -> num_rows == 0)
        echo "<p><span class='error'>There are no products at all.</span></p>";
      else
      {
        while (($row = $res -> fetch_assoc()) != false)
        {
          $product[] = $row;
        }
      }
      $res -> free_result();
      return $product;
    }

    private function retrieveRentedProducts()
    {
      $sql = "SELECT productid, category, brand, yom, characteristics, status, costreg, costover FROM product WHERE rentstat='rented' OR rentstat='overdue' ORDER BY productid";
      $res = @$this -> getConn() -> query($sql);
      $product = array();
      if($res -> num_rows == 0)
        echo "<p><span class='error'>There are no products that are rented.</span></p>";
      else
      {
        while (($row = $res -> fetch_assoc()) != false)
        {
          $product[] = $row;
        }
      }
      $res -> free_result();
      return $product;
    }

    private function saveNoti($update)
    {
      $display = "<p>Update successful" . $update . " has been changed.</p>";
      return $display;
    }

    private function saveUpdate($productid, $status, $payment)
    {
      $sqlPayment = "UPDATE client SET payment='$payment' WHERE clientid IN (SELECT clientCurrentProduct.clientid FROM client JOIN clientCurrentProduct
                                                                  ON client.clientid = clientCurrentProduct.clientid
                                                                  WHERE productid='$productid')";
      $sqlStatus = "UPDATE product SET status=? WHERE productid=?";
      $sqlClient = "SELECT clientid FROM client WHERE clientid IN (SELECT clientCurrentProduct.clientid FROM client JOIN clientCurrentProduct
                                                                   ON client.clientid = clientCurrentProduct.clientId
                                                                   WHERE productid='$productid')";

      if(!empty($payment))
      {
        if(@$this -> getConn() -> query($sqlPayment) === false)
          echo "<p>Client does not exist</p>";
        else
        {
          $resClient = @$this -> getConn() -> query($sqlClient);
          if($resClient -> num_rows > 0)
          {
            $row = $resClient -> fetch_assoc();
            $update = ", payment status for client ID " . $row['clientid'] . " for the product ID $productid";
            echo $this -> saveNoti($update);
          }
          else
            echo "<p>There is no such client</p>";
          $resClient -> free_result();
        }
      }
      else
      {
        if(@$this -> getConn() -> query($sqlPayment) === false)
          echo "<p>Client does not exist</p>";
        else
        {
          $resClient = @$this -> getConn() -> query($sqlClient);
          if($resClient -> num_rows > 0)
          {
            $row = $resClient -> fetch_assoc();
            $update = ", payment status for client ID " . $row['clientid'] . " for the product ID $productid";
            echo $this -> saveNoti($update);
          }
          $resClient -> free_result();
        }
      }

      if(!empty($status))
      {

        if($stmt = @$this -> getConn() -> prepare($sqlStatus))
        {
          $stmt -> bind_param("si", $Status, $productId);
          $Status = $status;
          $productId = $productid;
          if($stmt -> execute())
          {
            $update = ", status for the product ID $productid";
            echo $this -> saveNoti($update);
          }
          else
            echo "<p>Product does not exist</p>";
        }
        $stmt -> close();
      }
    }

    public function saveProduct($productid, $status, $payment)
    {
      $this -> saveUpdate($productid, $status, $payment);
    }

    public function displayAllProducts()
    {
      $productArray = $this -> retrieveAllProducts(); // arrays containing assoc arrays
      foreach($productArray as $product)
      {
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                "<td>" . htmlentities($product['category']) . "</td>" .
                "<td>" . htmlentities($product['brand']) . "</td>" .
                "<td>" . htmlentities($product['yom']) . "</td>" .
                "<td>" . htmlentities($product['characteristics']) . "</td>" .
                "<td>" . htmlentities($product['status']) . "</td>" .
                "<td>" . htmlentities($product['costreg']) . "</td>" .
                "<td>" . htmlentities($product['costover']) . "</td>" .
              "</tr>";

      }
      echo "<p>These are all the products there is.</p>";
    }

    public function displayRentedProducts()
    {
      $productArray = $this -> retrieveRentedProducts();
      foreach($productArray as $product)
      {
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                "<td>" . htmlentities($product['category']) . "</td>" .
                "<td>" . htmlentities($product['brand']) . "</td>" .
                "<td>" . htmlentities($product['yom']) . "</td>" .
                "<td>" . htmlentities($product['characteristics']) . "</td>" .
                "<td>" . htmlentities($product['status']) . "</td>" .
                "<td>" . htmlentities($product['costreg']) . "</td>" .
                "<td>" . htmlentities($product['costover']) . "</td>" .
              "</tr>";

      }
      echo "<p>These are the currently rented products.</p>";
    }

    public function displayOverdueProducts()
    {
      $productArray = $this -> retrieveOverdueProducts();
      foreach($productArray as $product)
      {
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($product['productid']) . "'>" . htmlentities($product['productid']) . "</a></td>" .
                "<td>" . htmlentities($product['category']) . "</td>" .
                "<td>" . htmlentities($product['brand']) . "</td>" .
                "<td>" . htmlentities($product['yom']) . "</td>" .
                "<td>" . htmlentities($product['characteristics']) . "</td>" .
                "<td>" . htmlentities($product['status']) . "</td>" .
                "<td>" . htmlentities($product['costreg']) . "</td>" .
                "<td>" . htmlentities($product['costover']) . "</td>" .
              "</tr>";

      }
      echo "<p>These are the currently overdue products.</p>";
    }

    public function insertProduct($product)
    {
      $sql = "INSERT INTO product (category, brand, yom, characteristics, status, costreg, costover) VALUES (?, ?, ?, ?, ?, ?, ?)";
      if ($stmt = @$this -> getConn() -> prepare($sql))
      {
        $stmt -> bind_param("sssssdd", $category, $brand, $yom, $characteristics, $status, $costReg, $costOver);
        $category = $product -> getCategory();
        $brand = $product -> getBrand();
        $yom = $product -> getYOM();
        $characteristics = $product -> getChar();
        $status = "available";
        $costReg = (float)($product -> getRegCost());
        $costOver = (float)($product -> getOverCost());
        if($stmt -> execute())
        {
          $product -> setId($this -> getConn() -> insert_id);
          $product -> setStatus($status);
        }
        else
          echo "<p>Failed to insert new product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "<p><br />";
      }
      else
        echo "<p>Failed to insert new product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      $stmt -> close();
    }
  }

  class client extends user
  {
    private $payment = "";
    private $clientId = 0;

    private $rentDuration = "";

    // mutator functions
    public function setId($id)
    {
      $this -> clientId = $id;
    }

    public function setPayment($payment)
    {
      $this -> payment = $payment;
    }

    private function setRentDuration($rentDuration)
    {
      $this -> rentDuration = $rentDuration;
    }

    // accessor functions
    public function getId()
    {
      $email = $this -> getEmail();
      $sql = "SELECT clientid FROM client WHERE email='$email'";
      $res = @$this -> getConn() -> query($sql);
      if($res -> num_rows > 0)
      {
        $row = $res -> fetch_assoc();
        $clientId = $row['clientid'];
      }
      else
        echo "<p>Failed to retrieve client id: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p>";
      $res -> free_result();
      return $clientId;
    }

    public function getPayment()
    {
      return ($this -> payment);
    }

    public function checkDuplicates()
    {
      $email = $this -> getEmail();
      $phone = $this -> getPhone();
      $errorMsg = "";
      $sqlPhone = "SELECT phone FROM client WHERE phone='$phone'";
      $sqlEmail = "SELECT email FROM client WHERE email='$email'";
      $resPhone = @$this -> getConn() -> query($sqlPhone);
      $resEmail = @$this -> getConn() -> query($sqlEmail);
      if($resPhone -> num_rows > 0)
        $errorMsg .= "<span class='error'>This phone number has already been taken</span>";
      else if($resEmail -> num_rows > 0)
        $errorMsg .= "<span class='error'>This email has already been taken</span>";
      $resPhone -> free_result();
      $resEmail -> free_result();
      return $errorMsg;
    }

    public function registerClient ()
    {
      $insertSql = "INSERT INTO client (passwordmd5, name, surname, phone, email) VALUES (?, ?, ?, ?, ?)";
      if ($stmt = @$this -> getConn() -> prepare($insertSql))
      {
        $stmt -> bind_param("sssss", $password, $name, $surname, $phone, $email);
        $password = md5($this -> getPassword());
        $name = $this -> getName();
        $surname = $this -> getSurname();
        $phone = $this -> getPhone();
        $email = $this -> getEmail();
        if($stmt -> execute())
        {
          $this -> setId($this -> getId());
          echo "<p>Successfully created new client account.</p>";
        }
        else
          echo "<p>Failed to register: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "<p><br />";
      }
      else
        echo "<p>Failed to insert new client: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      $stmt -> close();
    }

    public function verifyClientLogin($email, $pass)
    {
      $actualEmail = $this -> getEmail();
      $actualPass = $this -> getPassword();
      if($email == $actualEmail && $pass == $actualPass)
        return true;
      return false;
    }

    private function rentNoti($productid, $rentDuration, $rentDueDate, $dateRented)
    {
      $productArray = $this -> getProduct($productid);
      $product = $productArray['generalDetails'];
      $regCost = ($product -> getRegCost()) * $rentDuration;
      $overCost = $product -> getOverCost();
      $display = "<p>Rent successful, please take note of the following details: <br />
                     The date that you have rented this product is $dateRented <br />
                     The renting duration of the product is $rentDuration day(s) <br />
                     The due date for the rent of the product is $rentDueDate <br />
                     The cost of rent for returning the product on time is $$regCost<br />
                     The cost of rent for returning the product <b>late</b> is $$overCost per day
                  </p>";
      return $display;
    }

    private function returnNoti($rentstatnoti, $totalCost)
    {
      $display = "<p>Return successful, please take note of the following details: <br/>
                     You have returned the product <b>$rentstatnoti</b> <br />
                     The cost of the rental will be $totalCost
                  </p>";
      return $display;
    }

    private function updateRentProduct($productid, $rentDuration)
    {
      $rentDueDate = "";
      $clientSql = "UPDATE client SET payment=? WHERE clientid=?";
      $productSql = "UPDATE product SET status=?, rentstat=?, rentdate=?, daterented=? WHERE productid=?";
      $ccrpSql = "INSERT INTO clientCurrentProduct (clientid, productid) VALUES(?, ?)";
      if(($stmtClient = @$this -> getConn() -> prepare($clientSql)) && ($stmtProduct = @$this -> getConn() ->prepare($productSql))
          && ($stmtCCRP = @$this -> getConn() -> prepare($ccrpSql)))
      {
        $stmtClient -> bind_param("si", $payment, $clientId1);
        $stmtProduct -> bind_param("ssssi", $status, $rentStat, $rentDueDate, $dateRented, $productId1);
        $stmtCCRP -> bind_param("ii", $clientId2, $productId2);
        $payment = "not paid";
        $clientId1 = $this -> getId();
        $status = "not available";
        $rentStat = "rented";
        if($rentDuration === "1")
          $rentDueDate = date("Y-m-d", time() + 86400);
        else if ($rentDuration == "7")
          $rentDueDate = date("Y-m-d", time() + (86400 * 7));
        else if ($rentDuration == "28")
          $rentDueDate = date("Y-m-d", time() + (86400 * 28));
        $dateRented = date("Y-m-d");
        $productId1 = $productid;
        $clientId2 = $this -> getId();
        $productId2 = $productid;
        if(($stmtClient -> execute()) && ($stmtProduct -> execute()) && ($stmtCCRP -> execute()))
            echo $this -> rentNoti($productid, $rentDuration, $rentDueDate, $dateRented);
        else
          echo "<p>The product has already been rented.<p><br />";
      }
      else
        echo "<p>Failed to rent product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      $stmtClient -> close();
      $stmtProduct -> close();
      $stmtCCRP -> close();
    }

    public function rentProduct($productid, $rentDuration)
    {
      $this -> updateRentProduct($productid, $rentDuration);
    }

    private function processReturn($productid)
    {
      $sql = "SELECT costreg, costover, rentdate, daterented FROM product WHERE productid='$productid'";
      $res = @$this -> getConn() -> query($sql);
      if($res -> num_rows > 0)
      {
        $row = $res -> fetch_assoc();
        $costReg = $row['costreg'];
        $costOver = $row['costover'];
        $rentDueDate = $row['rentdate'];
        $dateRented = $row['daterented'];
      }
      else
        echo "<p>No such product exists: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
      $returnDetails = array();
      $today = strtotime(date('Y-m-d', time()));
      $rentDueDate = strtotime($rentDueDate);
      $dateRented = strtotime($dateRented);
      // 86400ms per day, returns number of days
      $dateDiff1 = round(($today - $dateRented)/(86400));
      $dateDiff2 = round(($today - $rentDueDate)/(86400));
      $rentDuration = round(($rentDueDate - $dateRented)/(86400));
      if($today > $rentDueDate) // overdue
      {
        $regCost = $costReg * $rentDuration;
        $overCost = $costOver * $dateDiff2;
        $totalCost = $regCost + $overCost;
        $returnDetails['returnnoti'] = "overdue";
      }
      else if($today == $rentDueDate) // on time
      {
        $totalCost = $costReg * $rentDuration;
        $returnDetails['returnnoti'] = "on time";
      }
      else if($today < $rentDueDate) // early return
      {
        $totalCost = $costReg * $dateDiff1;
        if($totalCost == 0)
          $totalCost = $costReg;
        $returnDetails['returnnoti'] = "early";
      }
      $returnDetails['totalcost'] = $totalCost;
      return $returnDetails;
    }

    private function updateReturnProduct($productid)
    {
      $returnDetails = $this -> processReturn($productid);
      $clientSql = "UPDATE client SET payment=? WHERE clientid=?";
      $productSql = "UPDATE product SET status=?, rentstat=?, rentdate=?, daterented=? WHERE productid=?";
      $ccrpSql = "DELETE FROM clientCurrentProduct WHERE productid=?";
      $crpSql = "INSERT INTO clientRentedProduct (clientid, productid) VALUES(?, ?)";
      if(($stmtClient = @$this -> getConn() -> prepare($clientSql)) && ($stmtProduct = @$this -> getConn() ->prepare($productSql))
          && ($stmtCCRP = @$this -> getConn() -> prepare($ccrpSql)) && ($stmtCRP = @$this -> getConn() -> prepare($crpSql)))
      {
        $stmtClient -> bind_param("si", $payment, $clientId1);
        $stmtProduct -> bind_param("ssssi", $status, $rentStat, $rentDueDate, $dateRented, $productId1);
        $stmtCCRP -> bind_param("i", $productId2);
        $stmtCRP -> bind_param("ii", $clientId2, $productId3);
        $payment = "paid";
        $clientId1 = $this -> getId();
        $status = "available";
        $rentStat = NULL;
        $rentDueDate = NULL;
        $dateRented = NULL;
        $productId1 = $productid;
        $productId2 = $productid;
        $clientId2 = $this -> getId();
        $productId3 = $productid;
        if(($stmtClient -> execute()) && ($stmtProduct -> execute()) && ($stmtCCRP -> execute()))
        {
          echo $this -> returnNoti($returnDetails['returnnoti'], $returnDetails['totalcost']);
          if($stmtCRP -> execute() === false)
            echo "<p>Thank you for returning the product failed</p><br />";
          else
            echo "<p>Thank you for returning the product</p><br />";
        }
        else
          echo "<p>The product has already been returned.<p><br />";
     }
     else
        echo "<p>Failed to return product: " . $this -> getConn() -> errno . "-" . $this -> getConn() -> error . "</p><br />";
    }

    public function returnProduct($productid)
    {
      $this -> updateReturnProduct($productid);
    }

    private function retrieveCCRP()
    {
      $clientId = $this -> getId();
      $sql = "SELECT productid FROM clientCurrentProduct WHERE clientid='$clientId' ORDER BY productid";
      $res = @$this -> getConn() -> query($sql);
      $productList = array();
      if($res -> num_rows == 0)
        echo "<p><span class='error'>You are currently not renting any products.</span></p>";
      else
      {
        while(($row = $res -> fetch_assoc()) != false)
        {
          $productId = $row['productid'];
          $prodArray = $this -> getProduct($productId);
          $prodArray['id'] = $productId;
          $productList[] = $prodArray;
        }
      }

      $res -> free_result();
      return $productList;
    }

    private function retrieveCRP()
    {
      $clientId = $this -> getId();
      $sql = "SELECT DISTINCT productid FROM clientRentedProduct WHERE clientid='$clientId' ORDER BY productid";
      $res = @$this -> getConn() -> query($sql);
      $productList = array();
      if($res -> num_rows == 0)
        echo "<p><span class='error'>You have not rented any products before.</span></p>";
      else
      {
        while(($row = $res -> fetch_assoc()) != false)
        {
          $productId = $row['productid'];
          $prodArray = $this -> getProduct($productId);
          $prodArray['id'] = $productId;
          $productList[] = $prodArray;
        }
      }

      $res -> free_result();
      return $productList;
    }

    public function displayCCRP()
    {
      $productList = $this -> retrieveCCRP();
      foreach($productList as $product)
      {
        $prodGenDetails = $product['generalDetails'];
        $productId = $product['id'];
        $prodStatus = $product['prodStatus'];
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($productId) . "'>" . htmlentities($productId) . "</a></td>" .
                "<td>" . htmlentities($prodGenDetails -> getCategory()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getBrand()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getYOM()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getChar()) . "</td>" .
                "<td>" . htmlentities($prodStatus) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getRegCost()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getOverCost()) . "</td>" .
              "</tr>";
      }
      echo "<p>These are the products you are currently renting.</p>";
    }

    public function displayCRP()
    {
      $productList = $this -> retrieveCRP();
      foreach($productList as $product)
      {
        $prodGenDetails = $product['generalDetails'];
        $productId = $product['id'];
        $prodStatus = $product['prodStatus'];
        echo "<tr>" .
                "<td>" . "<a href='viewIndividualProduct.php?productid=" . htmlentities($productId) . "'>" . htmlentities($productId) . "</a></td>" .
                "<td>" . htmlentities($prodGenDetails -> getCategory()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getBrand()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getYOM()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getChar()) . "</td>" .
                "<td>" . htmlentities($prodStatus) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getRegCost()) . "</td>" .
                "<td>" . htmlentities($prodGenDetails -> getOverCost()) . "</td>" .
              "</tr>";
      }
      echo "<p>These are the products you have rented in the past.</p>";
    }

  }

  class product
  {
    private $productId = 0;

    private $category = "";
    private $brand = "";
    private $yom = "";
    private $characteristics = "";
    private $regCost = 0;
    private $overdueCost = 0;

    function __construct($category, $brand, $yom, $characteristics, $regCost, $overdueCost)
    {
      $this -> category = $category;
      $this -> brand = $brand;
      $this -> yom = $yom;
      $this -> characteristics = $characteristics;
      $this -> regCost = $regCost;
      $this -> overdueCost = $overdueCost;
    }

    // mutator functions
    public function setId($id)
    {
      $this -> productId = $id;
    }

    public function setStatus($status)
    {
      $this -> status = $status;
    }

    // accessor functions
    private function getConn()
    {
      return ($this -> conn);
    }

    public function getCategory()
    {
      return ($this -> category);
    }

    public function getBrand()
    {
      return ($this -> brand);
    }

    public function getYOM()
    {
      return ($this -> yom);
    }

    public function getChar()
    {
      return ($this -> characteristics);
    }

    public function getRegCost()
    {
      return ($this -> regCost);
    }

    public function getOverCost()
    {
      return ($this -> overdueCost);
    }
  }
?>
