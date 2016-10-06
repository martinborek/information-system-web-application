<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'include/Security.php';
authenticate(AUTH_LVL_ADMIN);

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

    // ADMINS:
    case 'newAdmin':
        echo newAdmin();
        break;
    case 'editAdmin':
        echo editAdmin();
        break;
    
    // CUSTOMERS:
    case 'searchCustomer':
        echo searchPromptCustomer(0, DISP_MODE_DISPLAY);
        break;
    case 'newCustomer':
        echo newCustomer();
        break;

    // POSTMEN:
    case 'newPostman':
        echo newPostman();
        break;
    case 'editPostman':
        echo editPostman();
        break;
    
    // PUBLICATIONS:
    case 'newPublication':
        echo newPublication();
        break;
    case 'editPublication':
        echo editPublication();
        break;
    
    // SUBSCRIPTIONS:
    case 'newSubscription':
        echo newSubscription();
        break;
    case 'manageSubscriptions':
        echo editSubscription();
        break;
    
    // INVOICES:
    case 'newInvoice':
        echo newInvoice();
        break;
    case 'editInvoice':
        echo editInvoice();
        break;
    
    default:
        echo message("Nepodporovaná akce", MESG_WARNING);
        break;
}

