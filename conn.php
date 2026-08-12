<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "hotelbooking"; // Make sure this matches your MySQL DB name in phpMyAdmin

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>