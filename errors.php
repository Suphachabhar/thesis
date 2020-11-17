<?php
    function mainPage() {
        return permission() ? '../auth/home.php' : '../auth/index.php';
    }
    
    function permissionError($action) {
        return "You don't have permission to ".$action.".";
    }
    
    function blankInputError($attribute) {
        return "You did not choose ".$attribute.".";
    }
    
    function clashedInputError($attribute, $input) {
        return 'The '.$attribute.' "'.$input.'" is already used.';
    }
    
    function invalidInputError($attribute) {
        return "Invalid ".$attribute.".";
    }
?>