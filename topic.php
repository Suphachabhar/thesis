<?php
    require_once("database.php");
    session_start();
?>

<html>
<?php if (!isset($_GET['id'])) : 
    $_SESSION['success'] = "Invalid course ID.";
    header('location: index.php');
else : 
    $query = "SELECT name FROM topics where id = ".$_GET['id']." LIMIT 1";
    $results = mysqli_query($db, $query);
    
    if (mysqli_num_rows($results) != 0) :
        $topic = mysqli_fetch_assoc($results);
?>
<head>
    <title><?php echo $topic['name']; ?></title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
    
<body>
	<div class="header">
		<h2><?php echo $topic['name']; ?></h2>
	</div>
	<div class="content">
		<?php if (isset($_SESSION['success'])) : ?>
			<div class="error success">
				<h3>
					<?php 
						echo $_SESSION['success']; 
						unset($_SESSION['success']);
					?>
				</h3>
			</div>
		<?php endif ?>
        
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) : ?>
            <form action="topic_handler.php" method="post" enctype="multipart/form-data">
                <input name="function" value="upload" hidden>
                <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                <input type="file" name="fileToUpload">
                <input type="submit" value="Upload File">
            </form>
        <?php endif ?>
	</div>
</body>
<?php 
    else :
        $_SESSION['success'] = "Invalid course ID.";
        header('location: index.php');
    endif;
endif ?>
</html>