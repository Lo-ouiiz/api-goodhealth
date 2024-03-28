<?php 
require_once  'config.php';
try {
    $con = new PDO(
        "mysql:host=$DATABASE_SERVER_IP;
         dbname=$DATABASE_NAME", 
         $DATABASE_USER_NAME, 
         $DATABASE_USER_PASSWORD
        );
    // set the PDO error mode to exception
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully";
  } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
?>