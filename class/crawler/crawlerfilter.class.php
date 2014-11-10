<?php
/////////////////////////////////////////////////////////
// PHPCrawl
// - class crawlerfilter:
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
class CrawlerFilter
{
    //attack scanner filter,you can define your own filter here
    function attack_filter($link)
    {
        if(strpos($link,"?")>0)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
}