<?php
    require_once("database.php");
    session_start();
    switch ($_POST["function"]) {
        case "createTopic":
            createTopic($db);
            break;
    
        case "upload":
            uploadTopicContent();
            break;
    
        default:
            break;
    }
    
    function createTopic($db) {
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) {
            if (empty($_POST['name'])) {
                $_SESSION['success'] = "Please provide a topic name.";
                header('location: views/auth/home.php');
            } else {            
                $query = "SELECT id FROM topics where name = '".$_POST['name']."'";
                $results = mysqli_query($db, $query);
                
                if (mysqli_num_rows($results) != 0) {
                    $_SESSION['success'] = "The topic name \"".$_POST['name']."\" has been used.";
                    header('location: views/auth/home.php');
                } else {
                    $query = "SELECT max(id) FROM topics";
                    $results = mysqli_query($db, $query);
                    $id = mysqli_fetch_assoc($results)["max(id)"] + 1;
                    $query = "INSERT INTO topics (id, name) VALUES (".$id.", '".$_POST['name']."')";
                    mysqli_query($db, $query);
                    mkdir("files/".$id);
                    $_SESSION['success'] = "Topic \"".$_POST['name']."\" is created.";
                    $query = "SELECT id FROM topics where name = '".$_POST['name']."' LIMIT 1";
                    $results = mysqli_query($db, $query);
                    $topic = mysqli_fetch_assoc($results);
                    header('location: topic.php?id='.$id);
                }
            }
        } else {
            $_SESSION['success'] = "You don't have permission to create topics.";
            header('location: views/auth/index.php');
        }
    }
    
    function uploadTopicContent() {
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) {
            $uploadOk = 1;
            if (!isset($_POST["topic"])) {
                $_SESSION['success'] = "Invalid topic ID";
                header('location: views/auth/index.php');
            }
            $target_file = "files/".$_POST["topic"]."/".basename($_FILES["fileToUpload"]["name"]);
            
            if ($uploadOk == 0) {
                $_SESSION['success'] = "Sorry, your file was not uploaded.";
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    $_SESSION['success'] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
                } else {
                    $_SESSION['success'] = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $_SESSION['success'] = "You don't have permission to upload files.";
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
?>