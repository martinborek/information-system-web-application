<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'include/Security.php';
authenticate(AUTH_LVL_POSTMAN);


require_once 'include/ui/Customers.php';
require_once 'include/ui/Search.php'; // Search for customers
require_once 'include/ui/Postmen.php';
require_once 'include/ui/Admins.php';
require_once 'include/ui/Publications.php';
require_once 'include/ui/Subscriptions.php';
require_once 'include/ui/Invoices.php';

$action=  filter_input(INPUT_GET, "action");

switch ($action) {
    case 'loggedin': // Just logged in
        echo message("Byli jste úspì¹nì pøihlá¹eni do systému.", MESG_OK);
        break;
    case '': // No action
        break;
    
    default:
        echo message("Nepodporovaná akce", MESG_WARNING);
        break;
}

// Display deliveries:
echo "<div class='box' style='text-align: left;'>";

echo "<button class='button' style='float: left; margin: 5px;' onclick='window.open(&quot;postmanPrint.php&quot;);'>Vytisknout seznam</button>";

$date = date('Y-m-d', time()); // today
echo "<ul id='delivlist' class='delivery' style='float: left;'>";
foreach (getDeliveriesForDay($_SESSION['username']) as $city => $zips){
    echo "<li>";
    echo "$city";
    echo "<ul class='delivery'>";
    foreach ($zips as $zip => $streets) {
        echo "<li>";
        echo "PSÈ $zip";
        echo "<ul class='delivery' style='margin-bottom: 7px;'>";
        foreach ($streets as $street => $customers) {
            echo "<li class='street'>";
            echo "$street";
            echo " (<a target='blank$street$city' href='https://www.google.cz/maps/place/$street, $city/'>mapa</a>)";
            echo "<ul class='delivery'>";
            foreach ($customers as $customer => $publications) {
                echo "<li>";
                echo "$customer";
                echo "<ul class='delivery' style='list-style-type: decimal; margin-bottom: 7px; margin-left: 10px;'>";
                foreach ($publications as $publication) {
                    echo "<li>";
                    echo "$publication</li>";
                }
                echo "</ul></li>";
            }
            echo "</ul></li>";
        }
        echo "</ul></li>";
    }
    echo "</ul></li>";
}
echo "</ul>";

echo "</div>";
