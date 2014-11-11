<?php
/////////////////////////////////////////////////////////
// AttackBoard
// - class AttackBoard:
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
class AttackBoard
{
    function AttackBoard()
    {
        if (!class_exists("AttackUtil"))
        {
            include_once("attackutil.class.php");
        }
    }

    public $borad_info = array(
        "sql" => "Scan SQL Injection vulnerable\n",
        "xss" => "Scan XSS(cross site scripting) vulnerable\n",
        "rfi" => "Scan RFI(remote file include) vulnerable\n",
        "lfi" => "Scan LFI(local file include) vulnerable\n",
        "pmapwn" => "Scan phpMyAdmin code injection vulnerable\n"
    );

    function printBoard(){
        print "============================================================\r\n";
        foreach($this->borad_info as $key=>$val)
        {
            print $key.":".$val;
        }
        print "============================================================\r\n";
    }

    //the lfi scanner
    function lfi_scanner($result)
    {
        $scan_result = array();
        $lfi_error_list = array(
            '../etc/passwd',
            '../../etc/passwd',
            '../../../etc/passwd',
            '../../../../etc/passwd',
            '../../../../../etc/passwd',
            '../../../../../../etc/passwd',
            '../../../../../../../etc/passwd',
            '../../../../../../../../etc/passwd',
            '../../../../../../../../../etc/passwd',
            '../etc/passwd%00',
            '../../etc/passwd%00',
            '../../../etc/passwd%00',
            '../../../../etc/passwd%00',
            '../../../../../etc/passwd%00',
            '../../../../../../etc/passwd%00',
            '../../../../../../../etc/passwd%00',
            '../../../../../../../../etc/passwd%00',
            '../../../../../../../../../etc/passwd%00',
        );

        $attackutil = new AttackUtil();
        if(isset($result["links"]["path"]))
        {
            foreach($lfi_error_list as $lfi_error)
            {
                foreach($result["links"]["path"] as $link)
                {
                    $hr = $attackutil->simulate_http($link.$lfi_error,$result["cookie"],"");
                    if(preg_match("/root:x:/", $hr)) {
                        $scan_result[$lfi_error]["link"][] = $link;
                    }
                }

            }
        }
        return $scan_result;
    }

    function sql_scanner($result)
    {
        $scan_result = array();
        $sql_error_list = array(
            '/You have an error in your SQL/',
            '/Division by zero in/',
            '/supplied argument is not a valid MySQL result resource in/',
            '/Call to a member function/',
            '/Microsoft JET Database/','/ODBC Microsoft Access Driver/',
            '/Microsoft OLE DB Provider for SQL Server/',
            '/Unclosed quotation mark/',
            '/Microsoft OLE DB Provider for Oracle/',
            '/[Macromedia][SQLServer JDBC Driver][SQLServer]Incorrect/',
            '/Incorrect syntax near/'
        );
        $attackutil = new AttackUtil();
        if(isset($result["links"]["quota"]))
        {
            $links_parser = $attackutil->parserLinks($result["links"]["quota"]);
            $links_rebuild = $attackutil->rebuildLinks($links_parser,"=1'");
            foreach($links_rebuild as $link)
            {
                $hr = $attackutil->simulate_http($link,$result["cookie"],"");
                foreach($sql_error_list as $sql_error)
                {
                    if(preg_match($sql_error, $hr)) {
                        $scan_result[$sql_error]["link"][] = $link;
                    }
                }
            }
        }

        $form_parser = $attackutil->parserForm($result["form"],$result["uri"]);
        $form_rebuild = $attackutil->rebuildForm($form_parser,"=1'");
        foreach($form_rebuild as $uri => $form)
        {
            $hr = $attackutil->simulate_http($uri,$result["cookie"],$form);
            foreach($sql_error_list as $sql_error)
            {
                if(preg_match($sql_error, $hr)) {
                    $scan_result[$sql_error]["form"][] = $uri."+".$form;;
                }
            }
        }
        return $scan_result;
    }

    function xss_scanner($result)
    {
        $scan_result = array();
        $attackutil = new AttackUtil();
        if(isset($result["links"]["quota"]))
        {
            $links_parser = $attackutil->parserLinks($result["links"]["quota"]);
            $links_rebuild = $attackutil->rebuildLinks($links_parser,"=<h1>XSS_HERE</h1>");
            foreach($links_rebuild as $link)
            {
                $hr = $attackutil->simulate_http($link,$result["cookie"],"");
                if(preg_match("/<h1>XSS_HERE<\/h1>/", $hr)) {
                    $scan_result["xsserror"]["link"][] = $link;
                }
            }
        }

        $form_parser = $attackutil->parserForm($result["form"],$result["uri"]);
        $form_rebuild = $attackutil->rebuildForm($form_parser,"=<h1>XSS_HERE</h1>");
        foreach($form_rebuild as $uri => $form)
        {
            $hr = $attackutil->simulate_http($uri,$result["cookie"],$form);
            if(preg_match("/<h1>XSS_HERE<\/h1>/", $hr)) {
                $scan_result["xsserror"]["form"][] = $uri."+".$form;
            }
        }
        return $scan_result;
    }

    function rfi_scanner($result)
    {
        $scan_result = array();
        $attackutil = new AttackUtil();
        if(isset($result["links"]["quota"]))
        {
            $links_parser = $attackutil->parserLinks($result["links"]["quota"]);
            $links_rebuild = $attackutil->rebuildLinks($links_parser,"=http://google.com/index.html?");
            foreach($links_rebuild as $link)
            {
                $hr = $attackutil->simulate_http($link,$result["cookie"],"");
                if(preg_match("/Advertising&nbsp;Programs/", $hr)) {
                    $scan_result["rfierror"]["link"][] = $link;
                }
            }
        }

        $form_parser = $attackutil->parserForm($result["form"],$result["uri"]);
        $form_rebuild = $attackutil->rebuildForm($form_parser,"=http://google.com/index.html?");
        foreach($form_rebuild as $uri => $form)
        {
            $hr = $attackutil->simulate_http($uri,$result["cookie"],$form);
            if(preg_match("/Advertising&nbsp;Programs/", $hr)) {
                $scan_result["rfierror"]["form"][] = $uri."+".$form;
            }
        }
        return $scan_result;
    }

    function pmapwn_scanner($uri)
    {
        $scan_result = array();
        $phpadmin_list = array(
            '/phpmyadmin/',
            '/phpMyAdmin/',
            '/PMA/',
            '/pma/',
            '/admin/',
            '/dbadmin/',
            '/mysql/',
            '/myadmin/',
            '/phpmyadmin2/',
            '/phpMyAdmin2/',
            '/phpMyAdmin-2/',
            '/php-my-admin/',
            '/phpMyAdmin-2.2.3/',
            '/phpMyAdmin-2.2.6/',
            '/phpMyAdmin-2.5.1/',
            '/phpMyAdmin-2.5.4/',
            '/phpMyAdmin-2.5.5-rc1/',
            '/phpMyAdmin-2.5.5-rc2/',
            '/phpMyAdmin-2.5.5/',
            '/phpMyAdmin-2.5.5-pl1/',
            '/phpMyAdmin-2.5.6-rc1/',
            '/phpMyAdmin-2.5.6-rc2/',
            '/phpMyAdmin-2.5.6/',
            '/phpMyAdmin-2.5.7/',
            '/phpMyAdmin-2.5.7-pl1/',
            '/phpMyAdmin-2.6.0-alpha/',
            '/phpMyAdmin-2.6.0-alpha2/',
            '/phpMyAdmin-2.6.0-beta1/',
            '/phpMyAdmin-2.6.0-beta2/',
            '/phpMyAdmin-2.6.0-rc1/',
            '/phpMyAdmin-2.6.0-rc2/',
            '/phpMyAdmin-2.6.0-rc3/',
            '/phpMyAdmin-2.6.0/',
            '/phpMyAdmin-2.6.0-pl1/',
            '/phpMyAdmin-2.6.0-pl2/',
            '/phpMyAdmin-2.6.0-pl3/',
            '/phpMyAdmin-2.6.1-rc1/',
            '/phpMyAdmin-2.6.1-rc2/',
            '/phpMyAdmin-2.6.1/',
            '/phpMyAdmin-2.6.1-pl1/',
            '/phpMyAdmin-2.6.1-pl2/',
            '/phpMyAdmin-2.6.1-pl3/',
            '/phpMyAdmin-2.6.2-rc1/',
            '/phpMyAdmin-2.6.2-beta1/',
            '/phpMyAdmin-2.6.2-rc1/',
            '/phpMyAdmin-2.6.2/',
            '/phpMyAdmin-2.6.2-pl1/',
            '/phpMyAdmin-2.6.3/',
            '/phpMyAdmin-2.6.3-rc1/',
            '/phpMyAdmin-2.6.3/',
            '/phpMyAdmin-2.6.3-pl1/',
            '/phpMyAdmin-2.6.4-rc1/',
            '/phpMyAdmin-2.6.4-pl1/',
            '/phpMyAdmin-2.6.4-pl2/',
            '/phpMyAdmin-2.6.4-pl3/',
            '/phpMyAdmin-2.6.4-pl4/',
            '/phpMyAdmin-2.6.4/',
            '/phpMyAdmin-2.7.0-beta1/',
            '/phpMyAdmin-2.7.0-rc1/',
            '/phpMyAdmin-2.7.0-pl1/',
            '/phpMyAdmin-2.7.0-pl2/',
            '/phpMyAdmin-2.7.0/',
            '/phpMyAdmin-2.8.0-beta1/',
            '/phpMyAdmin-2.8.0-rc1/',
            '/phpMyAdmin-2.8.0-rc2/',
            '/phpMyAdmin-2.8.0/',
            '/phpMyAdmin-2.8.0.1/',
            '/phpMyAdmin-2.8.0.2/',
            '/phpMyAdmin-2.8.0.3/',
            '/phpMyAdmin-2.8.0.4/',
            '/phpMyAdmin-2.8.1-rc1/',
            '/phpMyAdmin-2.8.1/',
            '/phpMyAdmin-2.8.2/',
            '/sqlmanager/',
            '/mysqlmanager/',
            '/p/m/a/',
            '/PMA2005/',
            '/pma2005/',
            '/phpmanager/',
            '/php-myadmin/',
            '/phpmy-admin/',
            '/webadmin/',
            '/sqlweb/',
            '/websql/',
            '/webdb/',
            '/mysqladmin/',
            '/mysql-admin/',
        );
        $url_part = CrawlerParser::splitURL($uri);
        $base_url = $url_part["protocol"].$url_part["host"];
        $attackutil = new AttackUtil();
        foreach($phpadmin_list as $path) {
            $hr = $attackutil->simulate_http($base_url.$path,"","");
            if (preg_match("/200 OK/", $hr) and preg_match("/phpMyAdmin/", $hr))
            {
                $scan_result["phpadmin"][] = $base_url.$path;
            }
        }
        return $scan_result;
    }
}