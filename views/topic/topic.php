<?php
    require_once("../auth/server.php");
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
        $query = "SELECT id, name, sort, video, link FROM subtopics where topic = ".$_GET['id']." ORDER BY sort";
        $results = mysqli_query($db, $query);
        $nSubtopics = mysqli_num_rows($results);
        $sList = mysqli_fetch_all($results, MYSQLI_ASSOC);
            
        $prerequisite = array();
        if (!isAdmin() && count($topic['prerequisite']) > 0) {
            foreach ($topic['prerequisite'] as $p) {
                $query = "SELECT COUNT(id) AS nSub FROM subtopics WHERE topic = ".$p["id"];
                $result = mysqli_query($db, $query);
                $subTotal = mysqli_fetch_assoc($result)['nSub'];
                
                $query = "SELECT COUNT(a.subtopic) AS nSub FROM progresses AS a, subtopics AS b WHERE a.student = ".$_SESSION["user"]["id"]." AND a.subtopic = b.id AND b.topic = ".$p["id"];
                $result = mysqli_query($db, $query);
                $subFinished = mysqli_fetch_assoc($result)['nSub'];
                
                if ($subFinished < $subTotal) {
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    
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
                if (isAdmin()) {
            ?>
            <form class="nav-link-export" method='post' action='topic_handler.php'>
                <input name="function" value="exportTopic" hidden>
                <input name="id" value="<?php echo $_GET['id']; ?>" hidden>
                <input class="afterContent btn btn-light" type="submit" value="Export">
            </form>
            <?php
                }
            ?>
        </div>
        
        <h2>Subtopics</h2>
            <div class="subtopicrow">

                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                <?php
                    $defaultSub = 0;
                    if (isset($_GET['subtopic'])) {
                        $defaultSub = intval($_GET['subtopic']);
                        if (!isAdmin()) {
                            $query = "INSERT INTO progresses (student, subtopic) VALUES (".$_SESSION['user']['id'].", ".$_GET['subtopic'].")";
                            mysqli_query($db, $query);
                        }
                    }
                    
                    $subFinished = 0;
                    $progresses = array();
                    if (!permission() && $nSubtopics != 0) {
                        $query = "SELECT a.subtopic FROM progresses AS a, subtopics AS b WHERE a.student = ".$_SESSION["user"]["id"]." AND a.subtopic = b.id AND b.topic = ".$_GET["id"];
                        $results = mysqli_query($db, $query);
                        $subFinished = mysqli_num_rows($results);
                        
                        foreach (mysqli_fetch_all($results, MYSQLI_ASSOC) as $row) {
                            $progresses[] = $row['subtopic'];
                        }
                    }
                    
                    foreach ($sList as $subtopic) {
                ?>
                    <li class="nav-item">
                    <div class="subtopicSlot<?php if ($defaultSub == $subtopic['id']) {echo " selected";} ?> nav-link"
                        id="subtopicSlot_<?php echo $subtopic['id']; ?>">
                        <button class="subtopicName" id="subtopicName_<?php echo $subtopic['id']; ?>">
                            <?php
                                echo $subtopic['name'];
                                
                                if (!isAdmin()) {
                                    $finished = in_array($subtopic['id'], $progresses);
                                    $title = $finished ? 'completed' : 'not completed';
                                    $src = $finished ? 'tick' : 'dashed_circle';
                                    echo '<img id="status_'.$subtopic['id'].'" data-toggle="tooltip" title="'.$title.'" src="../auth/img/'.$src.'.png">';
                                }
                            ?>
                        </button>
                        
                    </div>
                <?php
                    }
                ?>
                </ul>
                 
            </div>
            
            <?php
            if (!permission()) {
                $percentage = $nSubtopics == 0 ? 0 : ($subFinished / $nSubtopics) * 100;
                print '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="'
                    .$percentage.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%"></div></div>';
            }
            ?>
            

            <div class="topiccontent">
                <?php
                    foreach ($sList as $subtopic) {
                ?>
                    <div class="subtopicContent" 
                        id="subtopicContent_<?php echo $subtopic['id']; ?>"<?php if ($defaultSub != $subtopic['id']) echo ' style="display: none;"'; ?>>
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
                        ?>
                        <?php
                            $has_video = false;
                            if ($subtopic['video']) {
                                $has_video = true;
                        ?>
                        <div class="externalcontent">
                            <div class="videoContainer">
                                <h5>External video</h5>
                                <iframe src="http://www.youtube.com/embed/<?php echo $subtopic['video']; ?>"allowfullscreen ></iframe>
                            </div>
                                <?php
                            }
                            
                            $has_link = false;
                            if ($subtopic['link']) {
                                $has_link = true;
                            ?>
                        
                            <div class="linkContainer">
                            <h5>External link <span><a href="<?php echo $subtopic['link']; ?>"><img src="../auth/img/expand.png"></img></a></span></h5>
                            
                            </div>
                        </div>
                        
                        <?php
                            }
                            
                            if (isAdmin()) {
                        ?>
                       
                        <div  class="top-bar-right" <?php echo $subtopic['id']; ?>>
                            <button class="modify-button-topic" data-toggle="modal" data-target="#renameSubModal_<?php echo $subtopic['id']; ?>" data-whatever="@mdo"></button>
                            <button class="delete-button-topic" data-toggle="modal" data-target="#deleteSubModal_<?php echo $subtopic['id']; ?>" data-whatever="@mdo"></button>
                        </div>

                        <form action="topic_handler.php" method="post" enctype="multipart/form-data">
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
                                    <input name="function" value="editSubtopic" hidden>
                                    <input name="topic" value="<?php echo $_GET['id']; ?>" hidden>
                                    <input name="id" value="<?php echo $subtopic['id']; ?>" hidden>
                                    <div class="form-group">
                                        <label class="col-form-label">Name:</label>
                                        <input name="name" value="<?php echo $subtopic['name']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <p><b><?php echo $has_files ? "Replace/Delete" : "Add";?> PDF file</b></p>
                                        <?php if ($has_files) { ?>
                                        <input type="radio" name="fileRemove" value="0" checked><label>Replace</label>
                                        <?php } ?>
                                        <input type="file" name="fileToUpload">
                                        <?php if ($has_files) { ?>
                                        <br><input type="radio" name="fileRemove" value="1"><label>Delete</label>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group">
                                        <p><b><?php echo $has_video ? "Replace/Delete" : "Add";?> YouTube video</b></p>
                                        <?php if ($has_video) { ?>
                                        <input type="radio" name="videoRemove" value="0" checked><label>Replace</label>
                                        <?php } ?>
                                        <input name="video">
                                        <?php if ($has_video) { ?>
                                        <br><input type="radio" name="videoRemove" value="1"><label>Delete</label>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group">
                                        <p><b><?php echo $has_link ? "Replace/Delete" : "Add";?> external link</b></p>
                                        <?php if ($has_link) { ?>
                                        <input type="radio" name="linkRemove" value="0" checked><label>Replace</label>
                                        <?php } ?>
                                        <input name="link">
                                        <?php if ($has_link) { ?>
                                        <br><input type="radio" name="linkRemove" value="1"><label>Delete</label>
                                        <?php } ?>
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
    
    $('input:radio[name="fileRemove"]').change(function () {
        $('input[name="fileToUpload"]').prop('disabled', $(this).val() == "1");
    });
    
    $('input:radio[name="videoRemove"]').change(function () {
        $('input[name="video"]').prop('disabled', $(this).val() == "1");
    });
    
    $('input:radio[name="linkRemove"]').change(function () {
        $('input[name="link"]').prop('disabled', $(this).val() == "1");
    });
    <?php
        } else {
    ?>
        var nSubtopics = <?php echo $nSubtopics; ?>,
            progresses = [<?php echo join(", ", $progresses);?>];
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
                $(this).css("display", "flex");
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
        
        <?php if (!isAdmin()) { ?>
        $.ajax({
            url: "topic_handler.php",
            method: "post",
            data: "function=recordProgress&topic=<?php echo $_GET['id']; ?>&progress=" + subID,
            success: function(){
                var subInt = parseInt(subID);
                if ($.inArray(subInt, progresses) < 0) {
                    progresses.push(subInt);
                    var percentage = (progresses.length / nSubtopics) * 100;
                    $('.progress-bar').attr('aria-valuenow', percentage);
                    $('.progress-bar').css('width', '' + percentage + '%');
                    $('#status_' + subID).attr('title', 'completed');
                    $('#status_' + subID).attr('src', '../auth/img/tick.png');
                }
            }
        });
        <?php } ?>
	}));

    

    
</script>

</body>
<?php 
        }
    }
?>

</html>
