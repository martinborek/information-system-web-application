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
class Customer {
    public $id = NULL;
    public $name = NULL;
    public $surname = NULL;
    public $city = NULL;
    public $street = NULL;
    public $ZIP = NULL;
    public $email = NULL;
    public $postmanID;
    public $inactiveSince = NULL;
    public $inactiveTill = NULL;
    
    function __construct($id, $name, $surname, $email, $city, $street, $ZIP, $postmanID,
                                $inactiveSince=NULL, $inactiveTill=NULL) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        
        $this->city = $city;
        $this->street = $street;
        $this->ZIP = $ZIP;
        
        $this->postmanID = $postmanID;
        $this->inactiveSince = $inactiveSince;
        $this->inactiveTill = $inactiveTill;
    }
}
