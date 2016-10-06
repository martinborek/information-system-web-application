<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';
require_once 'include/Admin.php';
require_once 'include/ui/UIUtils.php';

function newAdmin(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    // If user sent request, store the values:
    $login = s4web(filter_input(INPUT_POST, 'login'));
    $password = s4web(filter_input(INPUT_POST, 'password'));
    $name = s4web(filter_input(INPUT_POST, 'name'));
    $surname = s4web(filter_input(INPUT_POST, 'surname'));
    $email = s4web(filter_input(INPUT_POST, 'email'));
    
    $msg="";
    
    // If request was sent, create the admin:
    if (isset($_POST['npAction'])){
        // Check required fields:
        if (required($login, $password) && checkEmail($email)){
            // All OK:
            $a = new Admin("", $login, $password, $name, $surname, $email);
            try {
                createAdmin($a);
                $msg = message("Nový administrátor vytvoøen ($login)", MESG_OK);
            } catch (DBLoginException $exc) {
                $msg = message("U¾ivatelské jméno (login) ji¾ existuje, zvolte jiné.", MESG_ERROR);
            } catch (Exception $exc) {
                $msg = message("Nelze vytvoøit administrátora.", MESG_ERROR);
            }
        }
        else{
            if (!checkEmail($email)){
                $msg = message("©patný formát e-mailové adresy.", MESG_ERROR);
            }
            else{
                $msg = message("Nebyla vyplnìna v¹echna povinná pole. Pole oznaèená * musí být vyplnìna.", MESG_ERROR);
            }
        }
    }
    else{ // Request wasn't sent, display help
        $msg = message("Zadejte údaje nového administrátora. Pole oznaèená * jsou povinná.");
    }
    
    
    
    echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:
    
    echo $msg;
    echo '<table class="formtable" style="float:left;">';
        echo '<tr><td>*Login:</td><td><input type="text" name="login" maxlength="50" value="'.$login.'"></td></tr>';
        echo '<tr><td>*Heslo:</td><td><input type="password" name="password" maxlength="32" value="'.$password.'"></td></tr>';
        echo '<tr><td>E-mail:</td><td><input type="text" name="email" maxlength="100" value="'.$email.'"></td></tr>';

        // Devide into 2 columns:
        echo '</table><table class="formtable" style="float:center;">';

        echo '<tr><td>Jméno:</td><td><input type="text" name="name" maxlength="50" value="'.$name.'"></td></tr>';
        echo '<tr><td>Pøíjmení:</td><td><input type="text" name="surname" maxlength="50" value="'.$surname.'"></td></tr>';

    echo '<tr><td></td><td>';
    echo '<input type="hidden" name="npAction" value="sent">';
    echo '<input type="submit" value="Pøidat administrátora" class="button" style="float:left;">';
    echo '</td></tr>';
    
    echo '</table>';
    echo '</form></div>';
}



function editAdmin(){
    // Process POST:
    $showedit = false; // Show the edit box?
    $showmore = true; // Show the edit form?
    $dblogin = "";
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $adminID = "";
    
    echo "<div class='box'>";
    
    if (isset($_POST['adminID'])){
        $adminID = s4web(filter_input(INPUT_POST, 'adminID'));
    }
    else{
        echo message("Vyberte administrátora k úpravám:");
    }
    
    // If a admin has been selected, show them here:
    if (isset($_POST['adminID'])){
        $msg = "";
        
        // Get original data from DB:
        try{
            $pArray = searchAdmins($adminID);
            $p = $pArray[0];
            
            $adminID = $p->id;
            $login = $p->login;
            $dblogin = $p->login;
            $password = $p->password;
            $name = $p->name;
            $surname = $p->surname;
            $email = $p->email;
        } catch (Exception $e){
            echo message("Nelze vybrat administrátora, pravdìpodobnì byl odstranìn.", MESG_ERROR);
        }
        
        // Process POST:
        if (isset($_POST['editAdmin'])){
            // Form has been submitted:
            
            //$adminID = s4web(filter_input(INPUT_POST, 'adminID'));
            $login = s4web(filter_input(INPUT_POST, 'login'));
            $password = s4web(filter_input(INPUT_POST, 'password'));
            $name = s4web(filter_input(INPUT_POST, 'name'));
            $surname = s4web(filter_input(INPUT_POST, 'surname'));
            $email = s4web(filter_input(INPUT_POST, 'email'));

            // Check required fields:
            if (required($adminID, $login) && checkEmail($email)){
                // All OK, send to DB:
                $p = new Admin($adminID, $login, $password, $name, $surname, $email);
                try {
                    updateAdmin($p);
                    $msg = message("Záznamy o administrátorovi byly aktualizovány.", MESG_OK);
                    // If editing myself, update username in session.
                    if ($_SESSION['username'] == $dblogin){
                        $_SESSION['username'] = $login; // 
                    }
                } catch (DBLoginException $exc) {
                    $msg = message("U¾ivatelské jméno (login) ji¾ existuje, zvolte jiné.", MESG_ERROR);
                } catch (Exception $e){
                    $msg = message("Nelze aktualizovat administrátora (po¾adavek byl odmítnut databází.)", MESG_ERROR);
                }
            }
            else{
                if (!checkEmail($email)){
                    $msg = message("©patný formát e-mailové adresy.", MESG_ERROR);
                }
                else{
                    $msg = message("Nebyla vyplnìna v¹echna povinná pole. Pole oznaèená * musí být vyplnìna.", MESG_ERROR);
                }
            }
        }
        elseif (isset($_POST['deleteAdmin'])){
            if (required($adminID)){
                if ($_SESSION['username'] == $dblogin){
                    $msg = message("Nelze odstranit sebe sama.", MESG_ERROR);
                }
                else{
                    try {
                        deleteAdmin($adminID);
                        $msg = message("Administrátor byl odstranìn.", MESG_OK);
                        $showmore = false;
                    } catch (Exception $e){
                        $msg = message("Nelze odstranit administrátora (po¾adavek byl odmítnut databází.)", MESG_ERROR);
                    }
                }
            }
        }
        else{
            // Form hasn't been submitted yet
            $msg = message("Nyní mù¾ete upravit záznamy o administrátorovi: (pole oznaèená * nesmí být prázdná)");
        }
        
        $showedit = true;
    }
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr><td>Administrátor:</td><td><select name="adminID" class="button">';
    if (!isset($_POST['adminID'])){echo '<option value="X" selected="selected">Vyberte administrátora:</option>';}
    // Add admins from DB:
    try{
        $admins = searchAdmins("");
    } catch (Exception $e){
        $admins = array(); // Error with DB, don't show anything
    }
    foreach ($admins as $admin) {
        $sel = '';
        if ($admin->id == $adminID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$admin->id'$sel>$admin->surname, $admin->name ($admin->login)</option>";
    }
    echo '</select></td><td>'
    . '<input type="submit" value="Vybrat k editaci" class="button"></td></tr></table></form></div>';
    
    
    if ($showedit){
        echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:

        echo $msg;
        if ($showmore){
            echo '<table class="formtable" style="float:left;">';
            echo '<tr><td>*Login:</td><td><input type="text" name="login" maxlength="50" value="'.$login.'"></td></tr>';
            echo '<tr><td>Heslo:</td><td><input type="password" name="password" maxlength="32" value="'.$password.'"></td></tr>';
            echo '<tr><td>E-mail:</td><td><input type="text" name="email" maxlength="100" value="'.$email.'"></td></tr>';

            // Devide into 2 columns:
            echo '</table><table class="formtable" style="float:center;">';

            echo '<tr><td>Jméno:</td><td><input type="text" name="name" maxlength="50" value="'.$name.'"></td></tr>';
            echo '<tr><td>Pøíjmení:</td><td><input type="text" name="surname" maxlength="50" value="'.$surname.'"></td></tr>';

            echo '<tr><td></td><td>';
            echo '<input type="hidden" name="adminID" value="'.$adminID.'">';
            echo '<input type="hidden" name="eaAction" value="sent">';
            echo '<input type="submit" value="Upravit administrátora" name="editAdmin" class="button" style="float:left;">';
            echo '<input type="submit" value="Odstranit administrátora" name="deleteAdmin" class="button" style="float:left;">';
            echo '</td></tr>';
            echo '</table>';
        }
        echo '</form></div>';
    }
}