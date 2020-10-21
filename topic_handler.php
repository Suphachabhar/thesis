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
        $target_dir = "files/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        
        if ($uploadOk == 0) {
            $_SESSION['success'] = "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $_SESSION['success'] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            } else {
                $_SESSION['success'] = "Sorry, there was an error uploading your file.";
            }
        }
        header('location: topic.php');
    }
?>