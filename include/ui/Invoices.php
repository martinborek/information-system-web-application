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
    echo '<h3>Informace pro hodnot�c�</h3><br>';
    echo '<p style="text-align:left;">Tato str�nka je do informa�n�ho syst�mu vlo�ena um�le. Umo��uje nad datab�z� '
    . 'manu�ln� prov�d�t operace, kter� by p�i re�ln�m nasazen� byly automatizovan�.</p>'
    . '<p style="text-align:left;"> Nap��klad umo��uje vytvo�it v�echny faktury '
    . 'pro z�kazn�ky za aktu�ln� m�s�c, �i p�epo��tat data p��t�ho vyd�n� tiskovin. '
    . 'P�i re�ln�m nasazen� by ob� tyto funkcionality byly '
    . 'spou�t�ny automaticky (jako cron �lohy), pro snadn�j�� ov��en� funk�nosti p�i hodnocen� '
    . 'jsme v�ak zvolili manu�ln� spou�t�n�.</p>'
    . '<p style="text-align:left;">Rovn� jsme p�idali mo�nost v�echny faktury z datab�ze odstranit,'
    . 'jako�to i mo�nost smazat celou datab�zi (factory reset), takt� pro '
    . 'usnadn�n� hodnocen�. Tato funk�nost by v re�ln�m informa�n�m syst�mu '
    . 'nebyla implementov�na.</p><br>';
    
    echo '</div>';
    echo '<div class="box">';
    
    if (isset($_POST['invAction'])){
        if ($_POST['invAction'] == 'create'){
            try{
                createInvoices();
                echo message("Faktury pro aktu�ln� m�s�c byly vytvo�eny.", MESG_OK); 
            } catch (Exception $e){
                echo message("Nelze vytvo�it faktury (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'delete') {
            try{
                deleteAllInvoices();
                echo message("V�echny faktury byly ostran�ny z datab�ze.", MESG_OK);
                echo message("Nyn� m��ete m�t probl�my s finan�n�m ��adem. P�ejeme hodn� �t�st�.", MESG_WARNING);
            } catch (Exception $e){
                echo message("Nelze odstranit faktury (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'recalculate') {
            try{
                $res = updateDeliveryDates();
                echo message("Data n�sleduj�c�ho vyd�n� byla p�epo��t�na.", MESG_OK);
            } catch (Exception $e){
                echo message("Nelze p�epo��tat data vyd�n� (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
            }
        }
        elseif ($_POST['invAction'] == 'purge') {
            try{
                truncateAllTables();
                echo message("Datab�ze byla uvedena do v�choz�ho stavu.", MESG_OK);

                echo message("Byl vytvo�en v�choz� administr�tor: login 'admin', heslo '12345'.", MESG_OK);
                echo message("Byl vytvo�en v�choz� doru�ovatel: login 'znamy', heslo 'neheslo'.", MESG_OK);
                echo message("Byl vytvo�en v�choz� doru�ovatel: login 'postman', heslo 'abcde'.", MESG_OK);
                echo message("Byl vytvo�en v�choz� doru�ovatel: login 'Rom�a', heslo 'Heslo'.", MESG_OK);
                echo message("D�zazn� doporu�ujeme zm�nit p�ihla�ovac� �daje pro tyto u�ivatele!", MESG_WARNING);
            } catch (Exception $e){
                echo message("Nelze uv�st datab�zi do v�choz�ho stavu (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
            }
        }
    }
    else{
        echo message("Nyn� m��ete prov�d�t spr�vu datab�ze:");
    }

    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>P�epo��tat data dal��ho vyd�n� v�ech tiskovin (denn� v 0:00):</td>';
    echo '<td><input type="hidden" name="invAction" value="recalculate">'
    . '<input type="submit" value="P�epo��tat" class="button"></td></tr></table></form>';
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Vytvo�it faktury za aktu�ln� m�s�c (m�s��n� ka�d� posledn� den):</td>';
    echo '<td><input type="hidden" name="invAction" value="create">'
    . '<input type="submit" value="Vytvo�it" class="button"></td></tr></table></form>';
   
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Odstranit v�echny faktury z datab�ze:</td>';
    echo '<td><input type="hidden" name="invAction" value="delete">'
    . '<input type="submit" value="Odstranit faktury" class="button"></td></tr></table></form>';
    
    echo '<form action="'.$currentURL.'" method="POST"><table><tr>';
    echo '<td>Vymazat v�echna data z datab�ze a uv�st ji do v�choz�ho stavu:</td>';
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
        echo message("M��ete zobrazit v�echny faktury, nebo jen dosud nezaplacen� faktury:");
    }
    
    
    echo '<table class="formtable"><tr>';
    echo '<td>Zobrazit v�echny faktury:</td>';
    echo '<td><form action="'.$currentURL.'" method="POST"><input type="hidden" name="invAction" value="showAll">'
    . '<input type="submit" value="Zobrazit v�echny" class="button" style="width: 100%;"></form></td></tr>';
    
    
    echo '<tr><td>Zobrazit pouze nezaplacen� faktury:</td><td>'
    . '<form action="'.$currentURL.'" method="POST"><input type="hidden" name="invAction" value="showUnpaid">'
    . '<input type="submit" value="Zobrazit nezaplacen�" class="button"></form></td></tr></table>';
    
    echo '</div>';
    
    
    // If data have been submitted, display entries:
    if ($action != ""){
        // Process POST:
        $msg = message("Nyn� m��ete spravovat dosud nezaplacen� faktury:");
        if (isset($_POST['invoiceID'])){
            $invID = s4web(filter_input(INPUT_POST, 'invoiceID'));
            
            // What do we do with the invoice?
            if (isset($_POST['deleteInvoice'])){
                try{
                    deleteInvoice($invID);
                    $msg = message("Faktura #$invID byla odstran�na.", MESG_OK);
                } catch (Exception $e){
                    $msg = message("Nelze odstranit fakturu (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
                }
            }
            elseif (isset($_POST['markInvoicePaid'])){
                try{
                    paymentInvoice($invID, date('Y-m-d', time()));
                    $msg = message("Platba faktury #$invID byla evidov�na", MESG_OK);
                } catch (Exception $e){
                    $msg = message("Nelze evidovat platbu (Po�adavek byl odm�tnut datab�z�.)", MESG_ERROR);
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
            echo '<td class="resulttable">'.$invoice->price.' K�</td>';
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