<?php
    session_start();
    require_once("../../database.php");
    require_once("../../errors.php");
    require_once("../../checks.php");
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
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
	<div id="name" class="header">
		<h2><?php echo $topic['name']; ?></h2>
        <?php 
            $query = "SELECT id, name FROM subtopics where topic = ".$_GET['id']." ORDER BY sort";
            $results = mysqli_query($db, $query);
            $nSubtopics = mysqli_num_rows($results);
            
            if (permission()) : 
        ?>
            <button class="editTopic">Edit</button>
            <button class="deleteTopic">Delete</button>
            <?php if ($nSubtopics > 1) : ?>
            <button class="rearrange">Rearrange Subtopics</button>
        <?php 
                endif;
            endif;
        ?>
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
        
            $sList = mysqli_fetch_all($results);
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
		
	$(document).on("click", ".rearrange", (function () {
        $("#rearrangeSubtopics").css("display", "block");
	}));
    
	$(document).on("click", ".no", (function () {
        $(".modal").css("display", "none");
	}));
    
	$(document).on("click", ".close", (function () {
        $(".modal").css("display", "none");
	}));
    
	$(document).on("click", ".confirmRearrange", (function () {
        var dataItem = $("#sortable").sortable("serialize");
        
        $.ajax({
            url: "topic_handler.php",
            method: "post",
            data: "function=rearrangeSubtopics&topic=<?php echo $_GET['id']; ?>&" + dataItem,
            success: function(result) {
                window.location = "topic.php?id=<?php echo $_GET['id']; ?>";
            }
        });
	}));
</script>
</body>
<?php endif; ?>

</html>