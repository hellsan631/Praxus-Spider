<?php 

session_start();
require_once '../_objects/class.logos.setup.php';
$logos = new logos("../");
require_once ROOT_PATH.CLASS_PATH.'class.database.php';
new DB();

require './logging.php';

$region = $_GET['r'];
$target_url = "http://{$region}.lolesports.com/season3/split2/stats";

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

$player = array(
        'link_player_field' => '',
        'role_field' => '',
        'games' => '',
        'kills' => '',
        'deaths' => '',
        'assists' => '',
        'gpm' => '',
        'minion_kills' => '',
        'total_gold' => '',
        'team_id' => ''
    );

$teamname = '';

$dom = new DOMDocument();
@$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$href = $xpath->evaluate("/html/body//td | /html/body//img");
$players = 0;
for ($i = 0; $i < $href->length; $i++) {

    $data = $href->item($i);

    $class = $data->getAttribute('class');
    //echo $class."<br/>";

    if (strpos($class,'link_team_image') !== false) {

        $players++;
         
        $data = $href->item(++$i);
        //echo "link_team_image: "."<input type='text' value='".$data->getAttribute('alt')."' /> <br/>";
        
        $player['team_id'] = getTeamID($data->getAttribute('alt'));
         
        for($k = 0; $k < 10; $k++){
            $data = $href->item(++$i);
            $player[$data->getAttribute('class')] = $data->nodeValue;
        }
        
        //echo "SELECT COUNT(*) FROM player WHERE name = '{$player['link_player_field']}'<br/>";
        echo updatePlayer($player);
        
        //echo var_dump($player);
        
        //echo "<br/><br/>";
    }

    //echo "URL: ".$data->nodeValue."<br/>";
    //echo "DATA: ".$data->getAttribute('class')."<br/>";
    //echo "<br/>";

    if($players > 40){
        break;
    }

}

function getTeamID($teamname){
    
    $result = DB::sql("SELECT * FROM team WHERE name = '$teamname' ");
    
    $result = DB::sql_fetch($result);
    
    return $result['id'];
    
}

function updatePlayer($player){

    $result = DB::sql("SELECT COUNT(*) FROM player WHERE ign = '{$player['link_player_field']}'");

    $result = DB::sql_fetch($result);

    $count =  intval($result['COUNT(*)']);

    if($count == 0){

        $query = "INSERT INTO `player` (`ign`, `team_id`,  `role`,  `games`, `kills`, `deaths`, `assists`, `gpm`, `minions`, `total_gold`) 
        VALUES ('{$player['link_player_field']}', '{$player['team_id']}', '{$player['role_field']}', '{$player['games']}', 
        '{$player['kills']}', '{$player['deaths']}', '{$player['assists']}', '{$player['gpm']}', '{$player['minion_kills']}', '{$player['total_gold']}')";

        if(DB::sql($query)){
            return "<b>Added: {$player['link_player_field']}</b><br/>
            Team_ID: {$player['team_id']}<br/>
            Role: {$player['role_field']}<br/>
            Kills: {$player['kills']}<br/>
            Deaths: {$player['deaths']}<br/>
            Assits: {$player['assists']}<br/>
            GPM: {$player['gpm']}<br/>
            Minions: {$player['minion_kills']}<br/>
            Total_Gold: {$player['total_gold']}<br/><br/>";
        }

        return "Unable to update {$player['link_player_field']}. <br/><br/>";

    }else{

        $result = DB::sql("SELECT * FROM player WHERE ign = '{$player['link_player_field']}'");

        $result = DB::sql_fetch($result);

        if($result['games'] < $player['games']){

            $query = "UPDATE `team`
            SET 
            `games` = '{$player['games']}', 
            `kills` = '{$player['kills']}', 
            `deaths` = '{$player['deaths']}',
            `assists` = '{$player['assists']}', 
            `gpm` = '{$player['gpm']}', 
            `minions` = '{$player['minion_kills']}', 
            `total_gold` = '{$player['total_gold']}'
            WHERE `ign` = '{$player['link_player_field']}'";

            if(DB::sql($query)){

                $trans = md5(rand(0, 1000).strtotime("now"));
                
                if($result['games'] != $player['games']){
                    log_stat('games', $result['games'], $player['games'], $result['ign'], 'update player', $trans);
                }
                
                if($result['kills'] != $player['kills']){
                    log_stat('kills', $result['kills'], $player['kills'], $result['ign'], 'update player', $trans);
                }
                
                if($result['deaths'] != $player['deaths']){
                    log_stat('deaths', $result['deaths'], $player['deaths'], $result['ign'], 'update player', $trans);
                }
                
                if($result['assists'] != $player['assists']){
                    log_stat('assists', $result['assists'], $player['assists'], $result['ign'], 'update player', $trans);
                }
                
                if($result['gpm'] != $player['gpm']){
                    log_stat('gpm', $result['gpm'], $player['gpm'], $result['ign'], 'update player', $trans);
                }
                
                if($result['total_gold'] != $player['total_gold']){
                    log_stat('total_gold', $result['total_gold'], $player['total_gold'], $result['ign'], 'update player', $trans);
                }

                return "<b>Updated</b> {$player['link_player_field']}<br/><br/>";
            }

            return "Unable to update player {$player['link_player_field']}<br/><br/>";

        }

        return "<b>{$player['link_player_field']}</b> doesn't need updating.<br/><br/>";
    }
}

?>