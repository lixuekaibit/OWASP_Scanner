<?php
include "../Crawler/crawler.class.php";
include "../Crawler/crawlerparser.class.php";
include "../AttackImitate/phpattackboard.class.php";
error_reporting(E_ERROR);
$crawler = new Crawler();

//$crawler->setLoginData("LoginForm[username]=demo&LoginForm[password]=demo&LoginForm[rememberMe]=0&yt0=login");

$crawler->setRootUrl("http://localhost/test1.html");
$crawler->setCrawlerDepth(2);
$result = $crawler->startCrawl();
//var_dump($result);

//foreach($result["form"] as $formdata)
//{
//    if($formdata!="")
//    {
//        $xss = new PHPAttackBoard();
//        $xssres = $xss->XSS_Attack_Form($crawler->getRootUrl(),$formdata,$result["cookie"]);
//        var_dump($xssres);
//        $sqlres = $xss->Sql_Inject_Attack_Form($crawler->getRootUrl(),$formdata,$result["cookie"]);
//        var_dump($sqlres);
//    }
//}