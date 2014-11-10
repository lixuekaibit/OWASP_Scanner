<?php
/////////////////////////////////////////////////////////
// PHPCrawlParser
// - class CrawlerParser:
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
include "crawlerfilter.class.php";
class CrawlerParser
{
    //the fetch results temp storage
    var $match = array();
    var $submit_method = "POST"; // default submit method
    var $submit_type = "application/x-www-form-urlencoded"; // default submit type

    function splitURL($url)
    {

        // Get the protocol from URL
        preg_match("/^.{0,10}:\/\//", $url, $match); // Everything from the beginning to "..:\\"
        if (isset($match[0])) $protocol = $match[0];
        else $protocol = "";

        // Get the host from URL
        // (complete, including port and auth login)
        $url_tmp = substr($url, strlen($protocol)); // Cut off the protocol at beginning
        preg_match("/(^[^\/\?#]{1,})/", $url_tmp, $match); // Everything till the first "/", "?" or "#"
        if (isset($match[1])) $host_complete = $match[1];
        else $host_complete = "";

        // Get the path
        $url_tmp = substr($url_tmp, strlen($host_complete)); // Cut off the host at beginning
        preg_match("#^[^?\#]{0,}/#", $url_tmp, $match); // Everything till the last "/", but is not allowed to contain "?" and "#"
        if (isset($match[0])) $path = $match[0];
        else $path = "";

        // Get the file
        $url_tmp = substr($url_tmp, strlen($path)); // Cut off the path at beginning
        preg_match("#^[^?\#]*#", $url_tmp, $match); // Everything till "?" or "#"
        if (isset($match[0])) $file = $match[0];
        else $file = "";

        // Get the query
        $url_tmp = substr($url_tmp, strlen($file)); // Cut off the file at beginning
        preg_match("/^\?[^#]*/", $url_tmp, $match); // Everything from "?" till end or "#"
        if (isset($match[0])) $query = $match[0];
        else $query = "";

        // Split the host (complete) into PORT and HOST and UNAME and PASSWD
        // (i.e. host: "uname:passwd@www.foo.com:81)"

        // 1. Get uname:passwd
        preg_match("#^.*@#", $host_complete, $match); // Everythig till "@"
        if (isset($match[0])) $auth_login = $match[0];

        // 2. Get the clean host
        if (isset($auth_login))
            $host_complete = substr($host_complete, strlen($auth_login)); // Cut off auth_login at the beginning
        preg_match("#[^:]*#", $host_complete, $match); // Everything till ":" or end
        if (isset($match[0])) $host = $match[0];
        else $host = "";

        // 3. Get the port
        preg_match("#:([^:]*$)#", $host_complete, $match); // Everything from the last ":"
        if (isset($match[1])) $port = (int)$match[1];

        // Now get the DOMAIN from the host
        // Host: www.foo.com -> Domain: foo.com
        $parts = @explode(".", $host);
        if (count($parts) <= 2) {
            $domain = $host;
        } else {
            $pos = strpos($host, ".");
            $domain = substr($host, $pos + 1);
        }

        // DEFAULT VALUES for protocol, path, port etc. if not set yet

        // if the protocol is emtpy -> set protocol to "http://"
        if ($protocol == "") $protocol = "http://";

        // if the port is empty -> Set port to 80 or 443
        // depending on the protocol
        if (!isset($port)) {
            if ($protocol == "http://") $port = 80;
            if ($protocol == "https://") $port = 443;
        }

        // If the path is empty -> path is "/"
        if ($path == "") $path = "/";

        // Build return-array
        $url_parts["protocol"] = $protocol;
        $url_parts["host"] = $host;
        $url_parts["path"] = $path;
        $url_parts["file"] = $file;
        $url_parts["query"] = $query;
        $url_parts["domain"] = $domain;
        $url_parts["port"] = $port;

        return $url_parts;
    }

    function visualLogin($url, $login_data)
    {
        $post_fields = $login_data;
        //the saved cookie file
        $cookie_file = tempnam('./tmp', 'cookie');

        $ch = curl_init($url . "?r=site/login");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_exec($ch);
        curl_close($ch);

        //echo file_get_contents($cookie_file);

        //visit the login page
        //$ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        //$contents = curl_exec($ch);
        //curl_close($ch);

        //clear cookie file
        //unlink($cookie_file);
        $login_contents = array("cookie" => $cookie_file);
        return $login_contents;
    }

    //the function to find links in page
    function searchLinks($search_list, $cookie_file, $depth,$filter)
    {
        $root = $search_list[0];
        $links_found = array();
        $searched_links = array();
        $forms_found_all = array();

        for ($i = 0; $i < $depth; $i++) {
            if (count($search_list) > 0) {
                while (list($key, $val) = each($search_list)) {
                    $ch = curl_init($val);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    if ($cookie_file != "") {
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
                    }
                    $contents = curl_exec($ch);
                    curl_close($ch);
                    $links_found = CrawlerParser::expandLinks(CrawlerParser::fetchLinks($contents,$filter), $root);
                    //CrawlerParser::ptest($links_found);
                    $forms_found = CrawlerParser::fetchForms($contents);
                    $forms_found_all[] = $forms_found;
                    $text_found = CrawlerParser::fetchForms($contents);
                    //var_dump($text_found);
                }
            }

            unset($search_list);
            if (count($links_found) > 0) {
                foreach ($links_found as $value) {
                    if (!in_array($value, $searched_links)) {
                        $searched_links[] = $value;
                        $search_list[] = $value;
                    }
                }
            }
        }

        $result = array("links" => $searched_links, "form" => $forms_found_all,"cookie" => $cookie_file);
        return $result;
    }

    function ptest($a)
    {
        foreach ($a as $b) {
            var_dump($b);
            echo "<br>---------<br>";
        }
    }

    //find links from page
    function fetchLinks($source,$filter)
    {
        $match = array();
        $match_part="href|src|url|location|codebase|background|data|profile|open";
        preg_match_all("/<[ ]{0,}a[ \n\r][^<>]{0,}(?<= |\n|\r)"
                        ."(?:".$match_part.")[ \n\r]{0,}=[ \n\r]"
                        ."{0,}[\"|']{0,1}([^\"'>< ]{0,})[^<>]{0,}"
                        .">((?:(?!<[ \n\r]*\/a[ \n\r]*>).)*)<[ \n\r]*\/a[ \n\r]*>/ is", $source, $links);

        // catenate the non-empty matches from the conditional subpattern

        while (list($key, $val) = each($links[1])) {
            if (!empty($val))
            {
                if(count($filter)>0)
                {
                    foreach($filter as $flr){
                        if(CrawlerFilter::$flr($val)){
                            $match[] = $val;
                        }
                    }
                }
            }
        }

        // return the links
        return $match;
    }

    //find forms from page
    function fetchForms($source)
    {
        preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi", $source, $elements);
        // catenate the matches
        $match = implode("\r\n", $elements[0]);

        // return the links
        return $match;
    }

    function fectchText($document)
    {

        // I didn't use preg eval (//e) since that is only available in PHP 4.0.
        // so, list your entities one by one here. I included some of the
        // more common ones.

        $search = array("'<script[^>]*?>.*?</script>'si", // strip out javascript
            "'<[\/\!]*?[^<>]*?>'si", // strip out html tags
            "'([\r\n])[\s]+'", // strip out white space
            "'&(quot|#34|#034|#x22);'i", // replace html entities
            "'&(amp|#38|#038|#x26);'i", // added hexadecimal values
            "'&(lt|#60|#060|#x3c);'i",
            "'&(gt|#62|#062|#x3e);'i",
            "'&(nbsp|#160|#xa0);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&(reg|#174);'i",
            "'&(deg|#176);'i",
            "'&(#39|#039|#x27);'",
            "'&(euro|#8364);'i", // europe
            "'&a(uml|UML);'", // german
            "'&o(uml|UML);'",
            "'&u(uml|UML);'",
            "'&A(uml|UML);'",
            "'&O(uml|UML);'",
            "'&U(uml|UML);'",
            "'&szlig;'i",
        );
        $replace = array("",
            "",
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            chr(174),
            chr(176),
            chr(39),
            chr(128),
            "ä",
            "ö",
            "ü",
            "Ä",
            "Ö",
            "Ü",
            "ß",
        );

        $text = preg_replace($search, $replace, $document);

        return $text;
    }

    function expandLinks($links, $URI)
    {
        preg_match("/^[^\?]+/", $URI, $match);

        $match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|", "", $match[0]);
        $match = preg_replace("|/$|", "", $match);
        $match_part = CrawlerParser::splitURL($URI);
        $match_root =
            $match_part["protocol"] . $match_part["host"];

        $search = array("|^http://" . preg_quote($match_part["host"]) . "|i",
            "|^(\/)|i",
            "|^(?!http://)(?!mailto:)|i",
            "|/\./|",
            "|/[^\/]+/\.\./|"
        );

        $replace = array("",
            $match_root . "/",
            $match . "/",
            "/",
            "/"
        );

        $expandedLinks = preg_replace($search, $replace, $links);

        return $expandedLinks;
    }
}