<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
authenticate(AUTH_LVL_ADMIN);

echo '<div class=menu>'
        . '<h3 class="menu">Akce:</h3><br>'
        . '<h4 class="menu">Administrátoøi:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newAdmin" class="menulink">Pøidat administrátora</a></li><br>'
        . '<li class="menuitem"><a href="?action=editAdmin" class="menulink">Spravovat administrátory</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Zákazníci:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newCustomer" class="menulink">Pøidat zákazníka</a></li><br>'
        . '<li class="menuitem"><a href="?action=searchCustomer" class="menulink">Spravovat zákazníky</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Doruèovatelé:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newPostman" class="menulink">Pøidat doruèovatele</a></li><br>'
        . '<li class="menuitem"><a href="?action=editPostman" class="menulink">Spravovat doruèovatele</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Tiskoviny:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newPublication" class="menulink">Pøidat tiskovinu</a></li><br>'
        . '<li class="menuitem"><a href="?action=editPublication" class="menulink">Spravovat tiskoviny</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Odbìry:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newSubscription" class="menulink">Pøidat odbìr</a></li><br>'
        . '<li class="menuitem"><a href="?action=manageSubscriptions" class="menulink">Spravovat odbìry</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Faktury:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=editInvoice" class="menulink">Spravovat faktury</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Systém:</h4><br>'
        . '<ul class="menu">'    
        . '<li class="menuitem"><a href="?action=newInvoice" class="menulink" style="color: #FF0000;">Správa databáze</a></li><br>'
        . '</ul>'
        
        // Logout button:
        . '<p style="max-width: 150px; margin-left:10px;">U¾ivatel: '.$_SESSION['username'].'</p>'
        . '<form action="deauth.php" method="GET"> <input type="submit" value="Odhlásit se" class="button" style="float:right;"></form>'
. '</div>';