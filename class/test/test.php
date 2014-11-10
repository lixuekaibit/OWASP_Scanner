<?php
include "../crawler/crawler.class.php";
include "../crawler/crawlerparser.class.php";
include "../attackscan/attackutil.class.php";
$crawler = new Crawler();

//$crawler->setLoginData("LoginForm[username]=demo&LoginForm[password]=demo&LoginForm[rememberMe]=0&yt0=login");

$rooturl = array(0 => "http://www.baidu.com");
$crawler->setRootUrl($rooturl);

$filter = array("attack_filter");
$crawler->setFetchFilter($filter);

$crawler->setCrawlerDepth(2);

$result = $crawler->startCrawl();

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

$attackutil = new AttackUtil();

$links_parser = $attackutil->parserLinks($result["links"]);
$links_rebuild = $attackutil->rebuildLinks($links_parser,"111");
var_dump($links_rebuild);

$form_parser = $attackutil->parserForm($result["form"]);
$form_rebuild = $attackutil->rebuildForm($form_parser,"111");
var_dump($form_rebuild);

unlink("../crawler/".$result["cookie"]);



