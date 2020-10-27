<?php
    session_start();
    require_once("database.php");
    require_once("errors.php");
    switch ($_POST["function"]) {
        case "createTopic":
            createTopic($db);
            break;
            
        case "createSubtopic":
            createSubtopic($db);
            break;
    
        case "upload":
            uploadTopicContent($db);
            break;
    
        default:
            break;
    }
    
    function createTopic($db) {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
            $_SESSION['success'] = permissionError("create topics");
            header('location: views/auth/index.php');
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
            header('location: views/auth/home.php');
        } else {            
            $query = "SELECT id FROM topics where name = '".$_POST['name']."'";
            $results = mysqli_query($db, $query);
            
            if (mysqli_num_rows($results) != 0) {
                $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
                header('location: views/auth/home.php');
            } else {
                $query = "SELECT COALESCE(MAX(id), 0) as id FROM topics";
                $results = mysqli_query($db, $query);
                $id = mysqli_fetch_assoc($results)['id'] + 1;
                $query = "INSERT INTO topics (id, name) VALUES (".$id.", '".$_POST['name']."')";
                mysqli_query($db, $query);
                $_SESSION['success'] = "Topic \"".$_POST['name']."\" is created.";
                header('location: topic.php?id='.$id);
            }
        }
    }
    
    function createSubtopic($db) {
        if (empty($_POST['topic'])) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
            $query = "SELECT name FROM topics where id = ".$_POST['topic'];
            $results = mysqli_query($db, $query);
            if (mysqli_num_rows($results) == 0) {
                $_SESSION['success'] = invalidInputError("topic ID");
                header('location: '.mainPage());
            } else {
                if (!permission()) {
                    $_SESSION['success'] = permissionError("create subtopics");
                } elseif (empty($_POST['name'])) {
                    $_SESSION['success'] = blankInputError("a subtopic name");
                } else {
                    $query = "SELECT id FROM subtopics where name = '".$_POST['name']."'";
                    $results = mysqli_query($db, $query);
                    
                    if (mysqli_num_rows($results) != 0) {
                        $_SESSION['success'] = clashedInputError("subtopic name", $_POST['name']);
                    } else {
                        $query = "SELECT COALESCE(MAX(id), 0) as id FROM subtopics WHERE topic = ".$_POST['topic'];
                        $results = mysqli_query($db, $query);
                        $id = mysqli_fetch_assoc($results)['id'] + 1;
                        $query = "INSERT INTO subtopics (topic, id, name) VALUES (".$_POST['topic'].", ".$id.", '".$_POST['name']."')";
                        mysqli_query($db, $query);
                        $_SESSION['success'] = "Subtopic \"".$_POST['name']."\" is created.";
                    }
                }
                header('location: topic.php?id='.$_POST['topic']);
            }
        }
    }
    
    function uploadTopicContent($db) {
        if (empty($_POST['topic'])) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
        } else {
            $query = "SELECT name FROM topics where id = ".$_POST['topic'];
            $results = mysqli_query($db, $query);
            if (mysqli_num_rows($results) == 0) {
                $_SESSION['success'] = invalidInputError("topic ID");
                header('location: '.mainPage());
            } else {
                if (!permission()) {
                    $_SESSION['success'] = permissionError("upload files");
                } elseif (empty($_POST['subtopic'])) {
                    $_SESSION['success'] = invalidInputError("subtopic ID");
                } else {
                    $query = "SELECT name FROM subtopics where id = ".$_POST['subtopic']." and topic = ".$_POST['topic'];
                    $results = mysqli_query($db, $query);
                    if (mysqli_num_rows($results) == 0) {
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
                                $_SESSION['success'] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
                            } else {
                                $_SESSION['success'] = "Sorry, there was an error uploading your file.";
                            }
                        }
                    }
                }
                header('location: topic.php?id='.$_POST['topic']);
            }
        }
    }
?>