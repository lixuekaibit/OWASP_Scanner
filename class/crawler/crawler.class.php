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
    private $root_url = array();

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

    private $fetch_filter = array();

    /**
     * @return array
     */
    public function getFetchFilter()
    {
        return $this->fetch_filter;
    }

    /**
     * @param array $fetch_filter
     */
    public function setFetchFilter($fetch_filter)
    {
        $this->fetch_filter = $fetch_filter;
    }

    //if needed visual login,set this
    private $login_data = "";

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
            include_once($classpath."/crawlerparser.class.php");
        }

        // report-class
        if (!class_exists("CrawlerReport"))
        {
            include_once($classpath."/crawlerreport.class.php");

            // Initiate a new PageReport
            //$this->pageReport = new CrawlerReport();
        }
    }

    //crawler main function
    function startCrawl()
    {
        $search_list = $this->getRootUrl();
        $cookie_file = "";

        //if needed login
        if($this->getLoginData() != "")
        {
            //visual login && get cookie
            $cookie_file = CrawlerParser::visualLogin($this->getRootUrl()[0],$this->getLoginData())["cookie"];
        }
        $result = CrawlerParser::searchLinks($search_list,$cookie_file,$this->getCrawlerDepth(),$this->getFetchFilter());
        return $result;
    }
}