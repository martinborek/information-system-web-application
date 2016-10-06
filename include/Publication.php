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
class Publication {
    public $id = NULL;
    public $title = NULL;
    public $description = NULL;
    public $price = NULL;
    public $delivDate = NULL;
    public $nextDeliv = NULL;
    
    public function __construct($id, $title, $description, $price, $delivDate, $nextDeliv) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->delivDate = $delivDate;
        $this->nextDeliv = $nextDeliv;
    }
}
