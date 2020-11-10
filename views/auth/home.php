<?php 
include('server.php');

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: auth/login.php");
}
?>

<!DOCTYPE html>
<html>
<?php
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
        header('location: index.php');
    }
?>
<head>
<meta charset="UTF-8">

	<title>Course</title>
	<link href="home.css" rel="stylesheet">
	
	
</head>
<body>
	<nav>
      <div id="logo-img">
          <a href="home.php">
              <img src="img/unsw_0.png" href="home.php">
		  </a>
      </div>
      
  	</nav>

	<div class="header">
		
	</div>
	<div class="content">
		<!-- notification message -->
		<?php if (isset($_SESSION['success'])) : ?>
			<div class="error success" >
				<h3>
					<?php 
						echo $_SESSION['success']; 
						unset($_SESSION['success']);
					?>
				</h3>
			</div>
		<?php endif ?>
		<!-- logged in user information -->
		<div class="profile_info">
			

			<div>
				<?php  if (isset($_SESSION['user'])) : ?>
					<strong><?php echo $_SESSION['user']['username']; ?></strong>

					<small>
						<i  style="color: #888;">(<?php echo ucfirst($_SESSION['user']['user_type']); ?>)</i> 
						<br>
						<a href="index.php?logout='1'" style="color: red;">logout</a>
					</small>

				<?php endif ?>
			</div>
		</div>
        <div>
            <h2>Create a topic</h2>
            <form action="../../topic_handler.php" method="post">
                <input name="function" value="createTopic" hidden>
                <input name="name" value="">
                <input type="submit" value="Create">
            </form>
        </div>
	</div>
</body>
</html>