<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
authenticate(AUTH_LVL_POSTMAN);

echo '<div class=menu>'
        . '<h3 class="menu">Akce:</h3><br>'
//        . '<h4 class="menu">Administr�to�i:</h4><br>'
//        . '<ul class="menu">'
//        . '<li class="menuitem"><a href="?action=newAdmin" class="menulink">P�idat administr�tora</a></li><br>'
//        . '<li class="menuitem"><a href="?action=editAdmin" class="menulink">Spravovat administr�tory</a></li><br>'
//        . '</ul>'

        // Logout button:
        . '<p style="max-width: 150px; margin-left:10px;">U�ivatel: '.$_SESSION['username'].'</p>'
        . '<form action="deauth.php" method="GET"> <input type="submit" value="Odhl�sit se" class="button" style="float:right;"></form>'
        
. '</div>';