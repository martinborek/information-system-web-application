<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';
require_once 'include/Publication.php';


$MAXSTRAY = 5*365*24*3600; // Maximum stray (in seconds) since / till next delivery date

/**
 * Creates a form for creation of a new publication
 */
function newPublication(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    // If user sent request, store the values:
    $title = s4web(filter_input(INPUT_POST, 'title'));
    $description = s4web(filter_input(INPUT_POST, 'description'));
    $price = s4web(filter_input(INPUT_POST, 'price'));
    $delivDate = s4web(filter_input(INPUT_POST, 'delivDate'));
    $nextDeliv = s4web(filter_input(INPUT_POST, 'nextDeliv'));
    
    if ($delivDate == NULL){
        $delivDate = date('Y-m-d', time()); // If not set, set to today
    }
    if ($nextDeliv == NULL){
        $nextDeliv = "1"; // If not set, set to daily
    }
    
    $msg="";
    
    // If request was sent, create the publication:
    if (isset($_POST['npubAction'])){
        // Check date not too far away:
        $dateOK = true;
        $dateTime = strtotime($delivDate);
        if ($dateTime == ""){
            $dateOK = false;
        }
        else{
            $datediff = $dateTime - time();
            global $MAXSTRAY;
            if ( $datediff > $MAXSTRAY){
                $dateOK = false;
                $msg = message("Datum pøí¹tího vydání je o více ne¾ 5 let v budoucnosti.", MESG_ERROR);
            }
            if ( $datediff < -$MAXSTRAY){
                $dateOK = false;
                $msg = message("Datum pøí¹tího vydání je o více ne¾ 5 let v minulosti.", MESG_ERROR);
            }
        }
        
        // Check required fields:
        if ($dateOK && required($title, $price, $delivDate, $nextDeliv) && checkDateFormat($delivDate) && checkPrice($price) && ($nextDeliv > 0)){
            // All OK:
            $p = new Publication("", $title, $description, $price, $delivDate, $nextDeliv);
            try{
                createPublication($p);
                $msg = message("Nová tiskovina vytvoøena ($title)", MESG_OK);
            } catch (Exception $e){
                $msg = message("Nelze vytvoøit tiskovinu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
        else{
            if (!checkDateFormat($delivDate)){
                $msg = message("©patný formát data. Zadejte datum ve formátu RRRR-MM-DD.", MESG_ERROR);
            }
            elseif (!checkPrice($price)){
                $msg = message("Cena musí být celé nezáporné èíslo.", MESG_ERROR);
            }
            elseif (!$dateOK) {
            } // msg already set. Just don't do the else branch...
            else{
                $msg = message("Nebyla vyplnìna v¹echna povinná pole. Pole oznaèená * musí být vyplnìna.", MESG_ERROR);
            }
        }
    }
    else{ // Request wasn't sent, display help
        $msg = message("Zadejte údaje nové tiskoviny. Pole oznaèená * jsou povinná.");
    }
    
    
    
    echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:
    
    echo $msg;
    echo '<table class="formtable" style="float:left;">';
    echo '<tr><td>*Název:</td><td><input type="text" maxlength="100" name="title" value="'.$title.'"></td></tr>';
    echo '<tr><td>*Cena za mìsíc (Kè):</td><td><input type="text" name="price" value="'.$price.'"></td></tr>';
    echo '<tr><td>*Pøí¹tí vydání (rrrr-mm-dd):</td><td><input type="text" name="delivDate"  maxlength="10" value="'.$delivDate.'"></td></tr>';
    echo '<tr><td>*Vychází jednou za (dny):</td><td><input type="text" name="nextDeliv"  maxlength="10" value="'.$nextDeliv.'"></td></tr>';
    
    // Devide into 2 columns:
    echo '</table><table class="formtable" style="float:center;">';
    
    echo '<tr><td class="formtd">Popis:</td><td><textarea rows="5" cols="30" name="description">'.$description.'</textarea></td></tr>';

    echo '<tr><td></td><td>';
    echo '<input type="hidden" name="npubAction" value="sent">';
    echo '<input type="submit" value="Pøidat tiskovinu" class="button" style="float:left;">';
    echo '</td></tr>';
    
    echo '</table>';
    echo '</form></div>';
}


/**
 * Creates a form for editation of a publication
 */
function editPublication(){
    // Process POST:
    $showedit = false; // Show the edit box?
    $showmore = true; // Show the edit form?
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $publicationID = "";
    
    echo "<div class='box'>";
    
    if (isset($_POST['publicationID'])){
        $publicationID = s4web(filter_input(INPUT_POST, 'publicationID'));
        if (!required($publicationID)){
            echo message("Musíte vybrat tiskovinu k úpravám:", MESG_ERROR);
        }
    }
    else{
        echo message("Vyberte tiskovinu k úpravám:");
    }
    
    /////////////////////
    
    // If a publication has been selected, show them here:
    if (isset($_POST['publicationID']) && required($_POST['publicationID'])){
        $msg = "";
        // Get original data from DB:
        try{
            $pArray = searchPublications($publicationID);
            $p = $pArray[0];

            $publicationID = $p->id;
            $title = $p->title;
            $description = $p->description;
            $price = $p->price;
            $delivDate = $p->delivDate;
            $nextDeliv = $p->nextDeliv;
        } catch (Exception $e){
            $msg = message("Nelze vyhledat tiskovinu, pravdìpodobnì byla odstranìna.", MESG_ERROR);
        }
        
        // Process POST:
        if (isset($_POST['epAction'])){
            if (isset($_POST['edit'])){
                //$publicationID = s4web(filter_input(INPUT_POST, 'publicationID'));
                $title = s4web(filter_input(INPUT_POST, 'title'));
                $description = s4web(filter_input(INPUT_POST, 'description'));
                $price = s4web(filter_input(INPUT_POST, 'price'));
                $delivDate = s4web(filter_input(INPUT_POST, 'delivDate'));
                $nextDeliv = s4web(filter_input(INPUT_POST, 'nextDeliv'));
                
                // Check date not too far away:
                $dateOK = true;
                $dateTime = strtotime($delivDate);
                if ($dateTime == ""){
                    $dateOK = false;
                }
                else{
                    $datediff = $dateTime - time();
                    global $MAXSTRAY;
                    if ( $datediff > $MAXSTRAY){
                        $dateOK = false;
                        $msg = message("Datum pøí¹tího vydání je o více ne¾ 5 let v budoucnosti.", MESG_ERROR);
                    }
                    if ( $datediff < -$MAXSTRAY){
                        $dateOK = false;
                        $msg = message("Datum pøí¹tího vydání je o více ne¾ 5 let v minulosti.", MESG_ERROR);
                    }
                }
                
                // Check required fields:
                if ($dateOK && required($publicationID, $title, $price, $delivDate, $nextDeliv) && checkDateFormat($delivDate) && checkPrice($price) && ($nextDeliv > 0)){
                    // All ok, send to DB:
                    $p = new Publication($publicationID, $title, $description, $price, $delivDate, $nextDeliv);
                    try{
                        updatePublication($p);
                        $msg = message("Záznamy o tiskovinì byly aktualizovány.", MESG_OK);
                    } catch (Exception $e){
                        $msg = message("Nelze upravit tiskovinu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
                    }
                }
                else{
                    if (!checkDateFormat($delivDate)){
                        $msg = message("©patný formát data. Zadejte datum ve formátu RRRR-MM-DD.", MESG_ERROR);
                    }
                    elseif (!checkPrice($price)){
                        $msg = message("Cena musí být celé nezáporné èíslo.", MESG_ERROR);
                    }
                    elseif (!$dateOK) {
                    } // msg already set. Just don't do the else branch...
                    else{
                        $msg = message("Nebyla vyplnìna v¹echna povinná pole. Pole oznaèená * musí být vyplnìna.", MESG_ERROR);
                    }
                }
            }
            elseif (isset($_POST['delete'])) {
                try {
                    deletePublication($publicationID);
                    $msg = message("Tiskovina byla odstranìna.", MESG_OK);
                    $showmore = false;
                } catch (DBExecuteException $exc) {
                    $msg = message("Nelze ostranit tiskovinu, odstraòte v¹echny její odbìratele a zkuste to znovu.", MESG_ERROR);
                } catch (Exception $e){
                    $msg = message("Nelze odstranit tiskovinu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
                }
            }
        }
        else{
            $msg = message("Nyní mù¾ete upravit záznamy o tiskovinì:");
        }
        
        $showedit = true;
    }
    /////////////////////
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr><td>Tiskovina:</td><td><select name="publicationID" class="button">';
    if (!isset($_POST['publicationID']) || !required($publicationID)){echo '<option value="" selected="selected">Vyberte tiskovinu:</option>';}
    // Add publications from DB:
    foreach (searchPublications("") as $publication) {
        $sel = '';
        if ($publication->id == $publicationID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$publication->id'$sel>$publication->title</option>";
    }
    echo '</select></td><td>'
    . '<input type="submit" value="Vybrat k editaci" class="button"></td></tr></table></form></div>';
    
    
    if ($showedit){

        
        echo '<div class="box"><form action="'.$currentURL.'" method="POST">'; // action=change:

        echo $msg;
        if ($showmore){
            echo '<table class="formtable" style="float:left;">';
            echo '<table class="formtable" style="float:left;">';
            echo '<tr><td>*Název:</td><td><input type="text" name="title"  maxlength="100" value="'.$title.'"></td></tr>';
            echo '<tr><td>*Cena za mìsíc (Kè):</td><td><input type="text" name="price" value="'.$price.'"></td></tr>';
            echo '<tr><td>*Pøí¹tí vydání (rrrr-mm-dd):</td><td><input type="text" name="delivDate" maxlength="10" value="'.$delivDate.'"></td></tr>';
            echo '<tr><td>*Vychází jednou za (dny):</td><td><input type="text" name="nextDeliv" maxlength="10" value="'.$nextDeliv.'"></td></tr>';

            // Devide into 2 columns:
            echo '</table><table class="formtable" style="float:center;">';

            echo '<tr><td class="formtd">Popis:</td><td><textarea rows="5" cols="30" name="description">'.$description.'</textarea></td></tr>';

            echo '<tr><td></td><td>';
            echo '<input type="hidden" name="publicationID" value="'.$publicationID.'">';
            echo '<input type="hidden" name="epAction" value="sent">';
            echo '<input type="submit" name="edit" value="Upravit tiskovinu" class="button" style="float:center;">';
            echo '<input type="submit" name="delete" value="Odstranit tiskovinu" class="button" style="float:center;">';
            echo '</td></tr>';

            echo '</table>';
            echo '</form>';

            echo '<form action=".?action=manageSubscriptions" method="POST">';
            echo '<input type="hidden" name="esAction" value="publication">';
            echo '<input type="hidden" name="publicationID" value="'.$publicationID.'">';
            echo '<input type="submit" value="Zobrazit odbìratele" class="button" style="float:center;">';
            echo '</form>';
        }
        echo '</div>';
    }
    

}