<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Checks if all arguments are non-empty.
 * @return boolean did all args pass?
 */
function required(){
    foreach (func_get_args() as $arg) {
        if (is_null($arg) || $arg == ""){
            return false;
        }
    }
    
    return true;
}


/**
 * Checks if email is valid.
 * @param type $emailRequired is the field required?
 * @return boolean Is email valid?
 */
function checkEmail($email, $emailRequired=false){
    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
        return true; // email matched -> pass
    }
    else{
        // Email didn't pass.
        if ( (!$emailRequired) && ($email == "")){
            return true; // email is not required and it was empty. That's OK.
        }
        else{
            return false;
        }
    }
    return false; // Program flow should not reach this but just in case...
}

function checkDateFormat($date){
    $res = preg_match('@^(\d\d\d\d)-(\d\d)-(\d\d)$@', $date, $matches);
    if ($res == FALSE or $res == 0){
        return FALSE;
    }
    try{
        $retval = checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1])); // month, day, year
    }catch (ErrorException $exc){
        return false;
    }
    return $retval;
}

function checkZIP($ZIP){
    if ($ZIP == ""){
        return true;
    }
    
    $res = preg_match('@^\d\d\d\d\d$@', $ZIP);
    if ($res == FALSE or $res == 0){
        return FALSE;
    }
    return TRUE;
}

function checkPrice($price){
    $res = preg_match('@^\d+$@', $price);
    if ($res == FALSE or $res == 0){
        return FALSE;
    }
    return TRUE;
}