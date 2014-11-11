<?php
/////////////////////////////////////////////////////////
// AttackScanner
// - class AttackScanner:
//
// The attack-class util, version 0.1,
// 2014/01/15
//
// Author Lixuekai(lixuekaibit@gmail.com)
//
// This program is free software; you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the
// Free Software Foundation; either version 2 of the License, or
// at your option) any later version.
/////////////////////////////////////////////////////////
class AttackScanner
{
    var $crawler;
    var $attackscanner;

    // Constructor
    function AttackScanner()
    {
        $this->initAttackBoard();
    }

    function initAttackBoard()
    {
        // Include needed class-files
        $classpath = dirname(__FILE__);

        if (!class_exists("Crawler"))
        {
            include_once("../crawler/crawler.class.php");
            $this->crawler = new Crawler();
        }

        if (!class_exists("AttackBoard"))
        {
            include_once($classpath."/attackboard.class.php");
            $this->attackscanner = new AttackBoard();
        }
        $this->attackscanner->printBoard();
    }

    function doScan($url,$login_data,$scanType,$filter,$depth)
    {
        //$crawler->setLoginData("LoginForm[username]=demo&LoginForm[password]=demo&LoginForm[rememberMe]=0&yt0=login");


        $this->crawler->setRootUrl($url);

        $this->crawler->setFetchFilter($filter);

        $this->crawler->setCrawlerDepth($depth);

        $this->crawler->setLoginData($login_data);

        $result = $this->crawler->startCrawl();

        switch($scanType)
        {
            case "sql":
                $scan_result = $this->attackscanner->sql_scanner($result);
                var_dump($scan_result);
                break;
            case "xss":
                $scan_result = $this->attackscanner->xss_scanner($result);
                var_dump($scan_result);
                break;
            case "pmapwn":
                $scan_result = $this->attackscanner->pmapwn_scanner($result["uri"]);
                var_dump($scan_result);
                break;
            case "rfi":
                $scan_result = $this->attackscanner->rfi_scanner($result);
                var_dump($scan_result);
                break;
            case "lfi":
                $scan_result = $this->attackscanner->lfi_scanner($result);
                var_dump($scan_result);
                break;
            case "all":
                $scan_result["sql"] = $this->attackscanner->sql_scanner($result);
                $scan_result["xss"] = $this->attackscanner->xss_scanner($result);
                $scan_result["rfi"] = $this->attackscanner->rfi_scanner($result);
                $scan_result["lfi"] = $this->attackscanner->lfi_scanner($result);
                $scan_result["pmapwn"] = $this->attackscanner->pmapwn_scanner($result["uri"]);
                var_dump($scan_result);
            default:
                break;
        }
    }
}