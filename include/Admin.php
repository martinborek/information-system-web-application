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
class Admin {
    public $id = NULL;
    public $login = NULL;
    public $password = NULL;
    public $name = NULL;
    public $surname = NULL;
    public $email = NULL;
    
    function __construct($id, $login, $password, $name, $surname, $email) {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
    }
}
