<?php
    function permission() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1;
    }
    
    function existingTopicID($id, $db) {
        if (empty($id)) {
            return null;
        }
        
        $query = "SELECT name, prerequisite FROM topics where id = ".$id;
        $results = mysqli_query($db, $query);
        if (mysqli_num_rows($results) == 0) {
            return null;
        }
        $return = mysqli_fetch_assoc($results);
        if (!is_null($return['prerequisite'])) {
            $query = "SELECT id, name FROM topics where id = ".$return['prerequisite'];
            $results = mysqli_query($db, $query);
            $return['prerequisite'] = mysqli_fetch_assoc($results);
        }
        return $return;
    }
    
    function existingSubtopicID($id, $topic, $db) {
        if (empty($id) || empty($topic)) {
            return null;
        }
        
        $query = "SELECT name, sort FROM subtopics where id = ".$id." and topic = ".$topic;
        $results = mysqli_query($db, $query);
        if (mysqli_num_rows($results) == 0) {
            return null;
        }
        return mysqli_fetch_assoc($results);
    }
    
    function existingTopicName($name, $db) {
        $query = "SELECT id FROM topics where name = '".$name."'";
        $results = mysqli_query($db, $query);
        return mysqli_num_rows($results) != 0;
    }
    
    function existingSubtopicName($name, $topic, $db) {
        $query = "SELECT id FROM subtopics where name = '".$name."' and topic = ".$topic;
        $results = mysqli_query($db, $query);
        return mysqli_num_rows($results) != 0;
    }
?>