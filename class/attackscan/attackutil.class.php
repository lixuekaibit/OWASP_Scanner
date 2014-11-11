<?php
/////////////////////////////////////////////////////////
// AttackUtil
// - class AttackUtil:
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
class AttackUtil
{
    private $submit_type = "application/x-www-form-urlencoded"; // default submit type
    private $mime_boundary = ""; // MIME boundary for multipart/form-data submit type

    //the util function to parser form data for the post data use
    function parserForm($forms,$uri)
    {
        //the storage link for the filter
        $result_forms = array();
        if(count($forms)>0)
        {
            foreach($forms as $key => $form_data)
            {
                //var_dump($form_data);
                preg_match("/<form\s+[^>]*?action\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i", $form_data[0], $link);
                if(!empty($link))
                {
                    $base_url = CrawlerParser::expandLinks($link[2],$key);
                }
                preg_match_all("/<input\s+[^>]*?name\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i", $form_data[0], $names);
                foreach($names[2] as $name)
                {
                    $result_forms[$base_url][$name] = "";
                }

            }
        }
        return $result_forms;
    }

    //rebuid the new form post value by given str
    function rebuildForm($formvars,$replace_str)
    {
        $result_forms = array();
        if(count($formvars)>0)
        {
            foreach($formvars as $key => $val)
            {
                $base_url = $key;
                $postdata = self::preparePostBody($val,null,$replace_str);
                $result_forms[$base_url] = $postdata;
            }
        }
        return $result_forms;
    }

    function preparePostBody($formvars, $formfiles, $replace_str)
    {
        settype($formvars, "array");
        settype($formfiles, "array");
        $postdata = '';

        if (count($formvars) == 0 && count($formfiles) == 0)
            return;

        switch ($this->submit_type) {
            case "application/x-www-form-urlencoded":
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= urlencode($key) . "[]=" . $replace_str . "&";
                        }
                    } else
                        $postdata .= urlencode($key) . "=" . $replace_str . "&";
                }
                break;

            case "multipart/form-data":
                $this->mime_boundary = "lixuekai" . md5(uniqid(microtime()));
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= "--" . $this->mime_boundary . "\r\n";
                            $postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
                            $postdata .= "$cur_val\r\n";
                        }
                    } else {
                        $postdata .= "--" . $this->mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
                        $postdata .= "$val\r\n";
                    }
                }

                reset($formfiles);
                while (list($field_name, $file_names) = each($formfiles)) {
                    settype($file_names, "array");
                    while (list(, $file_name) = each($file_names)) {
                        if (!is_readable($file_name)) continue;

                        $fp = fopen($file_name, "r");
                        $file_content = fread($fp, filesize($file_name));
                        fclose($fp);
                        $base_name = basename($file_name);

                        $postdata .= "--" . $this->mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
                        $postdata .= "$file_content\r\n";
                    }
                }
                $postdata .= "--" . $this->mime_boundary . "--\r\n";
                break;
        }

        return $postdata;
    }

    //the util function to parser links for the post data use
    function parserLinks($links)
    {
        //the storage link for the filter
        $result_links = array();
        if(count($links)>0)
        {
            foreach($links as $val)
            {
                $flag_num = strpos($val,"?");
                $root_url = substr($val,0,$flag_num+1);
                $sub_str = substr($val,$flag_num+1);
                $sub_strs = explode("&",$sub_str);
                if(count($sub_strs)>0)
                {
                    foreach($sub_strs as $val)
                    {
                        if(strpos($val,"=")>0)
                        {
                            list($key,$var) = explode("=",$val);
                            $result_links[$root_url][$key] = $var;
                        }
                    }
                }
            }
        }
        return $result_links;
    }

    //rebuid the new link value by given str
    function rebuildLinks($parser_links,$replace_str)
    {
        $result_urls = array();
        if(count($parser_links)>0)
        {
            foreach($parser_links as $key => $val)
            {
                $rebuild_url = $key;
                if(count($val)>0)
                {
                    foreach($val as $k => $v)
                    {
                        $rebuild_url = $rebuild_url.$k."=".$replace_str."&";
                    }
                    $result_urls[] = $rebuild_url;
                }
            }
        }
        return $result_urls;
    }

    //this function is to simulate the http request
    function simulate_http($url,$cookie_file,$post_data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if($post_data != "")
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if($cookie_file != "")
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9; Mozilla Firefox');
        $pg = curl_exec($ch);
        curl_close($ch);
        if($pg){
            return $pg;
        } else {
            return false;
        }
    }

}