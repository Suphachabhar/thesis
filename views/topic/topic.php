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
	header("location: ../auth/login.php");
}
?>

<html>
<?php
    if (!isset($_SESSION['user_type'])) {
        header('location: ../auth/home.php');
    }
?>
<?php
    $topic = existingTopicID($_GET['id'], $db);
    if (is_null($topic)) {
        $_SESSION['success'] = invalidInputError("topic ID");
        header('location: '.mainPage());
    } else {
        $query = "SELECT id, name, sort FROM subtopics where topic = ".$_GET['id']." ORDER BY sort";
        $results = mysqli_query($db, $query);
        $nSubtopics = mysqli_num_rows($results);
        $sList = mysqli_fetch_all($results, MYSQLI_ASSOC);
            
        $prerequisite = array();
        if (!isAdmin() && count($topic['prerequisite']) > 0) {
            foreach ($topic['prerequisite'] as $p) {
                $query = "SELECT count(a.id) AS subtopics, b.progress FROM subtopics AS a, progresses AS b WHERE b.student = ".$_SESSION['user']['id']
                    ." AND b.topic = ".$p['id']." AND b.topic = a.topic";
                $results = mysqli_query($db, $query);
                $progress = mysqli_fetch_assoc($results);
                if (is_null($progress) || $progress['subtopics'] != $progress['progress']) {
                    $prerequisite[] = '<li><a href="../topic/topic.php?id='.$p['id'].'">'.$p['name'].'</a></li>';
                }
            }
        }
        
        if (count($prerequisite) > 0) {
            $_SESSION['success'] = 'You have to finish the prerequisite before studying this topic:
            <ul>'.join('', $prerequisite).'</ul>';
            header('location: '.mainPage());
        } else {
?>
<head>
    <title id="title"><?php echo $topic['name']; ?></title>
    <link rel="stylesheet" type="text/css" href="modal.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
    
    <!-- ajax -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    
    <link href="../../node_modules/slim-select/dist/slimselect.css" rel="stylesheet">
    <script src="../../node_modules/slim-select/dist/slimselect.js"></script>
    
    <script>
        $( function() {
            $( "#sortable" ).sortable();
            $( "#sortable" ).disableSelection();
        } );
    </script>
</head>
    
<body>
    
<div class="containner">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">   
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../auth/home.php?topic=<?php echo $_GET['id']; ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $topic['name']; ?></li>
            <?php 
                if (permission()) {
            ?>
                <!-- create sub topic -->
                <a class="btn btn-light" id="nav-link-create" data-toggle="modal" data-target="#courseAddModal" data-whatever="@mdo">+ Create</a>
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
                <!--<button class="plus-button-topic" data-toggle="modal" data-target="#courseAddModal" data-whatever="@mdo"></button> -->
                </form>

                <!-- rename topic / edit description / rearrange subtopic -->
                <a class="btn btn-light" id="nav-link-setting" data-toggle="modal" data-target="#modifyModal" data-whatever="@mdo">Setting</a>
                <form action="topic_handler.php" method="post">
                <div class="modal fade" id="modifyModal" tabindex="-1" role="dialog" aria-labelledby="modifyModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modifyModalLabel">Topic setting</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <div class="form-group">
                                        <label class="col-form-label">Name:</label>
                                        <input name="function" value="editTopic" hidden>
                                        <input name="id" value="<?php echo $_GET['id']; ?>" hidden>
                                        <input name="name" value="<?php echo $topic['name']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="message-text" class="col-form-label">Description:</label>
                                        <textarea class="form-control" id="message-text" name="description" rows="4" width="50"><?php echo $topic['description']; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="message-text" class="col-form-label">Category:</label>
                                        <select id="category" name="category[]" multiple>
                                            <?php
                                                $cat_ids = array_map(function($cat) {
                                                    return $cat['id'];
                                                }, $topic['category']);
                                            
                                                $query = "SELECT id, name FROM categories ORDER BY name";
                                                $results = mysqli_query($db, $query);
                                                foreach (mysqli_fetch_all($results, MYSQLI_ASSOC) as $row) {
                                            ?>
                                            <option value="<?php echo $row["id"]; ?>"<?php if (in_array($row["id"], $cat_ids)) echo "selected"; ?>>
                                                <?php echo $row["name"]; ?>
                                            </option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="message-text" class="col-form-label">Prerequisite:</label>
                                        <select id="prerequisite" name="prerequisite[]" multiple>
                                            <?php
                                                $prereq_ids = array_map(function($prereq) {
                                                    return $prereq['id'];
                                                }, $topic['prerequisite']);
                                                
                                                $query = "SELECT id, name FROM topics ORDER BY name";
                                                $results = mysqli_query($db, $query);
                                                foreach (mysqli_fetch_all($results, MYSQLI_ASSOC) as $row) {
                                            ?>
                                            <option value="<?php echo $row["id"]; ?>"<?php if (in_array($row["id"], $prereq_ids)) echo "selected"; ?>><?php echo $row["name"]; ?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="message-text" class="col-form-label">Subtopic order (drag to reorder)</label>
                                        <ul id="sortable">
                                        <?php
                                            foreach ($sList as $subtopic) {
                                        ?>
                                            <li>
                                                <?php echo $subtopic['name']; ?>
                                                <input name="subtopic[]" value="<?php echo $subtopic['id']; ?>" hidden>
                                            </li>
                                        <?php 
                                            }
                                        ?>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" value="Yes">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
                </form>

                <!-- delete topic -->
                <a class="btn btn-light" id="nav-link-delete" data-toggle="modal" data-target="#deleteModal" data-whatever="@mdo">Delete</a>
                <form action="topic_handler.php" method="post">
                <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Delete <?php echo $topic['name']; ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php if (count($topic['prerequisite']) > 0 && count($topic['after']) > 0) { ?>
                            <div class="modal-body">
                                <p>The topic you are going to delete is a prerequisite of some other topics. Please tick the box if you want to link these topics to the prerequisites of this topic.</p>
                                <form>
                                    <div class="form-group">
                                        <input name="function" value="deleteTopic" hidden>
                                        <input name="id" value="<?php echo $_GET['id']; ?>" hidden>
                                        <?php
                                            foreach ($topic['prerequisite'] as $p) {
                                                foreach ($topic['after'] as $a) {
                                                    echo '<input type="checkbox" name="connection[]" value="'.$a['id'].', '.$p['id']
                                                        .' checked"><a href="topic.php?id='.$p['id'].'" target="_blank">'.$p['name']
                                                        .'</a> -> <a href="topic.php?id='.$a['id'].'" target="_blank">'.$a['name'].'</a><br/>';
                                                }
                                            }
                                        ?>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" value="Yes">Delete this topic</button>
                            </div>
                            <?php } else { ?>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this topic?</p>
                                <form>
                                    <div class="form-group" hidden>
                                        <input name="function" value="deleteTopic">
                                        <input name="id" value="<?php echo $_GET['id']; ?>">
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                <button type="submit" class="btn btn-primary" value="Yes">Yes</button>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                </form>

            <?php } ?>
            
        </ol>   
    </nav>

    <a class="btn" id="nav-link" href="../auth/login.php?logout='1'"><img src="../auth/img/leave.png"></a>
    <?php echo display_error(); ?>
    <?php if (isset($_SESSION['success'])) : ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
  	<?php endif ?>
    </div>

  
      
    <div id="name" class="header">
    
        <h1 id="topichead"><?php echo $topic['name']; ?></h1>
        
        
        <div class="subcontent">
        <div class="description">
            <h2>Description</h2>
            <?php
                foreach (explode("\n", $topic["description"]) as $line) {
                    echo "<p>".$line."</p>";
                }
            ?>
        </div>
        <hr />
        <!--<hr style="height:10px;border:none;color:#f5c852;background-color:#f5c852;" />-->
        <div class="descAndNav">
            
            <?php
                $defaultSub = 1;
                $progress = $nSubtopics;
                if (isAdmin()) {
                    $defaultSub = isset($_GET['subtopic']) ? intval($_GET['subtopic']) : 1;
                } else {
                    $query = "SELECT progress FROM progresses where topic = ".$_GET['id']." AND student = ".$_SESSION['user']['id'];
                    $results = mysqli_query($db, $query);
                    if (mysqli_num_rows($results) == 0) {
                        $createProgress = "INSERT INTO progresses (student, topic, progress) VALUES (".$_SESSION['user']['id'].", ".$_GET['id'].", 0)";
                        mysqli_query($db, $createProgress);
                        $progress = 0;
                    } else {
                        $progress = mysqli_fetch_assoc($results)['progress'];
                    }
                    $defaultSub = $progress == $nSubtopics ? $progress : $progress + 1;
                    if (isset($_GET['subtopic']) && intval($_GET['subtopic']) < $defaultSub) {
                        $defaultSub = intval($_GET['subtopic']);
                    }
                }
                
                if (isAdmin()) {
            ?>
            <form class="nav-link-export" method='post' action='topic_handler.php'>
                <input name="function" value="exportTopic" hidden>
                <input name="id" value="<?php echo $_GET['id']; ?>" hidden>
                <input class="afterContent btn btn-light" type="submit" value="Export">
            </form>
            <?php
                } else {
                    foreach ($sList as $subtopic) {
                        if ($subtopic['sort'] > ($progress + 1)) continue;
            ?>
            <div class="navigation" 
                id="navigation_<?php echo $subtopic['sort']; ?>"<?php if ($defaultSub != $subtopic['sort']) echo ' style="display: none;"'; ?>>
                <?php
                        if ($subtopic['sort'] == $nSubtopics && $subtopic['sort'] == $progress) {
                            echo '<a class="afterContent btn btn-primary" href="../auth/home.php?topic='.$_GET['id'].'">Finish</a>';
                        } elseif ($subtopic['sort'] <= $progress && $subtopic['sort'] != $nSubtopics) {
                            echo '<button class="afterContent nextSubtopic btn btn-primary" id="nextSubtopic_'.$subtopic['sort'].'">Next</button>';
                        } else {
                            $button = '<button class="afterContent progressCheck btn btn-primary" id="progress_'.$subtopic['sort'].'">';
                            if ($subtopic['sort'] == $nSubtopics) {
                                $button .= "Finish";
                            } else {
                                $button .= "Next";
                            }
                            echo $button.'</button>';
                        }
                ?>
            </div>
            <?php 
                    }
                }
            ?>
        </div>
        
        <h2>Subtopics</h2>
            <div class="subtopicrow">

                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                <?php
                    foreach ($sList as $subtopic) {
                ?>
                    <li class="nav-item">
                    <div class="subtopicSlot<?php if ($defaultSub == $subtopic['sort']) {echo " selected";} ?> nav-link"
                        id="subtopicSlot_<?php echo $subtopic['sort']; ?>">
                        <button class="subtopicName" id="subtopicName_<?php echo $subtopic['sort']; ?>"<?php if (!isAdmin() && $subtopic['sort'] > ($progress + 1)) echo ' disabled'; ?>><?php echo $subtopic['name']; ?></button>
                        
                    </div>
                <?php
                    }
                ?>
                </ul>
                 
            </div>
           
            <?php
            if (!permission()) {
                $query = "SELECT b.progress, COUNT(c.topic) AS nSub FROM topics AS a LEFT JOIN progresses AS b ON a.id = b.topic"
                    ." LEFT JOIN subtopics AS c ON a.id = c.topic WHERE b.student = ".$_SESSION["user"]["id"]." AND a.id = ".$_GET["id"];
                $result = mysqli_query($db, $query);
                $row = mysqli_fetch_assoc($result);
                $percentage = $row['nSub'] == 0 ? 0 : round(($row['progress'] / $row['nSub']) * 100);
                print '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="'
                    .$percentage.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%"></div></div>';
            }
            ?>
            

            <div class="topiccontent">
                <?php
                    foreach ($sList as $subtopic) {
                        if (!isAdmin() && $subtopic['sort'] > ($progress + 1)) continue;
                ?>
                    <div class="subtopicContent" 
                        id="subtopicContent_<?php echo $subtopic['sort']; ?>"<?php if ($defaultSub != $subtopic['sort']) echo ' style="display: none;"'; ?>>
                        <?php
                            $directory = '../../files/'.$_GET['id'].'/'.$subtopic['id'];
                            $has_files = false;
                            if (is_dir($directory)) {
                                $files = scandir($directory);
                                if ($files !== false) {
                                    foreach ($files as $f) {
                                        if ($f == '.' || $f == '..') {continue;}
                                        $has_files = true;
                        ?>
                        
                        <iframe src="<?php echo $directory.'/'.$f; ?>" style="height:680px; width:100%"></iframe>
                        <?php
                                    }
                                }
                            }
                            
                            if (!$has_files && permission()) {
                        ?>
                       
                        <h4>Upload content</h4>
                        <form action="topic_handler.php" method="post" enctype="multipart/form-data">
                            <input name="function" value="upload" hidden>
                            <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                            <input name="subtopic" value="<?php echo $subtopic['id']; ?>" hidden>
                            <input type="file" name="fileToUpload">
                            <input type="submit" value="Upload File">
                        </form>
                        <?php 
                            }
                        ?>

                        <?php
                            if (isAdmin()) {
                        ?>
                       
                        <div  class="top-bar-right" <?php echo $subtopic['id']; ?>>
                            <button class="modify-button-topic" data-toggle="modal" data-target="#renameSubModal_<?php echo $subtopic['id']; ?>" data-whatever="@mdo"></button>
                            <button class="delete-button-topic" data-toggle="modal" data-target="#deleteSubModal_<?php echo $subtopic['id']; ?>" data-whatever="@mdo"></button>
                        </div>

                        <form action="topic_handler.php" method="post">
                    <div class="modal fade" id="renameSubModal_<?php echo $subtopic['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="renameSubModalLabel_<?php echo $subtopic['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="renameSubModalLabel_<?php echo $subtopic['id']; ?>">Subtopic setting</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                <div class="form-group">
                                    <label class="col-form-label">Name:</label>
                                    <input name="function" value="editSubtopicName" hidden>
                                    <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                                    <input name="id" value="<?php echo $subtopic['id']; ?>" hidden>
                                    <input name="name" value="<?php echo $subtopic['name']; ?>">
                                </div>
                                <div class="form-group">
                                    <p><b>Upload new file</b></p>
                                    <input name="function" value="upload" hidden>
                                    <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                                    <input name="subtopic" value="<?php echo $subtopic['id']; ?>" hidden>
                                    <input type="file" name="fileToUpload">
                                    <input type="submit" value="Upload File">
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
                </form>

                <form action="topic_handler.php" method="post">
                    <div class="modal fade" id="deleteSubModal_<?php echo $subtopic['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteSubModalLabel_<?php echo $subtopic['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteSubModalLabel_<?php echo $subtopic['id']; ?>">Delete <?php echo $subtopic['name']; ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this subtopic?</p>
                                    <form>
                                        <div class="form-group" hidden>
                                            <input name="function" value="deleteSubtopic">
                                            <input name="topic" value="<?php echo $_GET['id']; ?>">
                                            <input name="id" value="<?php echo $subtopic['id']; ?>">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                    <button type="submit" class="btn btn-primary" value="Yes">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                        
                        <?php 
                                }
                        ?>

                    </div>

                    
                <?php 
                    }
                ?>
            </div>
        </div>
    </div>


        
   

	
<script>
    <?php
        if (isAdmin()) {
    ?>
    $(document).on("click", ".deleteSubtopic", (function () {
		var subID = $(this).attr("id").split("_")[1];
        $("#subtopicNameToDelete").html($("#subtopicName_"+subID).html());
        $("#subtopicIDToDelete").val(subID);
        $("#confirmDeleteSubtopic").css("display", "block");
	}));

    $(document).ready(function () {
        var instance = new SlimSelect({
            select: '#prerequisite'
        });
        var instance2 = new SlimSelect({
            select: '#category'
        });
    });
    <?php
        } else {
    ?>
	$(document).on("click", ".progressCheck", (function () {
		var progressID = $(this).attr("id").split("_")[1];
        $.ajax({
            url: "topic_handler.php",
            method: "post",
            data: "function=recordProgress&topic=<?php echo $_GET['id']; ?>&progress=" + progressID,
            success: function(result){
                window.location = result;
            }
        });
	}));
    
	$(document).on("click", ".nextSubtopic", (function () {
		var currID = $(this).attr("id").split("_")[1];
		var nextID = parseInt(currID, 10) + 1;
        console.log(currID + " " + nextID);
        $("#subtopicSlot_" + currID).removeClass("selected");
        $("#subtopicSlot_" + nextID).addClass("selected");
        $("#subtopicContent_" + currID).css("display", "none");
        $("#subtopicContent_" + nextID).css("display", "block");
        $("#navigation_" + currID).css("display", "none");
        $("#navigation_" + nextID).css("display", "block");
	}));
    <?php
        }
    ?>
    
	$(document).on("click", ".subtopicName", (function () {
		var subID = $(this).attr("id").split("_")[1];
        $(".subtopicSlot").each(function() {
            if ($(this).attr("id") == "subtopicSlot_" + subID) {
                $(this).addClass("selected");
            } else {
                $(this).removeClass("selected");
            }
        });
        $(".subtopicContent").each(function() {
            if ($(this).attr("id") == "subtopicContent_" + subID) {
                $(this).css("display", "block");
            } else {
                $(this).css("display", "none");
            }
        });
        $(".navigation").each(function() {
            if ($(this).attr("id") == "navigation_" + subID) {
                $(this).css("display", "block");
            } else {
                $(this).css("display", "none");
            }
        });
	}));

    

    
</script>

</body>
<?php 
        }
    }
?>

</html>