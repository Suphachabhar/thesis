

<?php 
include('server.php');

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
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Course</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="home.css" rel="stylesheet">
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
				<a class="nav-link" href="#">Course<span class="sr-only">(current)</span></a>
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

    <div class="content">
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
	</div>

	<div class="container">
        
	<form action="../topic/topic_handler.php" method="post">
		<div class="modal fade" id="courseAddModal" tabindex="-1" role="dialog" aria-labelledby="courseAddModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="courseAddModalLabel">Create new topic</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
					<div class="form-group">
						<label class="col-form-label">Topic:</label>
						<input name="function" value="createTopic" hidden>
						<input name="name" type="text" class="form-control">
					</div>
					<div class="form-group">
						<label for="message-text" class="col-form-label">Description:</label>
						<textarea class="form-control" id="message-text"></textarea>
					</div>
					<div class="form-group">
						<label for="message-text" class="col-form-label">Prerequisite:</label>
						<input id="searchPrerequisite" list="prerequisite">
                        <datalist id="prerequisite" name="prerequisite">
                            <option value="Internet Explorer">
                            <option value="Firefox">
                            <option value="Chrome">
                            <option value="Opera">
                            <option value="Safari">
                        </datalist>  
					</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary" value="Create">Submit</button>
				</div>
				</div>
			</div>
		</div>
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#courseAddModal" data-whatever="@mdo"> + Create </button>
	</form>

	<h4>Learning map</h4>

	<div class="card-body">
		<?php
			$connection = mysqli_connect("localhost", "root", "");
			$db = mysqli_select_db($connection, 'thesis');

			$query = "SELECT * FROM topics";
			$query_run = mysqli_query($connection, $query);
		?>
		<table class="table table-hover">
		<thead>
			<tr>
			<th scope="col" colspan="5">Course</th>
			</tr>
		</thead>

		<?php
			if($query_run){
				foreach($query_run as $row){ 
		?>
		<tbody>
			<tr>
			<th scope="row"><?php echo $row['name']; ?></th>
			</tr>
		</tbody>
		<?php
				}
			}else{
				echo "NO course found";
			}	
		?>
		</table>
	</div>		
	</div>

	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>



</html>

<script>
$('#courseAddModal').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget) // Button that triggered the modal
	var recipient = button.data('whatever') // Extract info from data-* attributes
	// If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
	// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
	var modal = $(this)
	modal.find('.modal-title').text('Create new topic')
})
</script>

