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
    
        case "searchTopic":
            searchTopic($db);
            break;
    
        case "searchProgress":
            searchProgress($db);
            break;
    
        case "getInfo":
            getTopicInfo($db);
            break;
    
        default:
            break;
    }
    
    function createTopic($db) {
        $name = str_replace("'", "''", $_POST["name"]);
        $description = str_replace("'", "''", $_POST["description"]);
        if (!permission()) {
            $_SESSION['success'] = permissionError("create topics");
            header('location: ../auth/home.php');
            return;
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a topic name");
            header('location: ../auth/home.php');
            return;
        } elseif (existingTopicName($name, $db)) {
            $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
            header('location: ../auth/home.php');
            return;
        }
        
        $attr = "name";
        $val = "'".$name."'";
        if (!empty($_POST['description'])) {
            $attr .= ", description";
            $val .= ", '".$description."'";
        }
        $query = "INSERT INTO topics (".$attr.") VALUES (".$val.")";
        mysqli_query($db, $query); 
        
        $query = "SELECT id FROM topics WHERE name = '".$name."'";
        if (!empty($_POST['description'])) {
            $query .= " AND description = '".$description."'";
        }
        $query .= " ORDER BY id DESC LIMIT 1";
        $results = mysqli_query($db, $query); 
        $id = mysqli_fetch_assoc($results)["id"];
        
        $_SESSION['success'] = "Topic \"".$_POST['name']."\" has been created successfully.";
        foreach ($_POST['prerequisite'] as $p) {
            $query = "INSERT INTO prerequisites (topic, prerequisite) VALUES (".$id.", ".$p.")";
            mysqli_query($db, $query);
        }
        
        foreach ($_POST['category'] as $c) {
            $query = "INSERT INTO topic_categories (topic, category) VALUES (".$id.", ".$c.")";
            mysqli_query($db, $query);
        }
        header('location: topic.php?id='.$id);
    }
    
    function createSubtopic($db) {
        $name = str_replace("'", "''", $_POST["name"]);
        if (!existingTopicID($_POST['topic'], $db)) {
            $_SESSION['success'] = invalidInputError("topic ID");
            header('location: '.mainPage());
            return;
        }
        
        if (!permission()) {
            $_SESSION['success'] = permissionError("create subtopics");
        } elseif (empty($_POST['name'])) {
            $_SESSION['success'] = blankInputError("a subtopic name");
        } elseif (existingSubtopicName($name, $_POST['topic'], $db)) {
            $_SESSION['success'] = clashedInputError("subtopic name", $_POST['name']);
        } else {
            $query = "SELECT COALESCE(MAX(id), 0) as id, COUNT(*) as sort FROM subtopics WHERE topic = ".$_POST['topic'];
            $results = mysqli_query($db, $query);
            $newInfo = mysqli_fetch_assoc($results);
            $id = $newInfo['id'] + 1;
            $sort = $newInfo['sort'] + 1;
            $query = "INSERT INTO subtopics (topic, id, name, sort) VALUES (".$_POST['topic'].", ".$id.", '".$name."', ".$sort.")";
            mysqli_query($db, $query);
            $_SESSION['success'] = "Subtopic \"".$_POST['name']."\" has been created successfully.";
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function editSubtopicName($db) {
        $name = str_replace("'", "''", $_POST["name"]);
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
            if (existingSubtopicName($name, $_POST['topic'], $db)) {
                $_SESSION['success'] = clashedInputError('subtopic name', $_POST['name']);
            } else {
                $query = "UPDATE subtopics SET name = '".$name."' WHERE id = ".$_POST['id']." and topic = ".$_POST['topic'];
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
            $query = "UPDATE subtopics SET sort = (sort - 1) WHERE topic = ".$_POST['topic']." AND sort > ".$subtopic['sort'];
            mysqli_query($db, $query);
            removeSubtopicDirectory($_POST['topic'], $_POST['id']);
            $_SESSION['success'] = "Subtopic \"".$subtopic['name']."\" has been deleted successfully.";
        }
        header('location: topic.php?id='.$_POST['topic']);
    }
    
    function editTopic($db) {
        $name = str_replace("'", "''", $_POST["name"]);
        $description = str_replace("'", "''", $_POST["description"]);
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
            // subtopic order
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
            
            // category
            $existingCat = array_map(function($cat) {
                return strval($cat['id']);
            }, $topic['category']);
            $catToKeep = array_intersect($_POST['category'], $existingCat);
            $query = "DELETE FROM topic_categories WHERE topic = ".$_POST['id']." AND category IN (".join(", ", array_diff($existingCat, $catToKeep)).")";
            mysqli_query($db, $query);
            $values = array();
            foreach (array_diff($_POST['category'], $catToKeep) as $cat) {
                $values[] = "(".$_POST['id'].", ".$cat.")";
            }
            if (count($values) > 0) {
                $query = "INSERT INTO topic_categories (topic, category) VALUES ".join(", ", $values);
                mysqli_query($db, $query);
            }
            
            // prerequisite
            $existingPrereq = array_map(function($prereq) {
                return strval($prereq['id']);
            }, $topic['prerequisite']);
            $prereqToKeep = array_intersect($_POST['prerequisite'], $existingPrereq);
            $query = "DELETE FROM prerequisites WHERE topic = ".$_POST['id']." AND prerequisite IN (".join(", ", array_diff($existingPrereq, $prereqToKeep)).")";
            mysqli_query($db, $query);
            $values = array();
            foreach (array_diff($_POST['prerequisite'], $prereqToKeep) as $prereq) {
                $values[] = "(".$_POST['id'].", ".$prereq.")";
            }
            if (count($values) > 0) {
                $query = "INSERT INTO prerequisites (topic, prerequisite) VALUES ".join(", ", $values);
                mysqli_query($db, $query);
            }
            
            // topic name
            if (!empty($_POST['name']) && strcmp($_POST['name'], $topic['name']) != 0) {
                if (existingTopicName($name, $db)) {
                    $_SESSION['success'] = clashedInputError('topic name', $_POST['name']);
                    $error = true;
                } else {
                    $query = "UPDATE topics SET name = '".$name."' WHERE id = ".$_POST['id'];
                    mysqli_query($db, $query);
                }
            }
            
            // description
            if (strcmp($_POST['description'], $topic['description']) != 0) {
                $query = "UPDATE topics SET description = '".$description."' WHERE id = ".$_POST['id'];
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
        $url = "topic.php?id=".$_POST['topic'];
        if (!empty($_POST['topic']) && !empty($_POST['progress'])) {
            $query = "UPDATE progresses SET progress = ".$_POST['progress']." WHERE student = ".$_SESSION['user']['id']." AND topic = ".$_POST['topic'];
            mysqli_query($db, $query);
            
            $query = "SELECT max(sort) AS max FROM subtopics WHERE topic = ".$_POST['topic'];
            $result = mysqli_fetch_assoc(mysqli_query($db, $query));
            if ($result['max'] == intval($_POST['progress'])) {
                $url = "../auth/home.php?topic=".$_POST['topic'];
            }
        }
        print $url;
    }
    
    function searchTopic($db) {
        $link = "";
        $query = "SELECT id FROM topics WHERE name = '".str_replace("'", "''", $_POST["name"])."'";
        $result = mysqli_query($db, $query);
        if (mysqli_num_rows($result) > 0) {
            $link = "../topic/topic.php?id=".mysqli_fetch_assoc($result)["id"];
        }
        print $link;
    }
    
    function searchProgress($db) {
        if (is_numeric($_POST["student"]) && is_int(intval($_POST["student"]))) {
            $query = "SELECT a.id, b.progress, COUNT(c.topic) AS nSub FROM topics AS a LEFT JOIN progresses AS b ON a.id = b.topic"
                ." LEFT JOIN subtopics AS c ON a.id = c.topic WHERE b.student = ".$_POST["student"]." GROUP BY a.id";
            $result = mysqli_query($db, $query);
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            print json_encode($rows);
        }
    }
    
    function getTopicInfo($db) {
        $output = "error";
        $query = "SELECT name, description FROM topics WHERE id = ".$_POST["id"];
        $result = mysqli_query($db, $query);
        if (mysqli_num_rows($result) > 0) {
            $info = mysqli_fetch_assoc($result);
            $query = "SELECT name, sort FROM subtopics WHERE topic = ".$_POST["id"]." ORDER BY sort";
            $result = mysqli_query($db, $query);
            $subtopics = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $query = "SELECT a.id, a.name FROM topics AS a, prerequisites AS b WHERE b.topic = ".$_POST["id"]." AND b.prerequisite = a.id";
            $result = mysqli_query($db, $query);
            $prereqs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $query = "SELECT a.id, a.name FROM topics AS a, prerequisites AS b WHERE b.prerequisite = ".$_POST["id"]." AND b.topic = a.id";
            $result = mysqli_query($db, $query);
            $after = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            $addLink = true;
            $prereqCheck = array();
            if(!permission()){
                foreach ($prereqs as $p) {
                    $pid = $p['id'];
                    $progress = getUserProgress($pid, $db);
                    $prereqCheck[$pid] = !is_null($progress) && $progress['nSub'] == $progress['progress'];
                    if (!$prereqCheck[$pid]) {
                        $addLink = false;
                    }
                }
            }
            $output = '<h1>'.$info['name'];
            if ($addLink) {
                $output .= '<span><button type="button" class="btn btn-primary btn-sm" onclick="window.location.href = \'../topic/topic.php?id='.$_POST["id"].'\'">access</button></span>';
            }
            $output .= '</h1>';
          
            
            if (!is_null($info['description'])) {
                $output .= '<p>'.$info['description'].'</p>';
            }

            $subsFinished = 0;
            if (!permission()) {
                $row = getUserProgress($_POST["id"], $db);
                $subsFinished = $row['progress'];
                $percentage = $row['nSub'] == 0 ? 0 : round(($subsFinished / $row['nSub']) * 100);
                $output .= '<h4>Progress</h4><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="'
                    .$percentage.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%"></div></div>';
            }

            
            if (count($subtopics) > 0) {
                $output .= '<br/><h4>Subtopics</h4><div class="card-body"><table class="table table-hover"><tbody>';
                foreach ($subtopics as $s) {
                    $output .= '<tr><td style="width: 90%" onclick="window.location.href = \'../topic/topic.php?id='.$_POST["id"].'\'">'.$s['name'];
                    if (!permission()) {
                        if ($subsFinished >= $s['sort']) {
                            $output .= '<td><img data-toggle="tooltip" title="completed" src="../auth/img/tick.png"></td>';
                        } else {
                            $output .= '<td><img data-toggle="tooltip" title="not complete" src="../auth/img/warning.png"></td>';
                        }
                    }
                    $output .= '</td></tr>';
                }
                $output .= '</tbody></table></div>';
            }
            if (count($prereqs) > 0) {
                $output .= '<br/><h4>Prerequisite</h4><div class="card-body"><table class="table table-hover"><tbody>';
                foreach ($prereqs as $p) {
                    $pid = $p['id'];
                    $output .= '<tr><td style="width: 90%"  onclick="openNav('.$pid.')">'.$p['name'];
                    if (!permission()) {
                        if ($prereqCheck[$pid]) {
                            $output .= '<td><img data-toggle="tooltip" title="completed" src="../auth/img/tick.png"></td>';
                        } else {
                            $output .= '<td><img data-toggle="tooltip" title="not complete" src="../auth/img/warning.png"></td>';
                        }
                    }
                    $output .= '</td></tr>';
                }
                $output .= '</tbody></table></div>';
            }
            if (count($after) > 0) {
                $output .= '<br/><h4>What you should do next</h4><div class="card-body"><table class="table table-hover"><tbody>';
                foreach ($after as $a) {
                    $aid = $a['id'];
                    $output .= '<tr><td style="width: 90%"  onclick="openNav('.$aid.')">'.$a['name'];
                    if (!permission()) {
                        $progress = getUserProgress($aid, $db);
                        if (!is_null($progress) && $progress['nSub'] == $progress['progress']) {
                            $output .= '<td><img data-toggle="tooltip" title="completed" src="../auth/img/tick.png"></td>';
                        } else {
                            $output .= '<td><img data-toggle="tooltip" title="not complete" src="../auth/img/warning.png"></td>';
                        }
                    }
                    $output .= '</td></tr>';
                }
                $output .= '</tbody></table></div>';
            }
            
            
            
        }
        print $output;
    }

    '<!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body>
    
   
    </body>
    </html>'
?>