<?php
    require_once("../auth/server.php");
    require_once("../../database.php");
    require_once("../../errors.php");
    require_once("../../checks.php");

    
?>

<?php 
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: auth/login.php");
}
?>

<html>
<?php
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
        header('location: index.php');
    }
?>
<?php
    $topic = existingTopicID($_GET['id'], $db);
    if (is_null($topic)) : 
        $_SESSION['success'] = invalidInputError("topic ID");
        header('location: '.mainPage());
    else : 
?>
<head>
    <title id="title"><?php echo $topic['name']; ?></title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="modal.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <style>
        #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
        #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
        #sortable li span { position: absolute; margin-left: -1.3em; }
    </style>
    <script>
        $( function() {
            $( "#sortable" ).sortable();
            $( "#sortable" ).disableSelection();
        } );
    </script>
</head>
    
<body>
    <!-- nav bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
	<div class="logo-img">
		<a href="../auth/home.php">
			<img src="../auth/img/unsw_0.png">
		</a>
	</div>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="../auth/course.php">Course</a>
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

   
    
    <!-- showing the responding of the system -->
    <div class="container">
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
        if (permission()) :
    ?>
        <!-- create sub topic -->
        <form action="topic_handler.php" method="post">
		<div class="modal fade" id="courseAddModal" tabindex="-1" role="dialog" aria-labelledby="courseAddModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="courseAddModalLabel">Create new subtopic</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
					<div class="form-group">
						<label class="col-form-label">Subtopic:</label>
                        <input name="function" value="createSubtopic" hidden>
                        <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
						<input name="name">
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
        <?php endif; ?>

        

        <!-- showing topic name -->
        <div id="name" class="header">
		<h3>Learn: <?php echo $topic['name']; ?></h3>
        <?php 
            $query = "SELECT id, name FROM subtopics where topic = ".$_GET['id']." ORDER BY sort";
            $results = mysqli_query($db, $query);
            $nSubtopics = mysqli_num_rows($results);
            
            if (permission()) : 
        ?>
            
            <button class="deleteTopic">Delete</button>
        
        <?php 
               
            endif;
        ?>
    </div>

    <!-- showing subtopic list -->
    <body>
    <div class="row">
        <div class="col-4">
            <div class="list-group" id="list-tab" role="tablist">
                <?php
                    $sList = mysqli_fetch_all($results);
                    $first = true;
                    foreach ($sList as $subtopic) :
                ?>
                <font id="<?php echo 'title_'.$subtopic[0]; ?>" hidden><?php echo $subtopic[1]; ?></font>
                    <a class="list-group-item list-group-item-action<?php if ($first) {echo " active"; $first = false;} ?>" 
                        id="subtopicName_<?php echo $subtopic[0]; ?>" data-toggle="list" href="#list-profile_<?php echo $subtopic[0]; ?>" 
                        role="tab"><?php echo $subtopic[1]; ?></a>
                <?php 
                    endforeach; 
                ?>
            </div>
        </div>
        <div class="col-8">
            <div class="tab-content" id="nav-tabContent">
                <?php
                    $first = true;
                    foreach ($sList as $subtopic) :
                ?>
                <div class="tab-pane fade<?php if ($first) {echo " show active"; $first = false;} ?>" 
                    id="list-profile_<?php echo $subtopic[0]; ?>" role="tabpanel" aria-labelledby="subtopicName_<?php echo $subtopic[0]; ?>">
                    <?php if (permission()) : ?>
                        <h4>Upload content</h4>
                        <form action="topic_handler.php" method="post" enctype="multipart/form-data">
                            <input name="function" value="upload" hidden>
                            <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                            <input name="subtopic" value="<?php echo $subtopic[0]; ?>" hidden>
                            <input type="file" name="fileToUpload">
                            <input type="submit" value="Upload File">
                        </form>
                    <?php endif; ?>
                </div>
                <?php 
                    endforeach; 
                ?>
            </div>
        </div>
    </div>
    </body>
    
  
    <!-- showing a list of subtopic -->
	<div class="content">
		<?php
            $sList = mysqli_fetch_all($results);
            foreach ($sList as $subtopic) :
        ?>
            <div>
                <font id="<?php echo 'title_'.$subtopic[0]; ?>" hidden><?php echo $subtopic[1]; ?></font>
                <div id="<?php echo 'subtopicHeader_'.$subtopic[0]; ?>" class="subtopicHeader">
                    <h3 id="<?php echo 'subtopicName_'.$subtopic[0]; ?>"><?php echo $subtopic[1]; ?></h3>
                    <?php if (permission()) : ?>
                        
                        <button id="<?php echo 'deleteSubtopic_'.$subtopic[0]; ?>" class="deleteSubtopic">Delete</button>
                    <?php endif; ?>
                </div>
                <?php if (permission()) : ?>
                    <h4>Upload content</h4>
                    <form action="topic_handler.php" method="post" enctype="multipart/form-data">
                        <input name="function" value="upload" hidden>
                        <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                        <input name="subtopic" value="<?php echo $subtopic[0]; ?>" hidden>
                        <input type="file" name="fileToUpload">
                        <input type="submit" value="Upload File">
                    </form>
                <?php endif; ?>
            </div>
        <?php 
            endforeach; 
        ?>
            
    </div>
    
    <!-- backend for subtopic   -->
    <?php if (permission()) : ?>
        <div id="confirmDeleteTopic" class="modal">
            Are you sure you want to delete this topic?
            <form action="topic_handler.php" method="post">
                <input name="function" value="deleteTopic" hidden>
                <input name="id" value="<?php echo $_GET['id']; ?>" hidden>
                <input type="submit" value="Yes">
            </form>
            <button class="no">No</button>
        </div>
        <div id="confirmDeleteSubtopic" class="modal">
            Are you sure you want to delete the subtopic <font id="subtopicNameToDelete"></font>?
            <form action="topic_handler.php" method="post">
                <input name="function" value="deleteSubtopic" hidden>
                <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                <input name="subtopic" id="subtopicIDToDelete" hidden>
                <input type="submit" value="Yes">
            </form>
            <button class="no">No</button>
        </div>
        <div id="rearrangeSubtopics" class="modal">
            <span class="close">&times;</span>
            <ul id="sortable">
                <?php
                    foreach ($sList as $subtopic) :
                ?>
                    <li id="<?php echo 'subtopic_'.$subtopic[0]; ?>">
                        <?php echo $subtopic[1]; ?>
                    </li>
                <?php 
                    endforeach;
                ?>
            </ul>
            <button class="confirmRearrange">Save</button>
        </div>
    <?php endif; ?>



    
    </div>

	
<script>

    
	$(document).on("click", ".deleteTopic", (function () {
        $("#confirmDeleteTopic").css("display", "block");
	}));
    
	$(document).on("click", ".deleteSubtopic", (function () {
		var subID = $(this).attr("id").split("_")[1];
        $("#subtopicNameToDelete").html($("#subtopicName_"+subID).html());
        $("#subtopicIDToDelete").val(subID);
        $("#confirmDeleteSubtopic").css("display", "block");
	}));
    
	$(document).on("click", ".no", (function () {
        $(".modal").css("display", "none");
	}));
    
	$(document).on("click", ".close", (function () {
        $(".modal").css("display", "none");
	}));
    

</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

</body>
<?php endif; ?>

</html>