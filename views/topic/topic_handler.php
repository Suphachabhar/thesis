<?php
    session_start();
    require_once("../../database.php");
    require_once("../../errors.php");
    require_once("../../checks.php");
    switch ($_POST["function"]) {
        case "prerequisiteDiv":
            prerequisiteDiv($db);
            break;
            
        case "createTopic":
            createTopic($db);
            break;
            
        case "createSubtopic":
            createSubtopic($db);
            break;
            
        case "editTopicName":
            editTopicName($db);
            break;
            
        case "editSubtopicName":
            editSubtopicName($db);
            break;
            
        case "deleteTopic":
            deleteTopic($db);
            break;
            
        case "deleteSubtopic":
            deleteSubtopic($db);
            break;
            
        case "rearrangeSubtopics":
            rearrangeSubtopics($db);
            break;
    
        case "upload":
            uploadTopicContent($db);
            break;
    
        default:
            break;
    }
    
    function prerequisiteDiv($db) {
        $output = '';
        if (!empty($_POST["n"])) {
            $output = '<div class="prerequisiteSet" id="prerequisiteSet_'.$_POST["n"].'">';
            $output .= '<input class="searchPrerequisite" id="searchPrerequisite_'.$_POST["n"].'" list="selectPrerequisite_'.$_POST["n"].'">';
            $output .= '<datalist class="selectPrerequisite" id="selectPrerequisite_'.$_POST["n"].'">';
            $query = "SELECT id, name FROM topics";
            $results = mysqli_query($db, $query);
            foreach (mysqli_fetch_all($results) as $row) {
                $output .= '<option data-value="'.$row[0].'">'.$row[1].'</option>';
            }
            $output .= '</datalist>';
            $output .= '<button type="button" class="addAND" id="prerequisiteAND_'.$_POST["n"].'">+ AND</button>';
            $output .= '<button type="button" class="addOR" id="prerequisiteOR_'.$_POST["n"].'">+ OR</button>';
            $output .= '<button type="button" class="removePrerequisite" id="prerequisiteREMOVE_'.$_POST["n"].'">- remove</button>';
            $output .= '</div>';
        }
        print $output;
    }
    
    function createTopic($db) {
        if (!permission()) {
            $_SESSION['success'] = permissionError("create topics");
            header('location: ../auth/index.php');
            return;
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
            header('location: ../auth/home.php');
            return;
        } elseif (existingTopicName($_POST['name'], $db)) {
            $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
            header('location: ../auth/home.php');
            return;
        }
        
        $attr = "name";
        $val = "'".$_POST['name']."'";
        if (!empty($_POST['prerequisite'])) {
            $attr .= ", prerequisite";
            $val .= ", '".$_POST['prerequisite']."'";
        }
        $query = "INSERT INTO topics (".$attr.") VALUES (".$val.")";
        mysqli_query($db, $query); 
        $_SESSION['success'] = "Topic \"".$_POST['name']."\" has been created successfully.";
        header('location: ../auth/course.php');
    }
    
    function createSubtopic($db) {
        if (!existingTopicID($_POST['topic'], $db)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("create subtopics");
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a subtopic name");
        } elseif (existingSubtopicName($_POST['name'], $_POST['topic'], $db)) {
            $_SESSION['success'] = clashedInputError("subtopic name", $_POST['name']);
        } else {
            $query = "SELECT COALESCE(MAX(id), 0) as id, COUNT(*) as sort FROM subtopics WHERE topic = ".$_POST['topic'];
            $results = mysqli_query($db, $query);
            $newInfo = mysqli_fetch_assoc($results);
            $id = $newInfo['id'] + 1;
            $sort = $newInfo['sort'] + 1;
            $query = "INSERT INTO subtopics (topic, id, name, sort) VALUES (".$_POST['topic'].", ".$id.", '".$_POST['name']."', ".$sort.")";
            mysqli_query($db, $query);
            $_SESSION['success'] = "Subtopic \"".$_POST['name']."\" has been created successfully.";
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function editTopicName($db) {
        $topic = existingTopicID($_POST['id'], $db);
        if (is_null($topic)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("edit topic names");
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
        } elseif (strcmp($_POST['name'], $topic['name']) != 0) {
            if (existingTopicName($_POST['name'], $db)) {
                $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
            } else {
                $query = "UPDATE topics SET name = '".$_POST['name']."' WHERE id = ".$_POST['id'];
                mysqli_query($db, $query);
                $_SESSION['success'] = "Topic name has been changed to \"".$_POST['name']."\" successfully.";
            }
        }
        header('location: topic.php?id='.$_POST['id']);
    }
    
    function editSubtopicName($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        $subtopic = existingSubtopicID($_POST['id'], $_POST['topic'], $db);
        if (is_null($subtopic)) {
            $_SESSION['success'] = invalidInputError("subtopic ID");
        } elseif (!permission()) {
            $_SESSION['success'] = permissionError("edit subtopic names");
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
        } elseif (strcmp($_POST['name'], $subtopic['name']) != 0) {
            if (existingSubtopicName($_POST['name'], $_POST['topic'], $db)) {
                $_SESSION['success'] = clashedInputError('subtopic name', $_POST['name']);
            } else {
                $query = "UPDATE subtopics SET name = '".$_POST['name']."' WHERE id = ".$_POST['id']." and topic = ".$_POST['topic'];
                mysqli_query($db, $query);
                $_SESSION['success'] = "Subtopic name has been changed to \"".$_POST['name']."\" successfully.";
            }
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function deleteTopic($db) {
        $topic = existingTopicID($_POST['id'], $db);
        if (is_null($topic)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("delete topics");
            header('location: topic.php?id='.$_POST['id']);
            return;
        }
        
        $query = "SELECT id FROM subtopics WHERE topic = ".$_POST['id'];
        $subtopics = mysqli_query($db, $query);
        $query = "DELETE FROM topics WHERE id = ".$_POST['id'];
        mysqli_query($db, $query);
        removeTopicDirectory($_POST['id'], $subtopics);
        $_SESSION['success'] = "Topic \"".$topic['name']."\" has been deleted successfully.";
        header('location: '.mainPage());
    }
    
    function deleteSubtopic($db) {
        if (!isset($_POST['topic'])) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        $subtopic = existingSubtopicID($_POST['id'], $_POST['topic'], $db);
        if (is_null($subtopic)) {
            $_SESSION['success'] = invalidInputError("subtopic ID");
        } elseif (!permission()) {
            $_SESSION['success'] = permissionError("edit subtopic names");
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
        } elseif (strcmp($_POST['name'], $subtopic['name']) != 0) {
            if (existingSubtopicName($_POST['name'], $_POST['topic'], $db)) {
                $_SESSION['success'] = clashedInputError('subtopic name', $_POST['name']);
            } else {
                $query = "UPDATE subtopics SET name = '".$_POST['name']."' WHERE id = ".$_POST['id']." and topic = ".$_POST['topic'];
                mysqli_query($db, $query);
                removeSubtopicDirectory($_POST['topic'], $_POST['id']);
                $_SESSION['success'] = "Subtopic name has been changed to \"".$_POST['name']."\" successfully.";
            }
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function rearrangeSubtopics($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            print mainPage();
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("rearrange subtopics");
        } else {
            $edits = "";
            $count = 0;
            $OK = true;
            foreach ($_POST['subtopic'] as $subtopicID) {
                $subtopic = existingSubtopicID($subtopicID, $_POST['topic'], $db);
                if (is_null($subtopic)) {
                    $_SESSION['success'] = invalidInputError("subtopic ID");
                    $OK = false;
                    break;
                } else {
                    $count ++;
                    $edits .= " WHEN id = ".$subtopicID." THEN ".strval($count);
                }
            }
            
            if ($OK && $edits != "") {
                $query = "UPDATE subtopics SET sort = (CASE".$edits." END) WHERE topic = ".$_POST['topic']." AND id IN (".join(", ", $_POST['subtopic']).")";
                mysqli_query($db, $query);
                
                $_SESSION['success'] = "The subtopics have been rearranged successfully.";
            }
        }
        print 'topic.php?id='.$_POST['topic'];
    }
    
    function uploadTopicContent($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
    
        if (!permission()) {
            $_SESSION['success'] = permissionError("upload files");
        } elseif (is_null(existingSubtopicID($_POST['subtopic'], $_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("subtopic ID");
        } else {
            $chosen_file = basename($_FILES["fileToUpload"]["name"]);
            if (empty($chosen_file)) {
                $_SESSION['success'] = blankInputError("a file to upload");
            } else if (strtolower(pathinfo($chosen_file, PATHINFO_EXTENSION)) != "pdf") {
                $_SESSION['success'] = "You can upload PDF files only.";
            } else {
                $directory = "../../files/".$_POST["topic"]."/".$_POST["subtopic"];
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $directory."/".$chosen_file)) {
                    $_SESSION['success'] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded successfully.";
                } else {
                    $_SESSION['success'] = "Sorry, there was an error uploading your file.";
                }
            }
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function removeTopicDirectory($topic, $subtopics) {
        $directory = "../../files/".$topic."/";
        if (is_dir($directory)) {
            foreach ($subtopics as $subtopic) {
                removeSubtopicDirectory($topic, $subtopic["id"]);
            }
            rmdir($directory);
        }
    }
    
    function removeSubtopicDirectory($topic, $subtopic) {
        $directory = "../../files/".$topic."/".$subtopic."/";
        if (is_dir($directory)) {
            $files = glob($directory . '*', GLOB_MARK);
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
?>