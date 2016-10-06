<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';

const TIMEOUT = 5; // After how many minutes the user will be deauthenticated:

// Authentication levels for users:
const AUTH_LVL_NOBODY = 0;
const AUTH_LVL_POSTMAN = 1;
const AUTH_LVL_ADMIN = 2;
//const AUTH_LVL_ROOTADMIN = 3;
const AUTH_LVL_ANY = 7; // Allows anyone except NOBODY.

/**
 * Authenticates the user - makes sure that the user has proper
 * authentication level before proceeding. If not, displays login form.
 * @param type $level The level required to display the page (bitmask)
 */
function authenticate($level) {
    if (!isset($_SESSION)){
        session_start();
    }
    $currentLevel = AUTH_LVL_NOBODY;
    if (array_key_exists('authLevel', $_SESSION)) {
        $currentLevel = $_SESSION['authLevel'];
    }
    
    if ($currentLevel == AUTH_LVL_NOBODY && isset($_POST['username']) && isset($_POST['password'])) {
        $username = filter_input(INPUT_POST, "username");
        $password = filter_input(INPUT_POST, "password");
        
        try{
            $currentLevel = authenticateUser($username, $password);
        } catch (Exception $e){
            // don't display anything, the page is public.
            $currentLevel = AUTH_LVL_NOBODY;
        }
        
        if ($currentLevel != AUTH_LVL_NOBODY){
            $_SESSION['authLevel'] = $currentLevel;
            $_SESSION['username'] = $username;
        }
    }
    
    // Check session timeout (after TIMEOUT minutes):
    $timeout = False;
    if ($currentLevel != AUTH_LVL_NOBODY){
        if (isset($_SESSION['lastAuthed']) && ( ($_SESSION['lastAuthed'] + TIMEOUT*60) < time())){
            $timeout = True; // The session timed out
            deauthenticate();
            $currentLevel = AUTH_LVL_NOBODY;
        }
    }
    
    if ( (($currentLevel & $level) == 0) || $timeout) {
        // Display login form and stop generating page:
        $msg = message("Pøihla¹te se, prosím:");
        if ($timeout){
            $msg = message("Byli jste déle ne¾ ".TIMEOUT." minut neaktivní a <br>byli jste proto odhlá¹eni. <br>Znovu se pøihla¹te.", MESG_WARNING);
            deauthenticate();
        }
        if (array_key_exists('loggedout', $_GET)){
            $msg = message("Byli jste odhlá¹eni.", MESG_OK);
        }
        if (array_key_exists('username', $_POST)){
            $msg = message("Nesprávné pøihla¹ovací údaje.", MESG_ERROR);
            deauthenticate();
        }
        $loginForm = generateLoginForm($msg);
        echo $loginForm;
        echo "</body></html>";
        exit();
    }
    else{
        // Authentication ok. Set new timestamp:
        $_SESSION['lastAuthed'] = time();
    }
}

function generateLoginForm($message){
    // Sanitize current URL:
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $lastUsernameInput = s4web(filter_input(INPUT_POST, "username"));
    
    $form = '<div class="loginform">';
    $form .= '<form action="?action=loggedin" method="POST">';
    $form .= '<table style="text-align: center;">';
    $form .= '<tr>';
    $form .= '<td>'.'<p>Informaèní systém rozná¹ky tiskovin</p>'.'</td></tr><tr>';
    $form .= '<td>'.$message.'</td></tr><tr><td>';
    $form .= '<table style="text-align: right;"><tr><td>U¾ivatelské jméno:</td>';
    $form .= '<td><input type="text" name="username" value="'.$lastUsernameInput.'"></td>';
    $form .= '</tr><tr>';
    $form .= '<td>Heslo:</td><td><input type="password" name="password"></td>';
    $form .= '</tr></table></td></tr>';
    $form .= '<tr><td><input type="submit" class="button" value="Pøihlásit"></td><td></td><tr>';
    $form .= '</table>';
    $form .= '</form>';
    $form .= '</div>';
    return $form;
}


function deauthenticate() {
    if (!isset($_SESSION)){
        session_start();
    }
    $_SESSION['authLevel'] = AUTH_LVL_NOBODY; // redundant
    unset($_SESSION); // Delete session data, incl. session cookie...
    session_destroy(); // Close down the session.
}

function getAuthLevel() {
    if (isset($_SESSION['authLevel'])) {
        return $_SESSION['authLevel'];
    }
    return AUTH_LVL_NOBODY;
}

/**
 * Sanitizes string for HTML use
 * @param type $string
 * @return type
 */
function s4web($string) {
    return htmlspecialchars($string);
}


/**
 * Sanitizes string for MySQL use
 * @param type $string
 * @return type
 */
function s4db($string) {
    return mysqli_real_escape_string($string);
}