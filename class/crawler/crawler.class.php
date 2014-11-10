<?php
/////////////////////////////////////////////////////////
// PHPCrawl
// - class crawler:
//
// The main-class, version 0.1,
// 2014/01/15
//
// Author Lixuekai(lixuekaibit@gmail.com)
//
// This program is free software; you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the
// Free Software Foundation; either version 2 of the License, or
// at your option) any later version.
/////////////////////////////////////////////////////////
class Crawler
{
    //Version
    public $version = "0.1";

    //root url to be crawled
    private $root_url = "";

    /**
     * @return string
     */
    public function getRootUrl()
    {
        return $this->root_url;
    }

    /**
     * @param string $root_url
     */
    public function setRootUrl($root_url)
    {
        $this->root_url = $root_url;
    }

    //the crawl depth
    private $crawler_depth;

    /**
     * @return mixed
     */
    public function getCrawlerDepth()
    {
        return $this->crawler_depth;
    }

    /**
     * @param mixed $crawler_depth
     */
    public function setCrawlerDepth($crawler_depth)
    {
        $this->crawler_depth = $crawler_depth;
    }

    private $not_fetch_match = array();

    /**
     * @return array
     */
    public function getNotFetchMatch()
    {
        return $this->not_fetch_match;
    }

    /**
     * @param array $not_fetch_match
     */
    public function setNotFetchMatch($not_fetch_match)
    {
        $this->not_fetch_match = $not_fetch_match;
    }

    //if needed visual login,set this
    private $login_data = array("name"=>"","passwd"=>"","origURL"=>"","domain"=>"");

    /**
     * @return array
     */
    public function getLoginData()
    {
        return $this->login_data;
    }

    /**
     * @param array $login_data
     */
    public function setLoginData($login_data)
    {
        $this->login_data = $login_data;
    }

    //crawler report class
    private $page_report;

    /**
     * @return mixed
     */
    public function getPageReport()
    {
        return $this->page_report;
    }

    /**
     * @param mixed $page_report
     */
    public function setPageReport($page_report)
    {
        $this->page_report = $page_report;
    }

    //walking links array to be followed to fetch
    public $urls_to_crawl = array();

    // Constructor
    function Crawler()
    {
        $this->initCrawler();
    }

    function initCrawler()
    {
        // Include needed class-files
        $classpath = dirname(__FILE__);

        // Parser-class
        if (!class_exists("CrawlerParser"))
        {
            include_once($classpath."/crawlerparser.php");
        }

        // report-class
        if (!class_exists("CrawlerReport"))
        {
            include_once($classpath."/crawlerreport.php");

            // Initiate a new PageReport
            //$this->pageReport = new CrawlerReport();
        }
    }

    //crawler main function
    function startCrawl()
    {
        // Init, split given URL into host, port, path and file a.s.o.
        $url_parts = CrawlerParser::splitURL($this->getRootUrl());
        $search_list[] = $this->getRootUrl();
        $cookie_file = "";

        //if needed login
        if(count(array_filter($this->getLoginData()))>0)
        {
            //visual login && get cookie
            $cookie_file = CrawlerParser::visualLogin($this->getRootUrl(),$this->getLoginData())["cookie"];
        }
        $result = CrawlerParser::searchLinks($this->getRootUrl(),$cookie_file,$this->getCrawlerDepth());
        return $result;
    }
}