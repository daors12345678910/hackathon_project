<?php

$user="root";
$pass="";
$server="localhost";
$dbname="clothesDonation";

define('ADMIN_SIGNUP_CODE', 'KindSocietyAdmin2026!');

try {
	
	$conn =new PDO("mysql:host=$server;dbname=$dbname",$user,$pass);
} catch (PDOException $e) {
   
    die("Database connection failed: " . $e->getMessage());

}

?>