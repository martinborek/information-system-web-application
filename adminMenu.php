<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
authenticate(AUTH_LVL_ADMIN);

echo '<div class=menu>'
        . '<h3 class="menu">Akce:</h3><br>'
        . '<h4 class="menu">Administr�to�i:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newAdmin" class="menulink">P�idat administr�tora</a></li><br>'
        . '<li class="menuitem"><a href="?action=editAdmin" class="menulink">Spravovat administr�tory</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Z�kazn�ci:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newCustomer" class="menulink">P�idat z�kazn�ka</a></li><br>'
        . '<li class="menuitem"><a href="?action=searchCustomer" class="menulink">Spravovat z�kazn�ky</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Doru�ovatel�:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newPostman" class="menulink">P�idat doru�ovatele</a></li><br>'
        . '<li class="menuitem"><a href="?action=editPostman" class="menulink">Spravovat doru�ovatele</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Tiskoviny:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newPublication" class="menulink">P�idat tiskovinu</a></li><br>'
        . '<li class="menuitem"><a href="?action=editPublication" class="menulink">Spravovat tiskoviny</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Odb�ry:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=newSubscription" class="menulink">P�idat odb�r</a></li><br>'
        . '<li class="menuitem"><a href="?action=manageSubscriptions" class="menulink">Spravovat odb�ry</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Faktury:</h4><br>'
        . '<ul class="menu">'
        . '<li class="menuitem"><a href="?action=editInvoice" class="menulink">Spravovat faktury</a></li><br>'
        . '</ul>'
        
        . '<h4 class="menu">Syst�m:</h4><br>'
        . '<ul class="menu">'    
        . '<li class="menuitem"><a href="?action=newInvoice" class="menulink" style="color: #FF0000;">Spr�va datab�ze</a></li><br>'
        . '</ul>'
        
        // Logout button:
        . '<p style="max-width: 150px; margin-left:10px;">U�ivatel: '.$_SESSION['username'].'</p>'
        . '<form action="deauth.php" method="GET"> <input type="submit" value="Odhl�sit se" class="button" style="float:right;"></form>'
. '</div>';