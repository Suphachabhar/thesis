<?php

session_start();

//connect to the database

$db = mysqli_connect('localhost', 'root', '', 'thesis') or die("could not connect to database");

$username = "";
$email = "";
$errors = array();

//call registration() if register_btn is clicked
if(isset($_POST['register_btn'])){
    registration();
}

function registration(){
    global $db, $errors, $username, $email;
}




?>