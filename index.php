<?php

include_once "./admin/header.php";

$time = microtime(TRUE);
$mem = memory_get_usage();

error_reporting(0);

?>

<!DOCTYPE html>
<html>
<head>

<title>Test Run</title>

<link href="./css/index.css" rel="stylesheet" type="text/css" />

</head>
<body>


<div class="row center">

    <?php

    require './logging.php';

    date_default_timezone_set('America/Los_Angeles');

    global $filename;

    $filename =  date('md_h-i_', time()).substr(md5(strtotime("now")), 0, 6).".txt";

    $orgList = getOrgURLList();

    $memberList = array();

    foreach($orgList as $value){


        $memberList += getMembersList($value);

    }

    var_dump($memberList);

    function buildURL($URL, $type = 0){

        if(strlen($URL) < 10){
            //throw new Exception("ID Hidden or Redacted");
        }

        if($type == 0){
            $base_URL = "";

            return $base_URL.$URL."/members";
        }else{

            $base_URL = "";

            return $base_URL.$URL;

        }

    }

    function getMembersList($orgURL, $memberCount = 0, $page = 1, $memberList = array()){

        global $filename;

        $pageSize = 250;

        $target_url = $orgURL."?pagesize=$pageSize&page=$page";

        $xpath = getHTML("https://robertsspaceindustries.com".$target_url);
        $href = $xpath->evaluate("/html/body//a | /html/body//span");

        for ($i = 0; $i < $href->length; $i++) {

            $data = $href->item($i);

            $class = $data->getAttribute('class');

            if($class == "count"){

               $memberCount = $data->nodeValue;

            }

            if(strpos($class,'membercard') !== false) {

                try{

                    $dataTemp = buildURL($data->attributes->getNamedItem("href")->nodeValue, 1);
                    array_push($memberList, $dataTemp);

                }catch(Exception $e){



                }

                //addLog("Adding Member to Array: $dataTemp" , $filename);

            }

        }

        if(($memberCount/$page) < $pageSize){

            return $memberList;

        }else{

            $page++;

            return getMembersList($orgURL, $memberCount, $page, $memberList);

        }

    }

    function getOrgURLList(){

        global $filename;

        $orgList = array();

        $target_url = "https://robertsspaceindustries.com/community/orgs/listing?sort=size_desc&search=&pagesize=1&page=6";

        $xpath = getHTML($target_url);
        $href = $xpath->evaluate("/html/body//a");

        for ($i = 0; $i < $href->length; $i++) {

            $data = $href->item($i);

            $class = $data->getAttribute('class');

            if($class == "trans-03s"){

                $dataTemp = buildURL($data->attributes->getNamedItem("href")->nodeValue);

                array_push($orgList, $dataTemp);
                //addLog("Adding Organization to Array: $dataTemp" , $filename);

            }

        }

        return $orgList;

    }

    function getHTML($target_url){

        $userAgentCollection = ["Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de-AT; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; fr; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-AT; rv:1.7.8) Gecko/20050511 (No IDN)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-AT; rv:1.7.8) Gecko/20050511",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.6) Gecko/20050319",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr-FR; rv:1.7.6) Gecko/20050319",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.6) Gecko/20050319",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.6) Gecko/20050225",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de-AT; rv:1.7.6) Gecko/20050319",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-AT; rv:1.7.6) Gecko/20050414"];

        $userAgent = $userAgentCollection[mt_rand(0,(count($userAgentCollection))-1)];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $html = curl_exec($ch);

        if (!$html)
        {
            echo "ERROR NUMBER: ".curl_errno($ch);
            echo "ERROR: ".curl_error($ch);
            exit;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        return $xpath;

    }


    ?>



</div>

<div class="row alignleft">
    <?php

        $memTwo = memory_get_peak_usage();

        $memTemp = number_format(((memory_get_usage() - $mem) / 1024), 2);
        $memTwoTemp = number_format((($memTwo - memory_get_usage()) / 1024), 2);
        $timeTemp =  number_format((microtime(TRUE) - $time), 6);

        $page = "Undefined";

        echo "Page: $page<br/>";
        echo "Memory 1: $memTemp KB<br/>";
        echo "Memory 2: $memTwoTemp KB <br/>";
        echo "Time: $timeTemp sec";

    ?>
</div>


</body>
</html>