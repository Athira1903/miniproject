<?php
include("db.php");
session_start();
$name=$_POST["name"];
$email=$_POST["email"];
$password=$_POST["password"];
// die('Success');
$inn="insert into users(name,email,password)values('$name','$email','$password')";
if(mysqli_query($conn,$inn))
{
    header("Location: http://localhost/furiture/login.html");
    exit();}
?>