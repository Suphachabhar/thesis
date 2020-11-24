

<?php 
include('server.php');
include('../../database.php');

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: auth/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
        header('location: index.php');
    }
    
    function createUL($rows, $db) {
        $output = '<ul>';
        foreach ($rows as $row) {
            $output .= '<li><div><span><a href="../topic/topic.php?id='.$row['id'].'" target="_blank">'.$row['name'].'</a></span></div>';
            $query = "SELECT id, name, description FROM topics where prerequisite = ".$row['id'];
            $results = mysqli_query($db, $query);
            if (mysqli_num_rows($results) > 0) {
                $output .= createUL(mysqli_fetch_all($results, MYSQLI_ASSOC), $db);
            }
            $output .= '</li>';
        }
        $output .= '</ul>';
        return $output;
    }
    
    $query = "SELECT id, name, description FROM topics where prerequisite IS NULL";
    $results = mysqli_query($db, $query);
    $mindmap = createUL(mysqli_fetch_all($results, MYSQLI_ASSOC), $db);
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Course</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="home.css" rel="stylesheet">
    
    <!--for mind map-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://code.jquery.com/ui/jquery-ui-git.css" type="text/css" rel="stylesheet"/>
    <script src="https://code.jquery.com/ui/jquery-ui-git.js" type="text/javascript"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"> </script>
    <link href="../../buzzmap/styles.css" type="text/css" rel="stylesheet"/>
    <script src="../../buzzmap/buzzmap.min.js" type="text/javascript"></script>
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-white">
	<div id="logo-img">
		<a href="home.php">
			<img src="img/unsw_0.png" href="home.php">
		</a>
	</div>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="course.php">Course</a>
			</li>
			<li class="nav-item">
				<a class="nav-link disabled" href="#"></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="login.php?logout='1'">Logout</a>	
			</li>
		</ul>
	</div>
	</nav>

	<div class="alert alert-warning alert-dismissible fade show" role="alert">
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
	</div>
    
    <div id="container" class="mindmap-placeholder"></div>
    
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>

</html>

<script>
    $(document).ready(function() {
        $('#container').buzzmap({
            structure: '<?php echo $mindmap; ?>'
        });
    });

	$('.alert').alert();
	$('#exampleModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var recipient = button.data('whatever') // Extract info from data-* attributes
		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		var modal = $(this)
		modal.find('.modal-title').text('New message to ' + recipient)
		modal.find('.modal-body input').val(recipient)
	});
</script>

