<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Publication
 *
 * @author Michal Dobes <michal.dobes.jr@gmail.com>
 */
class Invoice {
    public $id = NULL;
    public $price = NULL;
    public $dateCreated = NULL;
    public $dateDue = NULL;
    public $datePaid = NULL;
    public $customerID = NULL;
    
    private $customer = NULL;
    
    public function __construct($id, $price, $dateCreated, $dateDue, $datePaid, $customerID) {
        $this->id = $id;
        $this->price = $price;
        $this->dateCreated = $dateCreated;
        $this->dateDue = $dateDue;
        $this->datePaid = $datePaid;
        $this->customerID = $customerID;
    }
    
    public function getCustomer(){
        if ($this->customer == NULL){
            try{
                $a = searchCustomers($this->customerID);
                $this->customer = $a[0];
            } catch (Exception $e){
                // Problem with DB, set to unknown:
                $this->customer = new Customer("", "???","???","???","???","???","???","???","???");
            }
        }
        return $this->customer;
    }
    
    public function isPaid(){
        if ( ($this->datePaid == NULL) || ($this->datePaid == "0000-00-00")){
            return false;
        }
        return true;
    }
}
