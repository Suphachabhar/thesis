<?php
    session_start();
?>

<html>
<head>
    <title>Topic</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
    
<body>
	<div class="header">
		<h2>Topic</h2>
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
        
        <form action="topic_handler.php" method="post" enctype="multipart/form-data">
            <input name="function" value="upload" hidden>
            <input type="file" name="fileToUpload">
            <input type="submit" value="Upload File">
        </form>
        <p></p>
	</div>
</body>
</html>