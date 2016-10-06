<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';
require_once 'include/Postman.php';

/**
 * Creates a form for creation of a new postman
 */
function newPostman(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    // If user sent request, store the values:
    $login = s4web(filter_input(INPUT_POST, 'login'));
    $password = s4web(filter_input(INPUT_POST, 'password'));
    $name = s4web(filter_input(INPUT_POST, 'name'));
    $surname = s4web(filter_input(INPUT_POST, 'surname'));
    $city = s4web(filter_input(INPUT_POST, 'city'));
    $street = s4web(filter_input(INPUT_POST, 'street'));
    $ZIP = s4web(filter_input(INPUT_POST, 'ZIP'));
    $email = s4web(filter_input(INPUT_POST, 'email'));
    
    $msg="";
    
    // If request was sent, create the postman:
    if (isset($_POST['npAction'])){
        // Check required fields:
        if (required($login, $password, $name, $surname) && checkEmail($email) && checkZIP($ZIP)){
            // All OK:
            $p = new Postman("", $login, $password, $name, $surname, $email, $city, $street, $ZIP);
            try{
                createPostman($p);
                $msg = message("Nov� doru�ovatel vytvo�en ($login)", MESG_OK);
            } catch (DBLoginException $e){
                $msg = message("P�ihla�ovac� jm�no (login) je ji� pou�ito, zvolte jin�.", MESG_ERROR);
            } catch (Exception $e){
                $msg = message("Nelze vytvo�it doru�ovatele (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
            }
        }
        else{
            if (!checkEmail($email)){
                $msg = message("�patn� form�t e-mailov� adresy.", MESG_ERROR);
            }
            elseif (!checkZIP($ZIP)){
                $msg = message("�patn� form�t PS�.", MESG_ERROR);
            }
            else{
                $msg = message("Nebyla vypln�na v�echna povinn� pole. Pole ozna�en� * mus� b�t vypln�na.", MESG_ERROR);
            }
        }
    }
    else{ // Request wasn't sent, display help
        $msg = message("Zadejte �daje nov�ho doru�ovatele. Pole ozna�en� * jsou povinn�.");
    }
    
    
    
    echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:
    
    echo $msg;
    echo '<table class="formtable" style="float:left;">';
    echo '<tr><td>*Login:</td><td><input type="text" maxlength="50" name="login" value="'.$login.'"></td></tr>';
    echo '<tr><td>*Heslo:</td><td><input type="password" maxlength="32" name="password" value="'.$password.'"></td></tr>';
    echo '<tr><td>*Jm�no:</td><td><input type="text" name="name" maxlength="50" value="'.$name.'"></td></tr>';
    echo '<tr><td>*P��jmen�:</td><td><input type="text" name="surname" maxlength="50" value="'.$surname.'"></td></tr>';
    
    // Devide into 2 columns:
    echo '</table><table class="formtable" style="float:center;">';
    
    echo '<tr><td>E-mail:</td><td><input type="text" name="email" maxlength="100" value="'.$email.'"></td></tr>';
    echo '<tr><td>M�sto:</td><td><input type="text" name="city" maxlength="100" value="'.$city.'"></td></tr>';
    echo '<tr><td>Ulice, �.p.:</td><td><input type="text" name="street" maxlength="100" value="'.$street.'"></td></tr>';
    echo '<tr><td>PS�:</td><td><input type="text" name="ZIP" maxlength="5" value="'.$ZIP.'"></td></tr>';

    echo '<tr><td></td><td>';
    echo '<input type="hidden" name="npAction" value="sent">';
    echo '<input type="submit" value="P�idat doru�ovatele" class="button" style="float:left;">';
    echo '</td></tr>';
    
    echo '</table>';
    echo '</form></div>';
}


/**
 * Creates a form for editation of a postman
 */
function editPostman(){
    // Process POST:
    $showedit = false;
    $showMoreEdit = true;
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $postmanID = "";
    
    echo "<div class='box'>";
    
    if (isset($_POST['postmanID'])){
        $postmanID = s4web(filter_input(INPUT_POST, 'postmanID'));
    }
    else{
        echo message("Vyberte doru�ovatele k �prav�m:");
    }
    
    // If a postman has been selected, show them here:
    if (isset($_POST['postmanID']) || isset($_POST['epAction'])){
        $msg = "";
        
        // Get original data from DB:
        try{
            $pArray = searchPostmen($postmanID);
            $p = $pArray[0];
        
            $postmanID = $p->id;
            $login = $p->login;
            $password = $p->password;
            $name = $p->name;
            $surname = $p->surname;
            $email = $p->email;
            $city = $p->city;
            $street = $p->street;
            $ZIP = $p->ZIP;
        } catch (Exception $e){
            echo message("Nelze vybrat doru�ovatele, pravd�podobn� byl odstran�n.", MESG_ERROR);
        }
        
        // Process POST:
        if (isset($_POST['epAction'])){
            if ($_POST['epAction'] == "edit"){
                //$postmanID = s4web(filter_input(INPUT_POST, 'postmanID'));
                $login = s4web(filter_input(INPUT_POST, 'login'));
                $password = s4web(filter_input(INPUT_POST, 'password'));
                $name = s4web(filter_input(INPUT_POST, 'name'));
                $surname = s4web(filter_input(INPUT_POST, 'surname'));
                $email = s4web(filter_input(INPUT_POST, 'email'));
                $city = s4web(filter_input(INPUT_POST, 'city'));
                $street = s4web(filter_input(INPUT_POST, 'street'));
                $ZIP = s4web(filter_input(INPUT_POST, 'ZIP'));
                
                // Check required fields:
                if (required($postmanID, $login, $name, $surname) && checkEmail($email) && checkZIP($ZIP)){
                    // All ok, send to DB:
                    $p = new Postman($postmanID, $login, $password, $name, $surname, $email, $city, $street, $ZIP);
                    try{
                        updatePostman($p);
                        $msg = message("Z�znamy o doru�ovateli byly aktualizov�ny.", MESG_OK);
                    } catch (DBLoginException $exc) {
                        $msg = message("U�ivatelsk� jm�no (login) ji� existuje, zvolte jin�.", MESG_ERROR);
                    } catch (Exception $e){
                        $msg = message("Nelze aktualizovat doru�ovatele (po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
                    }
                }
                else{
                    if (!checkEmail($email)){
                        $msg = message("�patn� form�t e-mailov� adresy.", MESG_ERROR);
                    }
                    elseif (!checkZIP($ZIP)){
                        $msg = message("�patn� form�t PS�.", MESG_ERROR);
                    }
                    else{
                        $msg = message("Nebyla vypln�na v�echna povinn� pole. Pole ozna�en� * mus� b�t vypln�na.", MESG_ERROR);
                    }
                }
            }
            elseif ($_POST['epAction'] == "delete"){
                $altPostmanID = s4web(filter_input(INPUT_POST, 'altPostmanID'));
                if ($altPostmanID != ""){
                    deletePostman($postmanID, $altPostmanID);
                    $msg = message("Doru�ovatel $name $surname ($login) byl odstran�n", MESG_OK);
                    // Don't display any data:
                    $postmanID = "X"; // "" would mean whole DB!
                    $showMoreEdit = false;
                }
                else{
                    $msg = message("Mus�te ur�it n�hradn�ho doru�ovatele, kter� p�ebere z�kazn�ky odstra�ovan�ho doru�ovatele.", MESG_ERROR);
                }
            }
        }
        else{
            $msg = message("Nyn� m��ete upravit z�znamy o doru�ovateli: (pole ozna�en� * nesm� b�t pr�zdn�)");
        }
        
        $showedit = true;
    }
    
    
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr><td>Doru�ovatel:</td><td><select name="postmanID" class="button">';
    //if (!isset($_POST['postmanID']) || $_POST['postmanID'] == ""){echo '<option value="" selected="selected">Vyberte doru�ovatele:</option>';}
    
    // Add postmen from DB:
    try{
        $postmen = searchPostmen("");
    } catch (Exception $e){
        $postmen = array(); // Error with DB, don't show anything
    }
    foreach ($postmen as $postman) {
        $sel = '';
        if ($postman->id == $postmanID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$postman->id'$sel>$postman->surname, $postman->name (PS� $postman->ZIP)</option>";
    }
    echo '</select></td><td>'
    . '<input type="submit" value="Vybrat k editaci" class="button"></td></tr></table></form></div>';
    
    
    
    if ($showedit){
        echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:

        echo $msg;
        
        if ($showMoreEdit){
            echo '<table class="formtable" style="float:left;">';
            echo '<tr><td>*Login:</td><td><input type="text" name="login" maxlength="50" value="'.$login.'"></td></tr>';
            echo '<tr><td>Heslo:</td><td><input type="password" maxlength="32" name="password" value="'.$password.'"></td></tr>';
            echo '<tr><td>*Jm�no:</td><td><input type="text" name="name" maxlength="50" value="'.$name.'"></td></tr>';
            echo '<tr><td>*P��jmen�:</td><td><input type="text" name="surname" maxlength="50" value="'.$surname.'"></td></tr>';

            // Devide into 2 columns:
            echo '</table><table class="formtable" style="float:center;">';

            echo '<tr><td>E-mail:</td><td><input type="text" name="email" maxlength="100" value="'.$email.'"></td></tr>';
            echo '<tr><td>M�sto:</td><td><input type="text" name="city" maxlength="100" value="'.$city.'"></td></tr>';
            echo '<tr><td>Ulice, �.p.:</td><td><input type="text" name="street" maxlength="100" value="'.$street.'"></td></tr>';
            echo '<tr><td>PS�:</td><td><input type="text" name="ZIP" maxlength="5" value="'.$ZIP.'"></td></tr>';

            echo '<tr><td></td><td>';
            echo '<input type="hidden" name="postmanID" value="'.$postmanID.'">';
            echo '<input type="hidden" name="epAction" value="edit">';
            echo '<input type="submit" value="Upravit doru�ovatele" class="button" style="float:left;">';
            echo '</td></tr>';

            echo '</table>';
            echo '</form>';


            // Form to delete the postman:
            echo '<form action="'.$currentURL.'" method="POST">';
            echo '<table class="formtable" style="float:left;">';

            echo '<tr><td>';
            echo '<input type="hidden" name="postmanID" value="'.$postmanID.'">';
            echo '<input type="hidden" name="epAction" value="delete">';
            echo '<input type="submit" value="Odstranit doru�ovatele " class="button" style="float:left;">';
            echo '</td><td>';
            echo ', jeho z�kazn�ky p�ebere doru�ovatel </td><td>';


            echo '<select name="altPostmanID" class="button">';
            if (!isset($_POST['altPostmanID']) || $_POST['altPostmanID'] == ""){echo '<option value="" selected="selected">Vyberte doru�ovatele:</option>';}
            $altPostmanID = "";

            // Add postmen from DB:
            try{
                $altPostmen = searchPostmen("");
            } catch (Exception $e){
                $altPostmen = array(); // Error with DB, don't show anything
            }
            foreach ($altPostmen as $altPostman) {
                $sel = '';
                if ($altPostman->id == $altPostmanID){
                    $sel = ' selected="selected"';
                }
                if ($altPostman->id != $postmanID){ // Do not show the postman being deleted.
                    echo "<option value='$altPostman->id'$sel>$altPostman->surname, $altPostman->name (PS� $altPostman->ZIP)</option>";
                }
            }
            echo '</select>';

            echo '</table>';
            echo '</form>';
        }
        echo '</div>';
    }
}