<?php 

session_start();
require_once '../_objects/class.logos.setup.php';
$logos = new logos("../");
require_once ROOT_PATH.CLASS_PATH.'class.database.php';
new DB();

require './logging.php';

date_default_timezone_set('America/Los_Angeles');

$region = $_GET['r'];

//for($i = 1; $i < 1; $i++){
    
    pageLoop(1253, $region);
    
//}

echo "<br/><br/>done!";

function pageLoop($matchid, $region){
    
    $regionlow = strtolower($region);
    $target_url = "http://{$regionlow}.lolesports.com/tourney/match/{$matchid}";
    
    //echo $target_url."<br/><br/>";
    
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
    
    $unit_match = array(
            'matchid' => $matchid,
            'playerlink' => '',
            'playername' => '',
            'teamname' => '',
            'unitname' => '',
            'kills' => '',
            'deaths' => '',
            'assists' => '',
            'minions' => '',
            'gold' => '',
            'win' => '',
            'gpm' => ''
    );
    
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    $xpath = new DOMXPath($dom);
    $href = $xpath->evaluate("//script");
    $players = 0;
    
    for ($i = 0; $i < $href->length; $i++) {
    
        $data = $href->item($i);
    
        $class = $data->getAttribute('class');
        
        //echo ": {$data->tagName}<br/>: $class<br/>: {}<br/>: {$data->getAttribute('alt')}<br/><br/>";

        if (strpos($data->nodeValue,'jQuery.extend(Drupal.settings,') !== false) {
                
            $tmp = str_replace("jQuery.extend(Drupal.settings,", "", $data->nodeValue);
            $tmp = mb_substr($tmp, 0, -2);
            $tmp = json_decode($tmp);
            
            foreach ($tmp->esportsDataDump->matchDataDump as $keya => $valuea){
                $tmp1 = $tmp->esportsDataDump->matchDataDump->{$keya}; //accesses inter match id
                
                foreach ($tmp1 as $keyb => $valueb){
                    $tmp2 = $tmp1->{$keyb}; //accesses inter team id
                    
                    foreach ($tmp2 as $keyc => $valuec){
                        $tmp3 =  $tmp2->{$keyc}; //accesses inter player id
                        
                        foreach ($tmp3 as $keyd => $valued){
                            
                            if (strpos($keyd,'link player image') !== false) {
                                $playerlink = htmlspecialchars($valued);
                                $playerlink = str_replace('&lt;a href=&quot;', '', $playerlink);
                                $playerlink = explode('&quot;&gt;&lt;img src=', $playerlink);
                                $unit_match['playerlink'] = $playerlink[0];
                            }
                            
                            if (strpos($keyd,'player field') !== false) {
                                $unit_match['playername'] = $valued;
                            }
                            
                            if (strpos($keyd,'player_fullname') !== false) {
                                $unit_match['fullname'] = $valued;
                            }
                            
                            if ($keyd === 'kills') {
                                $unit_match['kills'] = $valued;
                            }
                            
                            if (strpos($keyd,'deaths') !== false) {
                                $unit_match['deaths'] = $valued;
                            }
                            
                            if (strpos($keyd,'assists') !== false) {
                                $unit_match['assists'] = $valued;
                            }
                            
                            if (strpos($keyd,'total_gold') !== false) {
                                $unit_match['gold'] = $valued;
                            }
                            
                            if (strpos($keyd,'minion_kills') !== false) {
                                $unit_match['minions'] = $valued;
                            }
                            
                            if (strpos($keyd,'win') !== false) {
                                $unit_match['win'] = $valued;
                            }
                            
                            if ($keyd === 'champion') {
                                $unit_match['champion'] = $valued[0];
                            }

                        }
                        
                        $players++;
                        echo updateUnitMatch($unit_match)."<br/><br/>";

                    }
                }
            }
        }
        
        if($players < 10){
            //add this match to list that needs to be updated.
        }
  
    }
}

function getTeamID($teamname){
    
    $result = DB::sql("SELECT * FROM team WHERE name = '$teamname' ");
    
    $result = DB::sql_fetch($result);
    
    return $result['id'];
    
}

function getPlayer($playername){
    $result = DB::sql("SELECT * FROM player WHERE ign = '$playername' ");
    
    $result = DB::sql_fetch($result);
    
    return $result;
}

function updateUnitMatch($unit_match){
    
   $player = getPlayer($unit_match['playername']);
   
   $unit_match['player_id'] = $player['id'];
   $unit_match['team_id'] = $player['team_id'];

   return var_dump($unit_match);

}

?>