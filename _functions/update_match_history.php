<?php 

session_start();
require_once '../_objects/class.logos.setup.php';
$logos = new logos("../");
require_once ROOT_PATH.CLASS_PATH.'class.database.php';
new DB();

require './logging.php';

date_default_timezone_set('America/Los_Angeles');

$region = $_GET['r'];

for($i = 1; $i < 10; $i++){
    
    pageLoop($i, $region);
    
}

echo "done!";

function pageLoop($pagecount, $region){
    
    $regionlow = strtolower($region);
    $target_url = "http://{$regionlow}.lolesports.com/season3/split2/matches/{$pagecount}";
    
    $userAgent = 'LeagueStatCollection';
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $html = curl_exec($ch);
    
    if (!$html)
    {
        echo "ERROR NUMBER: ".curl_errno($ch);
        echo "ERROR: ".curl_error($ch);
        exit;
    }
    
    $match = array(
            'contestant-1' => '',
            'contestant-2' => '',
            'winner' => 0,
            'contestant-1_id' => '',
            'contestant-2_id' => '',
            'date' => '',
            'riot_id' => '',
            'url' => '',
            'region' => ''
    );
    
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    $xpath = new DOMXPath($dom);
    $href = $xpath->evaluate("/html/body//div | /html/body//span | /html/body//a | /html/body//img");
    $players = 0;
    for ($i = 0; $i < $href->length; $i++) {
    
        $data = $href->item($i);
    
        $class = $data->getAttribute('class');
    
        if (strpos($class,'match-date') !== false) {//gets the date the match was held on
            
            if($regionlow == "euw"){
                
                $date = date_parse_from_format('D d/m - H:i p', $data->nodeValue);
                
                $match['date'] = strtotime("{$date['month']}/{$date['day']} {$date['hour']}:{$date['minute']} CEST");
                
            }else{
                $match['date'] = strtotime(str_replace("-", " ", $data->nodeValue));
            }
    
        }
        
        if (strpos($class,'winner team-logo') !== false){ //gets the winner
                
                $i = $i+2;
                $data = $href->item($i);
                
                $match['winner'] = $data->getAttribute('src');
                
                //echo $match['winner']."<br/><br/>";  
        }
            
        if (strpos($class,'match-title') !== false) { //gets the two teams facing off
            
            $contestants = explode("vs", $data->nodeValue);
            
            $match['contestant-1'] = mb_substr($contestants[0], 8, -1);
            $match['contestant-2'] = mb_substr($contestants[1], 1, -2);
    
        }
        
        if (strpos($data->getAttribute('href'),'/tourney/match/') !== false) { //gets the url information
            if (strpos($data->nodeValue,'Match Details') !== false) {
                
                $match['url'] = $data->getAttribute('href');//http://euw.lolesports.com/tourney/match/1477
                $id = explode("http://{$regionlow}.lolesports.com/tourney/match/", $data->getAttribute('href'));
                $match['riot_id'] = $id[1];
                $match['region'] = $region;
                
                //echo var_dump($match)."<br/><br/>";  
             
                echo updateMatch($match);
    
            }
        }
    }
}

function getTeamID($teamname){
    
    $result = DB::sql("SELECT * FROM team WHERE name = '$teamname' ");
    
    $result = DB::sql_fetch($result);
    
    return $result['id'];
    
}

function updateMatch($match){

    $result = DB::sql("SELECT COUNT(*) FROM `match` WHERE riot_id = {$match['riot_id']}");

    $result = DB::sql_fetch($result);

    $count =  intval($result['COUNT(*)']);
    
    $match['contestant-1_id'] = getTeamID($match['contestant-1']);
    $match['contestant-2_id'] = getTeamID($match['contestant-2']);
    
    $result = DB::sql("SELECT `logo_name` FROM team WHERE id = '{$match['contestant-1_id']}' ");
    $result = DB::sql_fetch($result);
    
    if(strpos($match['winner'], $result['logo_name'])){
        $match['winner'] = (int)$match['contestant-1_id'];
    }
    
    $result = DB::sql("SELECT `logo_name` FROM team WHERE id = '{$match['contestant-2_id']}' ");
    $result = DB::sql_fetch($result);
    
    if(strpos($match['winner'], $result['logo_name'])){
        $match['winner'] = (int)$match['contestant-2_id'];
    }
    
    if(!is_int($match['winner'])){
        $match['winner'] = 0;
    }

    if($count == 0){
        
        $query = "INSERT INTO `match` (`bteam_id`, `pteam_id`,  `winner_id`,  `riot_id`, `date`, `link`, `region`)
        VALUES ({$match['contestant-1_id']}, {$match['contestant-2_id']}, {$match['winner']}, {$match['riot_id']}, FROM_UNIXTIME({$match['date']}), '{$match['url']}', '{$match['region']}')";

        if(DB::sql($query)){
            return "<b>Added {$match['region']}: {$match['contestant-1_id']} vs {$match['contestant-2_id']}</b><br/>
            Winner: {$match['winner']}<br/>
            Date: {$match['date']}<br/>
            URL: {$match['url']} : {$match['riot_id']}<br/><br/>";
        }

        return "Unable to create match {$match['riot_id']}. <br/><br/>";

    }else{

        $result = DB::sql("SELECT * FROM `match` WHERE riot_id = {$match['riot_id']}");
        
        $result = DB::sql_fetch($result);
        
        //echo "db: {$result['winnerid']} : {$match['winner']}<br/>";

        if((int)$result['winner_id'] === 0 && $match['winner'] !== 0){

            $query = "UPDATE `match` SET
            `winner_id` = {$match['winner']},
            `date` = FROM_UNIXTIME({$match['date']})
            WHERE `riot_id` = '{$match['riot_id']}'";

            if(DB::sql($query)){

                $trans = md5(rand(0, 1000).strtotime("now"));
    
                if($result['date'] != $match['date']){
                    log_stat('date', $result['date'], $match['date'], $result['riot_id'], 'update match', $trans);
                }
    
                if($result['winner_id'] != $match['winner']){
                    log_stat('winner_id', $result['winner_id'], $match['winner'], $result['riot_id'], 'update match', $trans);
                }
    
                return "<b>Updated Match</b> {$match['riot_id']}<br/><br/>";
            }
    
            return "Unable to update match {$match['riot_id']}<br/><br/>";

        }

        return "<b>Match: {$match['riot_id']}</b> doesn't need updating.<br/><br/>";
    }
    
    return "error";
}

?>