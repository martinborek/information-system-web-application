<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/Customer.php';
require_once 'include/Postman.php';

// When displaying a list of DB results, should the items be
// selected and returned or should their details be displayed?
const DISP_MODE_DISPLAY = 1;
const DISP_MODE_SELECT = 2;

// An array containing all the data from freviously-sent forms:
$afd = array(); // AFD = AllFormData

/**
 * includes <input type="hidden"> with all data in $afd
 */
function inclAFD(){
    global $afd;
    $out='';
    foreach ($afd as $key => $value) {
        $out .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
    }
    return $out;
}

/**
 * @param int $id id of the search prompt, in case there are more
 * @return string HTML of the main search prompt
 */
function searchPrompt($id=0){
    global $afd;
    $id = "sp".$id;
    
    // If we have results, evaluate them:
    $spAction = NULL;
    if (isset($_POST[$id.'spAction'])){
        $spAction = s4web(filter_input(INPUT_POST, $id.'spAction'));
        $afd[$id.'spAction'] = $spAction;
    }
    
    // The search form:
    $p = '<div class="box">';
    $p .= '<table class="buttontable"><tr><td>Vyhledat:</td>';
    
    // If the user already selected, put it here and space the others:
    if ($spAction != NULL){
        switch ($spAction){
            case 'customer':
                $p .= '<td>'.singleSubmitButton($id, 'customer' ,'Zákazníka').'</td>';
                break;
            case 'postman':
                $p .= '<td>'.singleSubmitButton($id, 'postman' ,'Doruèovatele').'</td>';
                break;
            case 'invoice':
                $p .= '<td>'.singleSubmitButton($id, 'invoice' ,'Fakturu').'</td>';
                break;
            case 'publication':
                $p .= '<td>'.singleSubmitButton($id, 'publication' ,'Tiskovinu').'</td>';
                break;
        }
        $p .= '<td class="spacer"></td>';
    }
    
    $p .= '<td>'.singleSubmitButton($id, 'customer' ,'Zákazníka').'</td>';
    $p .= '<td>'.singleSubmitButton($id, 'postman' ,'Doruèovatele').'</td>';
    $p .= '<td>'.singleSubmitButton($id, 'invoice' ,'Fakturu').'</td>';
    $p .= '<td>'.singleSubmitButton($id, 'publication' ,'Tiskovinu').'</td>';
    $p .= '';
    $p .= '</tr></table></div>';
    
    // If the user has chosen what to search for, proceed:
    switch ($spAction){
        case 'customer':
            $p .= searchPromptCustomer($id, DISP_MODE_DISPLAY);
            break;
        case 'postman':
            break;
        case 'invoice':
            break;
        case 'publication':
            break;
    }
    
    
    return $p;
}

function singleSubmitButton($spID, $name, $value, $styleclass="button"){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $p = '<form action="'.$currentURL.'" method="POST">';
    $p .= searchPromptButton($spID, $name, $value, $styleclass);
    $p .= '</form>';
    return $p;
}

function searchPromptButton($spID, $name, $value, $styleclass="button"){
    return '<input type="hidden" name="'.$spID.'spAction" value="'.$name.'"><input type="submit" class="'.$styleclass.'" name="'.$spID.$name.'" value="'.$value.'">';
}


/**
 * Asks the user for search criteria and performs the search for customers.
 * @param type $spID ID of the original searchPrompt
 * @param DISP_MODE $mode The mode to run in.
 * @return str/int based on mode, it will return the text to display or the id of the customer.
 */
function searchPromptCustomer($spID, $mode){
    global $afd;
     /* @param type $id
    * @param type $name -> cqName (cq == Customer Query)
    * @param type $surname -> cqSurname
    * @param type $address -> cqCity, cqStreet, cqZIP
    * @param type $email -> cqEmail */
             
    // If user sent request, store the values:
    $cqName = s4web(filter_input(INPUT_POST, $spID.'cqName'));
    $cqSurname = s4web(filter_input(INPUT_POST, $spID.'cqSurname'));
    $cqCity = s4web(filter_input(INPUT_POST, $spID.'cqCity'));
    $cqStreet = s4web(filter_input(INPUT_POST, $spID.'cqStreet'));
    $cqZIP = s4web(filter_input(INPUT_POST, $spID.'cqZIP'));
    $cqEmail = s4web(filter_input(INPUT_POST, $spID.'cqEmail'));
    
    $customerID = s4web(filter_input(INPUT_POST, 'customerID'));
    
    $afd[$spID.'cqName'] = $cqName;
    $afd[$spID.'cqSurname'] = $cqSurname;
    $afd[$spID.'cqCity'] = $cqCity;
    $afd[$spID.'cqStreet'] = $cqStreet;
    $afd[$spID.'cqZIP'] = $cqZIP;
    $afd[$spID.'cqEmail'] = $cqEmail;
        
    
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
             
    
    ////////////
    // If the query was made, display the results in a new box:
    //if (isset($_POST[$spID.'cqSent'])){
    $dispCust = "";
    $custDetails = "";
    if (true){ // display query 4all customers right away
        
        ////////
        // If a customer was selected:
        if (isset($_POST['customerID'])){
            $afd['customerID'] = $customerID;

            if ($mode == DISP_MODE_SELECT){
                return $customerID; // Return ID of the selected customer
            }
            if ($mode == DISP_MODE_DISPLAY){
                $custDetails = customerDetails($customerID); // Add box with customer operations
            }
        }
        ///////////
        
        
        
        $afd[$spID.'cqSent'] = "sent";
        try{
            $customers = searchCustomers("", $cqName, $cqSurname, 
                    $cqEmail, $cqCity, $cqStreet, $cqZIP, "");
        } catch (Exception $e){
            $customers = array(); // Error with DB, don't show anything
        }
        $dispCust = displayCustomers($customers, $mode);
        
        if (is_string($dispCust)){
            //$p .= $dispCust;
        }
        elseif (is_int($dispCust)) {
        return $dispCust;
        }
    }
    ////////////
    
    
    
    $p = '<div class="box">';
    $p .= '<form action="'.$currentURL.'" method="POST">';
    
    // To preserve information for the previous box:
    $p .= '<input type="hidden" name="'.$spID.'spAction" value="customer">';
    
    // Info for us that the user sent this form:
    $p .= '<input type="hidden" name="'.$spID.'cqSent" value="sent">';
    
    // If the request wasn't sent yet, display help:
    if (!isset($_POST[$spID.'cqSent'])){
        $p .= message("Mù¾ete filtrovat zákazníky pomocí tìchto kritérií:");
    }
    else{
        $p .= '<div class="boxheading">Kritéria vyhledávání:</div>';
    }
    
    // Table with search criteria:
    $p .= '<table class="formtable" style="float:left;">';
    $p .= '<tr><td>Jméno: </td><td><input type="text" name="'.$spID.'cqName" maxlength="50" value="'.$cqName.'"></td></tr>';
    $p .= '<tr><td>Pøíjmení: </td><td><input type="text" name="'.$spID.'cqSurname" maxlength="50" value="'.$cqSurname.'"></td></tr>';
    
    $p .= '<tr><td>E-mail: </td><td><input type="text" name="'.$spID.'cqEmail" maxlength="100" value="'.$cqEmail.'"></td></tr>';
    
    $p .= '<tr><td></td><td><input class="button" type="submit" value="Vyhledat"></td></tr>';
    
    // Devide into 2 columns:
    $p .= '</table><table class="formtable" style="float:center;">';
    
    $p .= '<tr><td>Mìsto: </td><td><input type="text" name="'.$spID.'cqCity" maxlength="100" value="'.$cqCity.'"></td></tr>';
    $p .= '<tr><td>Ulice, è.p.: </td><td><input type="text" name="'.$spID.'cqStreet" maxlength="100" value="'.$cqStreet.'"></td></tr>';
    $p .= '<tr><td>PSÈ: </td><td><input type="text" name="'.$spID.'cqZIP" maxlength="5" value="'.$cqZIP.'"></td></tr>';
    $p .= '';
    $p .= '</table>';
    $p .= '</form></div>';
    
    ///
    $p .= $dispCust;
    $p .= $custDetails;
    return $p;
}


/**
 * Lists all found customers with a selection button next to them
 * @param Customer[] $customers the costomers as returned by searchCustomers()
 * @param DISP_MODE $mode The mode to run in.
 * @return str/int based on mode, it will return the text to display or the id of the customer.
 */
function displayCustomers($customers, $mode){
    global $afd;
    $custDetails = "";
    $customerID = s4web(filter_input(INPUT_POST, 'customerID'));
    
    $p = '<div class="box">';
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));

    
    // If the request wasn't sent yet, display help:
    if (!isset($_POST['customerID'])){
        // Was search successful?
        if (isset($customers[0])){
            $p .= message("Vyberte z nalezených zákazníkù nebo upravte kritéria vyhledávání:");
        }
        else{
            $p .= message("Zadaným kritériím neodpovídají ¾ádní zákazníci.", MESG_ERROR);
        }
    }
    else{
        $p .= '<div class="boxheading">Výsledky vyhledávání:</div>';
    }
    

    
    // Display all results with select buttons:
    $p .= '<table class="resulttable">';
    foreach ($customers as $c) {
        if ($c->id == $customerID){
            $p .= '<tr class="selected">'; // If we have selected the customer, highlight
        }
        else {
            $p .= '<tr>';
        }
        $p .= '<td class="resulttable">'.$c->name.'</td>';
        $p .= '<td class="resulttable">'.$c->surname.'</td>';
        $p .= '<td class="resulttable">'.$c->street.'</td>';
        $p .= '<td class="resulttable">'.$c->city.'</td>';
        
        // Add some actions for the customer:
        $p .= '<td class="resulttable"><form action="'.$currentURL.'" method="POST">';
        $p .= inclAFD(); // Include all previous data...
        $p .= '<input type="hidden" name="customerID" value="'.$c->id.'">';
        $p .= '<input type="submit" value="Vybrat" class="button">';
        $p .= '</form></td>';
        $p .= '</tr>';
    }
    $p .= '</table>';
    $p .= '</div>';
    
    ///
    $p .= $custDetails;
    return $p;
}


/**
 * Shows customer details and provides editing operations.
 * @param type $customerID
 * @return string HTML
 */
function customerDetails($customerID){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    try{
        $cArray = searchCustomers($customerID);
    } catch (Exception $e){
        $cArray = array(); // Error with DB
    }
    if (!isset($cArray[0])){
        // No results from the DB
        return message("Neexistující zákazník, zkuste nové vyhledání.", MESG_ERROR);
    }
    $c = $cArray[0]; // Select the customer
    
    // Get customer data:
    $customerID = $c->id;
    $name = $c->name;
    $surname = $c->surname;
    $email = $c->email;
    $city = $c->city;
    $street = $c->street;
    $ZIP = $c->ZIP;
    $inactiveSince = $c->inactiveSince;
    $inactiveTill = $c->inactiveTill;
    $postmanID = $c->postmanID;
    
            
    $p = '<div class="box">';
    
    // If the request wasn't sent yet, display help:
    if (!isset($_POST['cdAction'])){
        $p .= message("Nyní lze provádìt operace se zákazníkem (editace polí, ...):");
    }
    else{ // The request was sent:
        $p .= '<div class="boxheading">Detail zákazníka:</div>';
        
        // Decide what to do:
        switch ($_POST['cdAction']) {
            case 'change':
                // Get POST data:
                //$customerID = s4web(filter_input(INPUT_POST, 'cdID'));
                $name = s4web(filter_input(INPUT_POST, 'cdName'));
                $surname = s4web(filter_input(INPUT_POST, 'cdSurname'));
                $email = s4web(filter_input(INPUT_POST, 'cdEmail'));
                $city = s4web(filter_input(INPUT_POST, 'cdCity'));
                $street = s4web(filter_input(INPUT_POST, 'cdStreet'));
                $ZIP = s4web(filter_input(INPUT_POST, 'cdZIP'));
                $postmanID = s4web(filter_input(INPUT_POST, 'cdPostmanID'));
                $inactiveSince = s4web(filter_input(INPUT_POST, 'cdInactSince'));
                $inactiveTill = s4web(filter_input(INPUT_POST, 'cdInactTill'));

                // Check required fields:
                // 
                // Check inactive fields:
                $inactOK = true;
                $inactReason = ""; // String with reason for date rejection
                if ($inactiveSince != ""){
                    if ($inactiveTill == ""){
                        $inactOK = false;
                        $inactReason .= ' Bylo vyplnìno datum "Neodebírá od", ale ne "Neodebírá do".';
                    }
                    else{
                        // Both fields are set, check if dates are ok:
                        $s = strtotime($inactiveSince);
                        $t = strtotime($inactiveTill);
                        if (!$s || !$t || !checkDateFormat($inactiveSince) || !checkDateFormat($inactiveTill)){
                            $inactOK = false;
                            if(!checkDateFormat($inactiveSince)){
                                $inactReason .= ' ©patný formát data "Neodebírá od". Datum musí být ve formátu rrrr-mm-dd';
                            }
                            elseif (!$s) {
                                $inactReason .= ' ©patné datum "Neodebírá od".';
                            }
                            
                            if(!checkDateFormat($inactiveTill)){
                                $inactReason .= ' ©patný formát data "Neodebírá do". Datum musí být ve formátu rrrr-mm-dd';
                            }
                            elseif (!$t) {
                                $inactReason .= ' ©patné datum "Neodebírá do".';
                            }
                        }
                        else{
                            if ($t < $s){ // Ends before it begins.
                                $inactOK = false;
                                $inactReason .= ' Datum "Neodebírá od" pøedchází datum "Neodebírá do".';
                            }
                        }
                    }
                }
                elseif ($inactiveTill != "") {
                    $inactOK = false; // One of them is set, the other unset
                    $inactReason .= 'Bylo vyplnìno datum "Neodebírá do", ale ne "Neodebírá od".';
                }
                
                if ($inactOK && required($customerID, $name, $surname, $city, $street, $ZIP, $postmanID) && checkEmail($email) && checkZIP($ZIP)){
                    // All OK:
                    $c = new Customer($customerID, $name, $surname, $email, $city, $street, $ZIP, $postmanID);
                    try{
                        updateCustomer($c);
                        $p .= message("Data zákazníka byla upravena.", MESG_OK);
                    } catch (Exception $e){
                        $msg = message("Nelze upravit zákazníka (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
                    }
                }
                else{
                    if (!checkEmail($email)){
                        $p .= message("©patný formát e-mailové adresy.", MESG_ERROR);
                    }
                    elseif (!checkZIP($ZIP)){
                        $msg = message("©patný formát PSÈ.", MESG_ERROR);
                    }
                    elseif (!$inactOK) {
                        $p .= message($inactReason, MESG_ERROR);
                    }
                    else{
                        $p .= message("Nebyla vyplnìna v¹echna povinná pole. Pole oznaèená * musí být vyplnìna.", MESG_ERROR);
                    }
                }
                break;
            
            default:
                break;
        }
    }
    
    $p .= '<form action="'.$currentURL.'" method="POST">'; // action=change:
    
    $p .= '<table class="formtable" style="float:left;">';
    $p .= '<tr><td>*Jméno:</td><td><input type="text" name="cdName" maxlength="50" value="'.$name.'"></td></tr>';
    $p .= '<tr><td>*Pøíjmení:</td><td><input type="text" name="cdSurname" maxlength="50" value="'.$surname.'"></td></tr>';
    $p .= '<tr><td>E-mail:</td><td><input type="text" name="cdEmail" maxlength="100" value="'.$email.'"></td></tr>';
    
    $p .= '<tr><td>*Doruèovatel:</td><td><select name="cdPostmanID" class="button">';
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
        $p .= "<option value='$postman->id'$sel>$postman->surname, $postman->name (PSÈ $postman->ZIP)</option>";
    }
    $p .= '</select></td></tr>';

    
    // Devide into 2 columns:
    $p .= '</table><table class="formtable" style="float:center;">';
    
    $p .= '<tr><td>*Mìsto:</td><td><input type="text" name="cdCity" maxlength="100" value="'.$city.'"></td></tr>';
    $p .= '<tr><td>*Ulice, è.p.:</td><td><input type="text" name="cdStreet" maxlength="100" value="'.$street.'"></td></tr>';
    $p .= '<tr><td>*PSÈ:</td><td><input type="text" name="cdZIP" maxlength="5" value="'.$ZIP.'"></td></tr>';
    
    $p .= '<tr><td>Neodebírá od (rrrr-mm-dd):</td><td><input type="text" maxlength="10" name="cdInactSince" value="'.$inactiveSince.'"></td></tr>';
    $p .= '<tr><td>Neodebírá do (rrrr-mm-dd):</td><td><input type="text" maxlength="10" name="cdInactTill" value="'.$inactiveTill.'"></td></tr>';
    
    $p .= '</table>';
            
    $p .= '<input type="hidden" name="cdAction" value="change">';
    $p .= '<input type="hidden" name="cdID" value="'.$customerID.'">';
    $p .= inclAFD(); // Do not erase previously-entered data
    $p .= '<input type="submit" value="Provést zmìny" class="button">';
    $p .= '</form>';
    
    $p .= '<form action=".?action=manageSubscriptions" method="POST">';
    $p .= '<input type="hidden" name="esAction" value="customer">';
    $p .= '<input type="hidden" name="customerID" value="'.$customerID.'">';
    $p .= '<input type="submit" value="Zobrazit odbìry" class="button" style="float:center;">';
    $p .= '</form>';
    
    
    $p .= '</div>';
    return $p;
}