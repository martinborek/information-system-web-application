<html>
    <head>
        <meta charset="ISO-8859-2">
        <link rel="stylesheet" type="text/css" href="res/bare.css">
        <title></title>
    </head>
    <body>
        <script type="text/javascript">window.print()</script>
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'include/BackendInterface.php';
require_once 'include/Security.php';
authenticate(AUTH_LVL_POSTMAN);

echo "Seznam pro datum ".date('Y-m-d', time())."<br>";

echo "<ul id='delivlist' class='delivery' style='float: left;'>";
foreach (getDeliveriesForDay($_SESSION['username']) as $city => $zips){
    echo "<li>";
    echo "$city";
    echo "<ul class='delivery'>";
    foreach ($zips as $zip => $streets) {
        echo "<li>";
        echo "PSČ $zip";
        echo "<ul class='delivery' style='margin-bottom: 7px;'>";
        foreach ($streets as $street => $customers) {
            echo "<li class='street'>";
            echo "$street";
            echo "<ul class='delivery'>";
            foreach ($customers as $customer => $publications) {
                echo "<li>";
                echo "$customer";
                echo "<ul class='delivery' style='list-style-type: decimal; margin-bottom: 7px; margin-left: 10px;'>";
                foreach ($publications as $publication) {
                    echo "<li>";
                    echo "$publication</li>";
                }
                echo "</ul></li>";
            }
            echo "</ul></li>";
        }
        echo "</ul></li>";
    }
    echo "</ul></li>";
}
echo "</ul>";
?>

    </body>
</html>