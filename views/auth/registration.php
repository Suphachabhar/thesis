<?php include('server.php') ?>

<!DOCTYPE html>
<html>
<head>
	<title>Meta LMS</title>
	
	<link href="style.css" rel="stylesheet">
</head>
<body>
	<img class="wave" src="img/wave.png">
	<div class="container">
	
		<div class="login-content">
			<form method="post" action="login.php">

			<?php echo display_error(); ?>
				<img src="img/avatar.svg">
				<h2 class="title">Login Form</h2>

				<div class="input-div one">
					<div class="i">
						<i class="fas fa-user"></i>
					</div>
					<div class="div">
           		   		<h5>Username</h5>
           		   		<input type="text" class="input" name="username" value="<?php echo $username; ?>">
           		   </div>
                </div>
                
                <div class="input-div one">
           		   <div class="i"> 
           		    	<i class="fas fa-lock"></i>
           		   </div>
           		   <div class="div">
           		    	<h5>Email</h5>
           		    	<input type="email" class="input" name="email" value="<?php echo $email; ?>">
            	   </div>
				</div>

				<div class="input-div one">
           		   <div class="i"> 
           		    	<i class="fas fa-lock"></i>
           		   </div>
           		   <div class="div">
           		    	<h5>Password</h5>
           		    	<input type="password" class="input" name="password_1">
            	   </div>
				</div>
                
                <div class="input-div pass">
           		   <div class="i"> 
           		    	<i class="fas fa-lock"></i>
                    </div>
                    
           		   <div class="div">
           		    	<h5>Confirm Password</h5>
           		    	<input type="password" class="input" name="password_2">
            	   </div>
                </div>
                
				<input type="submit" class="btn" value="Register" name="regiter_btn">

				
				<a href="login.php">Sign in</a>
				

				</form>
		</div>

	</div>
	<script type="text/javascript" src="style.js"></script>
</body>
</html>