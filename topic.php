<?php
    session_start();
    require_once("database.php");
    require_once("errors.php");
?>

<html>
<?php if (!isset($_GET['id'])) : 
    $_SESSION['success'] = invalidInputError("topic ID");
    header('location: '.mainPage());
else : 
    $query = "SELECT name FROM topics where id = ".$_GET['id']." LIMIT 1";
    $results = mysqli_query($db, $query);
    
    if (mysqli_num_rows($results) == 0) :
        $_SESSION['success'] = invalidInputError("topic ID");
        header('location: '.mainPage());
    else :
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
		<?php
            endif;
        
            $query = "SELECT id, name FROM subtopics where topic = ".$_GET['id'];
            $sList = mysqli_fetch_all(mysqli_query($db, $query));
            foreach ($sList as $subtopic) :
        ?>
            <div>
                <h3><?php echo $subtopic[1]; ?></h3>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) : ?>
                    <h4>Upload content</h4>
                    <form action="topic_handler.php" method="post" enctype="multipart/form-data">
                        <input name="function" value="upload" hidden>
                        <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                        <input name="subtopic" value="<?php echo $subtopic[0]; ?>" hidden>
                        <input type="file" name="fileToUpload">
                        <input type="submit" value="Upload File">
                    </form>
                <?php endif ?>
            </div>
        <?php 
            endforeach;
        
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) :
        ?>
            <h3>Create a new subtopic</h3>
            <form action="topic_handler.php" method="post">
                <input name="function" value="createSubtopic" hidden>
                <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                <input name="name">
                <input type="submit" value="Create">
            </form>
        <?php endif ?>
	</div>
</body>
<?php 
    endif;
endif; ?>
</html>