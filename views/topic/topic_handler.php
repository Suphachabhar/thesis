<?php
    session_start();
    require_once("../../database.php");
    require_once("../../errors.php");
    require_once("../../checks.php");
    switch ($_POST["function"]) {
        case "createTopic":
            createTopic($db);
            break;
            
        case "createSubtopic":
            createSubtopic($db);
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
            
        case "editTopic":
            editTopic($db);
            break;
    
        case "upload":
            uploadTopicContent($db);
            break;
    
        case "recordProgress":
            recordProgress($db);
            break;
    
        case "search":
            searchTopic($db);
            break;
    
        default:
            break;
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
        if (!empty($_POST['description'])) {
            $attr .= ", description";
            $val .= ", '".$_POST['description']."'";
        }
        $query = "INSERT INTO topics (".$attr.") VALUES (".$val.")";
        mysqli_query($db, $query); 
        
        $query = "SELECT id FROM topics WHERE name = '".$_POST['name']."'";
        if (!empty($_POST['description'])) {
            $query .= " AND description = '".$_POST['description']."'";
        }
        $query .= " ORDER BY id DESC LIMIT 1";
        $results = mysqli_query($db, $query); 
        $id = mysqli_fetch_assoc($results)["id"];
        
        $_SESSION['success'] = "Topic \"".$_POST['name']."\" has been created successfully.";
        foreach ($_POST['prerequisite'] as $p) {
            $query = "INSERT INTO prerequisites (topic, prerequisite) VALUES (".$id.", ".$p.")";
            mysqli_query($db, $query);
        }
        header('location: topic.php?id='.$id);
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
        if (isset($_POST['connection']) && count($_POST['connection']) > 0) {
            $values = array();
            foreach ($_POST['connection'] AS $c) {
                $values[] = "(".str_replace(" checked", "", $c).")";
            }
            $query = "INSERT INTO prerequisites (topic, prerequisite) VALUES ".join(", ", $values);
            mysqli_query($db, $query);
        }
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
            $_SESSION['success'] = permissionError("delete subtopics");
        } else {
            $query = "DELETE FROM subtopics WHERE id = ".$_POST['id']." and topic = ".$_POST['topic'];
            mysqli_query($db, $query);
            removeSubtopicDirectory($_POST['topic'], $_POST['id']);
            $_SESSION['success'] = "Subtopic \"".$subtopic['name']."\" has been deleted successfully.";
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function editTopic($db) {
        $topic = existingTopicID($_POST['id'], $db);
        if (is_null($topic)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("edit topic settings");
        } else {
            $error = false;
            $edits = "";
            $count = 0;
            foreach ($_POST['subtopic'] as $subtopicID) {
                $subtopic = existingSubtopicID($subtopicID, $_POST['id'], $db);
                if (is_null($subtopic)) {
                    $_SESSION['success'] = invalidInputError("subtopic ID");
                    $error = true;
                    break;
                } else {
                    $count ++;
                    $edits .= " WHEN id = ".$subtopicID." THEN ".strval($count);
                }
            }
            if (!$error && $edits != "") {
                $query = "UPDATE subtopics SET sort = (CASE".$edits." END) WHERE topic = ".$_POST['id']." AND id IN (".join(", ", $_POST['subtopic']).")";
                mysqli_query($db, $query);
            }
            
            if (!empty($_POST['name']) && strcmp($_POST['name'], $topic['name']) != 0) {
                if (existingTopicName($_POST['name'], $db)) {
                    $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
                    $error = true;
                } else {
                    $query = "UPDATE topics SET name = '".$_POST['name']."' WHERE id = ".$_POST['id'];
                    mysqli_query($db, $query);
                }
            }
            
            if (strcmp($_POST['description'], $topic['description']) != 0) {
                $query = "UPDATE topics SET description = '".$_POST['description']."' WHERE id = ".$_POST['id'];
                mysqli_query($db, $query);
            }
            
            if (!$error) {
                $_SESSION['success'] = "The topic setting has been saved successfully.";
            }
        }
        header('location: topic.php?id='.$_POST['id']);
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
                $cwd = getcwd();
                $directory = "../../files/" . $_POST["topic"] . "/" . $_POST["subtopic"];
                $test = file_exists($directory);

                if (!$test) {
                    $test = mkdir($directory, 0777, true);
                }

                if (!$test) {
                    $_SESSION['success'] = 'failed to create the directory';
                } else {
                    $move_result = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $directory."/".$chosen_file);
                    $_SESSION['success'] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded successfully.";
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
    
    function recordProgress($db) {
        $url = "../auth/course.php";
        if (!empty($_POST['topic']) && !empty($_POST['progress'])) {
            $query = "UPDATE progresses SET progress = ".$_POST['progress']." WHERE student = ".$_SESSION['user']['id']." AND topic = ".$_POST['topic'];
            mysqli_query($db, $query);
            
            $query = "SELECT max(sort) AS max FROM subtopics WHERE topic = ".$_POST['topic'];
            $result = mysqli_fetch_assoc(mysqli_query($db, $query));
            if ($result['max'] != $_POST['progress']) {
                $url = "topic.php?id=".$_POST['topic'];
            }
        }
        print $url;
    }
    
    function searchTopic($db) {
        $link = "";
        $query = "SELECT id FROM topics WHERE name = '".$_POST["name"]."'";
        $result = mysqli_query($db, $query);
        if (mysqli_num_rows($result) > 0) {
            $link = "../topic/topic.php?id=".mysqli_fetch_assoc($result)["id"];
        }
        print $link;
    }
?>