<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Customer
 *
 * @author Michal Dobes <michal.dobes.jr@gmail.com>
 */
class Subscription {
    public $id = NULL;
    public $pubID = NULL;
    public $cusID = NULL;
    
    private $customer = NULL;
    
    function __construct($id, $pubID, $cusID) {
        $this->id = $id;
        $this->pubID = $pubID;
        $this->cusID = $cusID;
    }
    
    function getPublication(){
        $a = searchPublications($this->pubID);
        return $a[0];
    }
    
    function getCustomer(){
        if ($this->customer == NULL){
            try{
                $a = searchCustomers($this->cusID);
                $this->customer = $a[0];
            } catch (Exception $e){
                // Problem with DB, set to unknown:
                $this->customer = new Customer("", "???","???","???","???","???","???","???","???");
            }
        }
        return $this->customer;
    }
}
