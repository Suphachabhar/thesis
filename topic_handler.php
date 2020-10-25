<?php
    session_start();
    switch ($_POST["function"]) {
        case "upload":
            uploadCourseContent();
            break;
    
        default:
            break;
    }
    
    function uploadCourseContent() {
        $uploadOk = 1;
        if (!isset($_POST["topic"])) {
            $_SESSION['success'] = "Invalid topic ID";
            header('location: index.php');
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
        header('location: topic.php?id='.$_POST['topic']);
    }
?>