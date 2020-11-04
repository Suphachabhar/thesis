<?php
    session_start();
    require_once("database.php");
    require_once("errors.php");
    require_once("checks.php");
?>

<html>
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
    
<body>
	<div id="name" class="header">
		<h2><?php echo $topic['name']; ?></h2>
        <?php if (permission()) : ?>
            <button class="editTopic">Edit</button>
            <button class="deleteTopic">Delete</button>
        <?php endif; ?>
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
                <font id="<?php echo 'title_'.$subtopic[0]; ?>" hidden><?php echo $subtopic[1]; ?></font>
                <div id="<?php echo 'subtopicHeader_'.$subtopic[0]; ?>" class="subtopicHeader">
                    <h3 id="<?php echo 'subtopicName_'.$subtopic[0]; ?>"><?php echo $subtopic[1]; ?></h3>
                    <?php if (permission()) : ?>
                        <button id="<?php echo 'editSubtopic_'.$subtopic[0]; ?>" class="editSubtopic">Edit</button>
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
        
            if (permission()) :
        ?>
            <h3>Create a new subtopic</h3>
            <form action="topic_handler.php" method="post">
                <input name="function" value="createSubtopic" hidden>
                <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                <input name="name">
                <input type="submit" value="Create">
            </form>
        <?php endif; ?>
	</div>
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
    <?php endif; ?>
</body>
<?php endif; ?>

<script>
	$(document).on("click", ".editTopic", (function () {
		$("#name").html(' \
            <form action="topic_handler.php" method="post"> \
                <input name="function" value="editTopicName" hidden> \
                <input name="id" value='+"<?php echo $_GET['id']; ?>"+' hidden> \
                <input name="name" value='+"<?php echo $topic['name']; ?>"+'> \
                <input type="submit" value="Change"> \
            </form> \
            <button class="cancelEditTopic">Cancel</button> \
        ');
	}));
    
	$(document).on("click", ".deleteTopic", (function () {
        $("#confirmDeleteTopic").css("display", "block");
	}));
    
	$(document).on("click", ".cancelEditTopic", (function () {
		$("#name").html(' \
            <h2>'+ $("#title").html() +'</h2> \
            <button class="editTopic">Edit</button> \
        ');
	}));
		
	$(document).on("click", ".editSubtopic", (function () {
		var subID = $(this).attr("id").split("_")[1];
		$("#subtopicHeader_"+subID).html(' \
            <form action="topic_handler.php" method="post"> \
                <input name="function" value="editSubtopicName" hidden> \
                <input name="topic" value='+"<?php echo $_GET['id']; ?>"+' hidden> \
                <input name="id" value='+ subID +' hidden> \
                <input name="name" value='+ $("#subtopicName_"+subID).html() +'> \
                <input type="submit" value="Change"> \
            </form> \
            <button id="cancel_'+ subID +'" class="cancelEditSubtopic">Cancel</button> \
        ');
	}));
    
	$(document).on("click", ".deleteSubtopic", (function () {
		var subID = $(this).attr("id").split("_")[1];
        $("#subtopicNameToDelete").html($("#subtopicName_"+subID).html());
        $("#subtopicIDToDelete").val(subID);
        $("#confirmDeleteSubtopic").css("display", "block");
	}));
    
	$(document).on("click", ".cancelEditSubtopic", (function () {
		var subID = $(this).attr("id").split("_")[1];
		$("#subtopicHeader_"+subID).html(' \
            <h3 id="subtopicName_'+ subID +'">'+ $("#title_"+subID).html() +'</h3> \
            <button id="editSubtopic_'+ subID +'" class="editSubtopic">Edit</button> \
        ');
	}));
    
	$(document).on("click", ".no", (function () {
        $(".modal").css("display", "none");
	}));
</script>
</html>