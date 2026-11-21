<?php

$user="root";
$pass="";
$server="localhost";
$dbname="clothesDonation";

try {
	
	$conn =new PDO("mysql:host=$server;dbname=$dbname",$user,$pass);
} catch (PDOException $e) {
   
    die("Database connection failed: " . $e->getMessage());

}

?>