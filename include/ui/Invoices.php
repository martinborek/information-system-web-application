<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'include/Security.php';
require_once 'include/BackendInterface.php';
require_once 'include/ui/Message.php';
require_once 'include/Invoice.php';
require_once 'include/Customer.php';


/**
 * Creates a form for creation of a new subscription
 */
function newInvoice(){
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    echo '<div class="box" style="padding: 10px;">';
    
    // Info
    echo '<h3>Informace pro hodnotící</h3><br>';
    echo '<p style="text-align:left;">Tato stránka je do informaèního systému vlo¾ena umìle. Umo¾òuje nad databází '
    . 'manuálnì provádìt operace, které by pøi reálném nasazení byly automatizované.</p>'
    . '<p style="text-align:left;"> Napøíklad umo¾òuje vytvoøit v¹echny faktury '
    . 'pro zákazníky za aktuální mìsíc, èi pøepoèítat data pøí¹tího vydání tiskovin. '
    . 'Pøi reálném nasazení by obì tyto funkcionality byly '
    . 'spou¹tìny automaticky (jako cron úlohy), pro snadnìj¹í ovìøení funkènosti pøi hodnocení '
    . 'jsme v¹ak zvolili manuální spou¹tìní.</p>'
    . '<p style="text-align:left;">Rovnì¾ jsme pøidali mo¾nost v¹echny faktury z databáze odstranit,'
    . 'jako¾to i mo¾nost smazat celou databázi (factory reset), takté¾ pro '
    . 'usnadnìní hodnocení. Tato funkènost by v reálném informaèním systému '
    . 'nebyla implementována.</p><br>';
    
    echo '</div>';
    echo '<div class="box">';
    
    if (isset($_POST['invAction'])){
        if ($_POST['invAction'] == 'create'){
            try{
                createInvoices();
                echo message("Faktury pro aktuální mìsíc byly vytvoøeny.", MESG_OK); 
            } catch (Exception $e){
                echo message("Nelze vytvoøit faktury (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'delete') {
            try{
                deleteAllInvoices();
                echo message("V¹echny faktury byly ostranìny z databáze.", MESG_OK);
                echo message("Nyní mù¾ete mít problémy s finanèním úøadem. Pøejeme hodnì ¹tìstí.", MESG_WARNING);
            } catch (Exception $e){
                echo message("Nelze odstranit faktury (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'recalculate') {
            try{
                $res = updateDeliveryDates();
                echo message("Data následujícího vydání byla pøepoèítána.", MESG_OK);
            } catch (Exception $e){
                echo message("Nelze pøepoèítat data vydání (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'purge') {
            try{
                truncateAllTables();
                echo message("Databáze byla uvedena do výchozího stavu.", MESG_OK);

                echo message("Byl vytvoøen výchozí administrátor: login 'admin', heslo '12345'.", MESG_OK);
                echo message("Byl vytvoøen výchozí doruèovatel: login 'znamy', heslo 'neheslo'.", MESG_OK);
                echo message("Byl vytvoøen výchozí doruèovatel: login 'postman', heslo 'abcde'.", MESG_OK);
                echo message("Byl vytvoøen výchozí doruèovatel: login 'Romèa', heslo 'Heslo'.", MESG_OK);
                echo message("Dùzaznì doporuèujeme zmìnit pøihla¹ovací údaje pro tyto u¾ivatele!", MESG_WARNING);
            } catch (Exception $e){
                echo message("Nelze uvést databázi do výchozího stavu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
            }
        }
    }
    else{
        echo message("Nyní mù¾ete provádìt správu databáze:");
    }

    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Pøepoèítat data dal¹ího vydání v¹ech tiskovin (dennì v 0:00):</td>';
    echo '<td><input type="hidden" name="invAction" value="recalculate">'
    . '<input type="submit" value="Pøepoèítat" class="button"></td></tr></table></form>';
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Vytvoøit faktury za aktuální mìsíc (mìsíènì ka¾dý poslední den):</td>';
    echo '<td><input type="hidden" name="invAction" value="create">'
    . '<input type="submit" value="Vytvoøit" class="button"></td></tr></table></form>';
   
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Odstranit v¹echny faktury z databáze:</td>';
    echo '<td><input type="hidden" name="invAction" value="delete">'
    . '<input type="submit" value="Odstranit faktury" class="button"></td></tr></table></form>';
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Vymazat v¹echna data z databáze a uvést ji do výchozího stavu:</td>';
    echo '<td><input type="hidden" name="invAction" value="purge">'
    . '<input type="submit" value="Factory reset" class="button"></td></tr></table></form>';
    
    echo '</div>';
}






/**
 * Creates a form for editation of a subscription
 */
function editInvoice(){
    // Process POST:
    $currentURL = s4web(filter_input(INPUT_SERVER, "REQUEST_URI"));
    
    echo "<div class='box'>";
    
    $unpaidOnly = true;
    $action = "";
    
    if (isset($_POST['invAction'])){
        $action = $_POST['invAction'];
        switch ($action) {
            case 'showAll':
                $unpaidOnly = false;
                break;
            
            case 'showUnpaid':
                $unpaidOnly = true;
                break;
        }
    }
    else{
        echo message("Mù¾ete zobrazit v¹echny faktury, nebo jen dosud nezaplacené faktury:");
    }
    
    
    echo '<table class="formtable"><tr>';
    echo '<td>Zobrazit v¹echny faktury:</td>';
    echo '<td><form action="'.$currentURL.'" method="POST"><input type="hidden" name="invAction" value="showAll">'
    . '<input type="submit" value="Zobrazit v¹echny" class="button" style="width: 100%;"></form></td></tr>';
    
    
    echo '<tr><td>Zobrazit pouze nezaplacené faktury:</td><td>'
    . '<form action="'.$currentURL.'" method="POST"><input type="hidden" name="invAction" value="showUnpaid">'
    . '<input type="submit" value="Zobrazit nezaplacené" class="button"></form></td></tr></table>';
    
    echo '</div>';
    
    
    // If data have been submitted, display entries:
    if ($action != ""){
        // Process POST:
        $msg = message("Nyní mù¾ete spravovat dosud nezaplacené faktury:");
        if (isset($_POST['invoiceID'])){
            $invID = s4web(filter_input(INPUT_POST, 'invoiceID'));
            
            // What do we do with the invoice?
            if (isset($_POST['deleteInvoice'])){
                try{
                    deleteInvoice($invID);
                    $msg = message("Faktura #$invID byla odstranìna.", MESG_OK);
                } catch (Exception $e){
                    $msg = message("Nelze odstranit fakturu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
                }
            }
            elseif (isset($_POST['markInvoicePaid'])){
                try{
                    paymentInvoice($invID, date('Y-m-d', time()));
                    $msg = message("Platba faktury #$invID byla evidována", MESG_OK);
                } catch (Exception $e){
                    $msg = message("Nelze evidovat platbu (Po¾adavek byl odmítnut databází.)", MESG_ERROR);
                }
            }
        }
        
        echo '<div class="box">';
        echo $msg;
        // Display found invoices:
        echo '<table class="resulttable">';

        foreach (searchInvoices("", $unpaidOnly) as $invoice) {
            echo '<tr>';
            echo '<td class="resulttable" style="padding-left: 10px;">#'.$invoice->id.'</td>';
            echo '<td class="resulttable">'.$invoice->price.' Kè</td>';
            echo '<td class="resulttable">'.$invoice->getCustomer()->surname.'</td>';
            echo '<td class="resulttable">'.$invoice->getCustomer()->name.'</td>';
            echo '<td class="resulttable">'.$invoice->getCustomer()->city.'</td>';
            echo '<td class="resulttable">'.$invoice->getCustomer()->street.'</td>';

            if (!$invoice->isPaid()){
                // Add some actions for the invoice if it's not paid yet:
                echo '<td class="resulttable"><form action="'.$currentURL.'" method="POST">';

                echo '<input type="hidden" name="invoiceID" value="'.$invoice->id.'">';
                echo '<input type="hidden" name="invAction" value="'.$action.'">';

                echo '<input type="submit" name="deleteInvoice" value="Odstranit fakturu" class="button">';
                //echo '</td><td>';
                echo '<input type="submit" name="markInvoicePaid" value="Evidovat platbu" class="button">';
                echo '</form></td>';
            }
            else{
                echo '<td class="resulttable"></td>';
            }
            echo '</tr>';
        }
        //

        echo '</table></div>';
    }
}