<?php
    session_start();
    require_once("database.php");
    require_once("errors.php");
    require_once("checks.php");
    switch ($_POST["function"]) {
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
    
        case "upload":
            uploadTopicContent($db);
            break;
    
        default:
            break;
    }
    
    function createTopic($db) {
        if (!permission()) {
            $_SESSION['success'] = permissionError("create topics");
            header('location: views/auth/index.php');
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
            header('location: views/auth/home.php');
        } elseif (existingTopicName($_POST['name'], $db)) {
            $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
            header('location: views/auth/home.php');
        } else {
            $query = "SELECT COALESCE(MAX(id), 0) as id FROM topics";
            $results = mysqli_query($db, $query);
            $id = mysqli_fetch_assoc($results)['id'] + 1;
            $query = "INSERT INTO topics (id, name) VALUES (".$id.", '".$_POST['name']."')";
            mysqli_query($db, $query);
            $_SESSION['success'] = "Topic \"".$_POST['name']."\" has been created successfully.";
            header('location: topic.php?id='.$id);
        }
    }
    
    function createSubtopic($db) {
        if (!existingTopicID($_POST['topic'], $db)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
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
    }
    
    function editTopicName($db) {
        $topic = existingTopicID($_POST['id'], $db);
        if (is_null($topic)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
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
    }
    
    function editSubtopicName($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
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
    }
    
    function deleteTopic($db) {
        $topic = existingTopicID($_POST['id'], $db);
        if (is_null($topic)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
            if (!permission()) {
                $_SESSION['success'] = permissionError("delete topics");
                header('location: topic.php?id='.$_POST['id']);
            } else {
                $query = "SELECT id FROM subtopics WHERE topic = ".$_POST['id'];
                $subtopics = mysqli_query($db, $query);
                $query = "DELETE FROM topics WHERE id = ".$_POST['id'];
                mysqli_query($db, $query);
                removeTopicDirectory($_POST['id'], $subtopics);
                $_SESSION['success'] = "Topic \"".$topic['name']."\" has been deleted successfully.";
                header('location: '.mainPage());
            }
        }
    }
    
    function deleteSubtopic($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
            $subtopic = existingSubtopicID($_POST['subtopic'], $_POST['topic'], $db);
            if (is_null($subtopic)) {
                $_SESSION['success'] = invalidInputError("subtopic ID");
            } elseif (!permission()) {
                $_SESSION['success'] = permissionError("delete subtopics");
            } else {
                $query = "DELETE FROM subtopics WHERE id = ".$_POST['subtopic']." AND topic = ".$_POST['topic'];
                mysqli_query($db, $query);
                
                $query = "UPDATE subtopics SET sort = sort - 1 WHERE topic = ".$_POST['topic']." AND sort > ".$subtopic['sort'];
                mysqli_query($db, $query);
                
                removeSubtopicDirectory($_POST['topic'], $_POST['subtopic']);
                $_SESSION['success'] = "Subtopic \"".$subtopic['name']."\" has been deleted successfully.";
            }
            header('location: topic.php?id='.$_POST['topic']);
        }
    }
    
    function uploadTopicContent($db) {
        if (is_null(existingTopicID($_POST['topic'], $db))) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
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
                    $directory = "files/".$_POST["topic"]."/".$_POST["subtopic"];
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
    }
    
    function removeTopicDirectory($topic, $subtopics) {
        $directory = "files/".$topic."/";
        if (is_dir($directory)) {
            foreach ($subtopics as $subtopic) {
                removeSubtopicDirectory($topic, $subtopic["id"]);
            }
            rmdir($directory);
        }
    }
    
    function removeSubtopicDirectory($topic, $subtopic) {
        $directory = "files/".$topic."/".$subtopic."/";
        if (is_dir($directory)) {
            $files = glob($directory . '*', GLOB_MARK);
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
?>