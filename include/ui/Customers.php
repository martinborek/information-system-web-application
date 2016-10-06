<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';
require_once 'include/Customer.php';

/**
 * Creates a form for creation of a new customer
 */
function newCustomer(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    // If user sent request, store the values:
    $name = s4web(filter_input(INPUT_POST, 'name'));
    $surname = s4web(filter_input(INPUT_POST, 'surname'));
    $city = s4web(filter_input(INPUT_POST, 'city'));
    $street = s4web(filter_input(INPUT_POST, 'street'));
    $ZIP = s4web(filter_input(INPUT_POST, 'ZIP'));
    $email = s4web(filter_input(INPUT_POST, 'email'));
    $postmanID = s4web(filter_input(INPUT_POST, 'postmanID'));
    
    $msg = "";
    
    // If request was sent, create the customer:
    if (isset($_POST['ncAction'])){
        // Check required fields:
        if (required($name, $surname, $city, $street, $ZIP, $postmanID) && checkEmail($email) && checkZIP($ZIP)){
            // All OK:
            $c = new Customer($id="", $name, $surname, $email, $city, $street, $ZIP, $postmanID);
            $msg = message("Nov� z�kazn�k vytvo�en ($name $surname)", MESG_OK);
            try {
                createCustomer($c);
            } catch (Exception $e){
                $msg = message("Nelze vytvo�it z�kazn�ka (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
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
        $msg = message("Zadejte �daje nov�ho z�kazn�ka a p�i�a�te mu doru�ovatele. Pole ozna�en� * jsou povinn�.");
    }
    
    
    
    echo '<div class="box"><form action="'.$currentURL.'" method="POST">';
    
    echo $msg;
    echo '<table class="formtable" style="float:left;">';
    echo '<tr><td>*Jm�no:</td><td><input type="text" name="name" maxlength="50" value="'.$name.'"></td></tr>';
    echo '<tr><td>*P��jmen�:</td><td><input type="text" name="surname" maxlength="50" value="'.$surname.'"></td></tr>';
    echo '<tr><td>E-mail:</td><td><input type="text" name="email" maxlength="100" value="'.$email.'"></td></tr>';
    
    
    echo '<tr><td>*Doru�ovatel:</td><td><select name="postmanID" class="button">';
    if (!isset($_POST['postmanID']) || $_POST['postmanID'] == ""){echo '<option value="" selected="selected">Vyberte doru�ovatele:</option>';}
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
    echo '</select></td></tr>';
    
    
    // Devide into 2 columns:
    echo '</table><table class="formtable" style="float:center;">';
    
    echo '<tr><td>*M�sto:</td><td><input type="text" name="city" maxlength="100" value="'.$city.'"></td></tr>';
    echo '<tr><td>*Ulice, �.p.:</td><td><input type="text" name="street" maxlength="100" value="'.$street.'"></td></tr>';
    echo '<tr><td>*PS�:</td><td><input type="text" name="ZIP" maxlength="5" value="'.$ZIP.'"></td></tr>';

    echo '<tr><td></td><td>';
    echo '<input type="hidden" name="ncAction" value="sent">';
    echo '<input type="submit" value="P�idat z�kazn�ka" class="button" style="float:left;">';
    echo '</td></tr>';
    
    echo '</table>';
    echo '</form></div>';
}