<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="ISO-8859-2">
        <link rel="stylesheet" type="text/css" href="res/base.css">
        <title></title>
    </head>
    <body>
        <?php
        // Set own error handling: throw Notices as exceptions
        set_error_handler('errorHandler');

        function errorHandler($severity, $message, $filename, $lineno) {
          if (error_reporting() == 0) {
            return;
          }
          if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $filename, $lineno);
          }
        }
        
        require_once 'include/ui/Message.php';
        require_once 'include/Security.php';
        authenticate(AUTH_LVL_POSTMAN | AUTH_LVL_ADMIN);
        
        // PAGE CONTENTS HERE
        
        echo '<div class="mainpage">';
        switch (getAuthLevel()){
            case AUTH_LVL_ADMIN:
                include 'adminBody.php';
                break;
            case AUTH_LVL_POSTMAN:
                include 'postmanBody.php';
                break;
        }
        echo '</div>';
        
        
        switch (getAuthLevel()){
            case AUTH_LVL_ADMIN:
                include 'adminMenu.php';
                break;
            case AUTH_LVL_POSTMAN:
                include 'postmanMenu.php';
                break;
        }
        ?>
    </body>
</html>