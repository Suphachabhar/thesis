<?php

session_start();

if(isset($_SESSION['username'])){
    $_SESSION['msg'] = "You must login first";
    header("location: login.php");
}

if(isset($_GET['logout'])){
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");     //back to login page
}

//isset = Determine if a variable is declared and is different than NULL
//unset = Unset a given variable

?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Page</title>
</head>
<body>
    <h1> This is a course page</h1>
    <?php
        if(isset($_SESSION['success'])) : ?>
    
    <div>
        <h3>
            <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </h3>
    </div>

    <?php endif ?>

//if the user logs in print the info

    <?php if(isset($_SESSION['username'])) : ?>
        <h3> Welcome <strong><?php echo $_SESSION['username']; ?><strong></h3>
        <button><a href="index.php?logout='1'"></a></button>
    <?php endif ?>


</body>
</html>