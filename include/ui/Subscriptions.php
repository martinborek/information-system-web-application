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
require_once 'include/Subscription.php';
require_once 'include/Customer.php';
require_once 'include/ui/UIUtils.php';


/**
 * Creates a form for creation of a new subscription
 */
function newSubscription(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    $customerID = "";
    $publicationID = "";
    
    echo '<div class="box">';
    
    if (isset($_POST['nsAction'])){
        $publicationID = s4web(filter_input(INPUT_POST, 'publicationID'));
        $customerID = s4web(filter_input(INPUT_POST, 'customerID'));
        if (required($publicationID, $customerID)){
            $s = new Subscription("", $publicationID, $customerID);
            try{
                createSubscription($s);
                echo message("Odbìr byl vytvoøen (".$s->getPublication()->title." pro ".$s->getCustomer()->name." ".$s->getCustomer()->surname.")", MESG_OK);
            } catch (Exception $e){
                echo message("Nelze vytvoøit odbìr (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
        else{
            echo message("K vytvoøení odbìru je tøeba vybrat zákazníka a tiskovinu.", MESG_ERROR);
        }
    }
    else{
        echo message("Zadejte zákazníka a tiskovinu pro vytvoøení nového odbìru:");
    }
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    
    echo '<td>Zákazník:</td>';
    echo '<td><select name="customerID" class="button">';
    if (!isset($_POST['customerID']) || !required($_POST['customerID'])){echo '<option value="" selected="selected">Vyberte zákazníka</option>';}
    // Add customers from DB:
    try{
        $customers = searchCustomers("");
    } catch (Exception $e){
        $customers = array(); // Error with DB, don't show anything
    }
    foreach ($customers as $customer) {
        $sel = '';
        if ($customer->id == $customerID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$customer->id'$sel>$customer->surname, $customer->name</option>";
    }
    echo '</select></td>';
    
    
echo '<td>bude odebírat tiskovinu:</td>';
    echo '<td><select name="publicationID" class="button">';
    if (!isset($_POST['publicationID']) || !required($_POST['publicationID'])){echo '<option value="" selected="selected">Vyberte tiskovinu</option>';}
    // Add publications from DB:
    foreach (searchPublications("") as $publication) {
        $sel = '';
        if ($publication->id == $publicationID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$publication->id'$sel>$publication->title</option>";
    }
    echo '</select></td>';
    
    
    echo '<td><input type="hidden" name="nsAction" value="sent">'
    . '<input type="submit" value="Vytvoøit odbìr" class="button"></td></tr></table></form></div>';
}






/**
 * Creates a form for editation of a subscription
 */
function editSubscription(){
    // Process POST:
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    echo "<div class='box'>";
    

    $publicationID = "";
    $customerID = "";
    $action = "";
    
    if (isset($_POST['esAction'])){
        $action = $_POST['esAction'];
        switch ($action) {
            case 'customer':
                $customerID = s4web(filter_input(INPUT_POST, 'customerID'));
                if (!required($customerID)){echo message("Je tøeba vybrat zákazníka (èi tiskovinu).", MESG_ERROR);}
                break;
            
            case 'publication':
                $publicationID = s4web(filter_input(INPUT_POST, 'publicationID'));
                if (!required($publicationID)){echo message("Je tøeba vybrat tiskovinu (èi zákazníka).", MESG_ERROR);}
                break;

            default:
                break;
        }
        $publicationID = s4web(filter_input(INPUT_POST, 'publicationID'));
    }
    else{
        echo message("Vyberte tiskovinu èi zákazníka pro zobrazení jejich odbìrù:");
    }
    
    
    // FORM FOR CUSTOMERS:
    echo '<table class="formtable"><tr>';
    echo '<td><form action="'.$currentURL.'" method="POST">Zákazník:</td>';
    echo '<td><select name="customerID" class="button">';
    if (!isset($_POST['customerID']) || !required($_POST['customerID'])){echo '<option value="" selected="selected">Vyberte zákazníka</option>';}
    // Add customers from DB:
    try{
        $customers = searchCustomers("");
    } catch (Exception $e){
        $customers = array(); // Error with DB, don't show anything
    }
    foreach ($customers as $customer) {
        $sel = '';
        if ($customer->id == $customerID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$customer->id'$sel>$customer->surname, $customer->name</option>";
    }
    echo '</select></td>';
    echo '<td><input type="hidden" name="esAction" value="customer">'
    . '<input type="submit" value="Vybrat zákazníka" class="button"></form></td></tr>';
    
    
    
    // FORM FOR PUBLICATIONS:
    echo '<tr><td><form action="'.$currentURL.'" method="POST">Tiskovina:</td><td><select name="publicationID" class="button">';
    if (!isset($_POST['publicationID']) || !required($_POST['publicationID'])){echo '<option value="" selected="selected">Vyberte tiskovinu:</option>';}
    // Add publications from DB:
    foreach (searchPublications("") as $publication) {
        $sel = '';
        if ($publication->id == $publicationID){
            $sel = ' selected="selected"';
        }
        echo "<option value='$publication->id'$sel>$publication->title</option>";
    }
    echo '</select></td><td>'
    . '<input type="hidden" name="esAction" value="publication">'
    . '<input type="submit" value="Vybrat tiskovinu" class="button"></td></tr></table></form>';
    
    echo '</div>';
    
    
    // If data have been submitted, display entries:
    if ($action != ""){
        // Process POST:
        $msg = message("Nyní mù¾ete zru¹it odbìry:");
        if (isset($_POST['delSubscriptionID'])){
            $delID = s4web(filter_input(INPUT_POST, 'delSubscriptionID'));
            try{
                $delSubArray = searchSubscriptions($delID, "", "");
                $delSub = $delSubArray[0];
                $delInfo = $delSub->getPublication()->title." pro ".$delSub->getCustomer()->name." ".$delSub->getCustomer()->surname;

                deleteSubscription($delID);
                $msg = message("Odbìr byl zru¹en ($delInfo)", MESG_OK);
            } catch (Exception $e){
                $msg = message("Nelze zru¹it odbìr, zkuste obnovit seznam.", MESG_ERROR);
            }
        }
        
        switch($action){
            case "customer":
                if (!required($customerID)){break;}
                $noSubscriptions = true;
                echo '<div class="box">';
                echo $msg;
                // Display all publications for the customer:
                echo '<table class="resulttable">';

                foreach (searchSubscriptions("", $customerID, "") as $subscription) {
                    $noSubscriptions = false;
                    echo '<tr>';
                    echo '<td class="resulttable" id="name">'.$subscription->getPublication()->title.'</td>';

                    // Add some actions for the customer:
                    echo '<td class="resulttable"><form action="'.$currentURL.'" method="POST">';

                    echo '<input type="hidden" name="customerID" value="'.$customerID.'">';
                    echo '<input type="hidden" name="esAction" value="customer">';
                    echo '<input type="hidden" name="delSubscriptionID" value="'.$subscription->id.'">';

                    echo '<input type="submit" value="Zru¹it odbìr" class="button">';
                    echo '</form></td>';
                    echo '</tr>';
                }
                if ($noSubscriptions){
                    echo message("Nebyly nalezeny ¾ádné odbìry pro zadaného zákazníka.", MESG_WARNING);
                }

                echo '</table></div>';
                break;

                
            case "publication":
                if (!required($publicationID)){break;}
                echo '<div class="box">';
                echo $msg;
                $noSubscriptions = true;
                // Display all publications for the customer:
                echo '<table class="resulttable">';

                foreach (searchSubscriptions("", "", $publicationID) as $subscription) {
                    $noSubscriptions = false;
                    echo '<tr>';
                    $c = $subscription->getCustomer();
                    echo '<td class="resulttable">'.$c->name.'</td>';
                    echo '<td class="resulttable">'.$c->surname.'</td>';
                    echo '<td class="resulttable">'.$c->city.'</td>';
                    echo '<td class="resulttable">'.$c->street.'</td>';
                    echo '<td class="resulttable">'.$c->ZIP.'</td>';

                    // Add some actions for the customer:
                    echo '<td class="resulttable"><form action="'.$currentURL.'" method="POST">';

                    echo '<input type="hidden" name="publicationID" value="'.$publicationID.'">';
                    echo '<input type="hidden" name="esAction" value="publication">';
                    echo '<input type="hidden" name="delSubscriptionID" value="'.$subscription->id.'">';

                    echo '<input type="submit" value="Zru¹it odbìr" class="button">';
                    echo '</form></td>';
                    echo '</tr>';
                }
                if ($noSubscriptions){
                    echo message("Nebyly nalezeny ¾ádné odbìry pro zadanou tiskovinu.", MESG_WARNING);
                }

                echo '</table></div>';
                break;
        }
    }
}