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
class Postman {
    public $id = NULL;
    public $login = NULL;
    public $password = NULL;
    public $name = NULL;
    public $surname = NULL;
    public $city = NULL;
    public $street = NULL;
    public $ZIP = NULL;
    public $email = NULL;
    
    function __construct($id, $login, $password, $name, $surname, $email, $city, $street, $ZIP) {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        
        $this->city = $city;
        $this->street = $street;
        if($ZIP != 0){
            $this->ZIP = $ZIP;
        }else{
            $this->ZIP = "";
        }
    }
}
