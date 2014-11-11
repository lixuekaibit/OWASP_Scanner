<?php
include "../attackscan/attackscanner.class.php";

$filter = array("attack_filter");
$rooturl = array(0 => "localhost/test1.html");
//$login_data = "LoginForm[username]=demo&LoginForm[password]=demo&LoginForm[rememberMe]=0&yt0=login";
$ascanner = new AttackScanner();
$ascanner->doScan($rooturl,"","all",$filter,2);