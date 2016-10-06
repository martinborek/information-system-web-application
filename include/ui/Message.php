<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Graphical representation of a message to the user.
 * Can be informational, warning, error, ...
 *
 * @author Michal Dobes <michal.dobes.jr@gmail.com>
 */
const MESG_INFO = 0;
const MESG_OK = 1;
const MESG_WARNING = 2;
const MESG_ERROR = 3;

/**
 * Returns HTML message to display on the webpage...
 * @return string HTML representing the message.
 */
function message($messageText, $messageType = MESG_INFO)
{
    $divClass = 'mesg-info';
    $img = 'mesg_info.png';
    $bgcolor = "#b4e6ff";

    switch ($messageType) {
        case MESG_OK:
            $divClass = 'mesg-ok';
            $img = 'mesg_ok.png';
            break;

        case MESG_WARNING:
            $divClass = 'mesg-warning';
            $img = 'mesg_warning.png';
            break;

        case MESG_ERROR:
            $divClass = 'mesg-error';
            $img = 'mesg_error.png';
            break;
    }
    $html = '<div class="mesg" style="background: '.$bgcolor.';">';
    $html .= '<p class="mesg_par"><img class="mesg_img" src="res/'.$img.'">'.$messageText.'</p>';
    $html .= '</div>';
    return $html;
}
