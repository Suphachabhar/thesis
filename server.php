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
    //use global to make them avilable in funciton
    global $db, $errors, $username, $email;

    $username = e($_POST['username']);
    $email = e($_POST['email']);
    $password_1 = e($_POST['password_1']);
    $password_2 = e($_POST['password_2']);

    //validation 
    if(empty($username)){
        array_push($errors, "Username is required");
    }

    if(empty($email)){
        array_push($errors, "Email is required");
    }

    if(empty($password_1)){
        array_push($errors, "Password is required");
    }

    if($password_1 != $password_2){
        array_push($errors, "Passwords do not match");
    }


    if(count($errors) == 0){
        $password = md5($password_1);
        //encrypt the password before saving to the database

        if(isset($_POST['user_type'])){
            $user_type = e($_POST['user_type']);
            $query = "INSERT INTO user (username, email, user_type, password) 
                      VALUES('$username', '$email', '$user_type', '$password')";
            mysqli_query($db, $query);

            $_SESSION['success']  = "New user successfully created!!";
			header('location: home.php');
		}else{
			$query = "INSERT INTO user (username, email, user_type, password) 
					  VALUES('$username', '$email', 'user', '$password')";
            mysqli_query($db, $query);
            
            $logged_in_user_id = mysqli_insert_id($db);

			$_SESSION['user'] = getUserById($logged_in_user_id); // put logged in user in session
			$_SESSION['success']  = "You are now logged in";
			header('location: index.php');	

        }
    }

}

// return user array from their id
function getUserById($id){
	global $db;
	$query = "SELECT * FROM user WHERE id=" . $id;
	$result = mysqli_query($db, $query);

	$user = mysqli_fetch_assoc($result);
	return $user;
}

// escape string
function e($val){
	global $db;
	return mysqli_real_escape_string($db, trim($val));
}

function display_error() {
	global $errors;

	if (count($errors) > 0){
		echo '<div class="error">';
			foreach ($errors as $error){
				echo $error .'<br>';
			}
		echo '</div>';
	}
}

function isLoggedIn()
{
	if (isset($_SESSION['user'])) {
		return true;
	}else{
		return false;
	}
}

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: login.php");
}

// call the login() function if register_btn is clicked
if (isset($_POST['login_btn'])) {
	login();
}

// LOGIN USER
function login(){
	global $db, $username, $errors;

	// grap form values
	$username = e($_POST['username']);
	$password = e($_POST['password']);

	// make sure form is filled properly
	if (empty($username)) {
		array_push($errors, "Username is required");
	}
	if (empty($password)) {
		array_push($errors, "Password is required");
	}

	// attempt login if no errors on form
	if (count($errors) == 0) {
		$password = md5($password);

		$query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
		$results = mysqli_query($db, $query);

		
			$logged_in_user = mysqli_fetch_assoc($results);
			if ($logged_in_user['user_type'] == 1) {

				$_SESSION['user'] = $logged_in_user;
				$_SESSION['success']  = "You are now logged in";
				header('location: home.php');		  
			}else{
				$_SESSION['user'] = $logged_in_user;
				$_SESSION['success']  = "You are now logged in";

				header('location: index.php');
			}
		
	}
}

function isAdmin()
{
	if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin' ) {
		return true;
	}else{
		return false;
	}
}

?>