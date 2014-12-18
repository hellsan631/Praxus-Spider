<?php  

session_start();
require_once '../_objects/class.logos.setup.php';
$logos = new logos("../");
require_once ROOT_PATH.CLASS_PATH.'class.database.php';
new DB();

require './logging.php';

$region = $_GET['r'];
$target_url = "http://{$region}.lolesports.com/season3/split2/teams";

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

$winloss = "win-loss";
$teamtitle ="team-title";

$teamname = "";
$wincount = "";
$losscount = "";
$gamecount = "";

$dom = new DOMDocument();
@$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$href = $xpath->evaluate("/html/body//div | /html/body//span");
$teams = 0;

for ($i = 0; $i < $href->length; $i++) {
    
    $data = $href->item($i);
    
    $class = $data->getAttribute('class');
    //echo $class.": <b>".$data->nodeValue."</b><br/><br/>";

    if (strpos($class, $teamtitle) !== false) {

        $teamname = mb_substr($data->nodeValue, 8, -2);
        //echo "<input type='text' value='$teamname' /> <br/>";
        //exit;
    }
    
    if (strpos($class, $winloss) !== false) {
        $strings_wins = explode("Wins", $data->nodeValue);
        $strings_losses = explode("Losses", $strings_wins[1]);
        
        $wincount = (int)$strings_wins[0];
        $losscount = (int)$strings_losses[0];
        $gamecount = $wincount + $losscount;
        
        $teams++;
        
        echo updateTeam($teamname, $wincount, $losscount, $gamecount, $region);
    }
    
    if($teams > 12){
        break;
    }
    
}

function updateTeam($teamname, $wincount, $losses, $gamecount, $region){

    $result = DB::sql("SELECT COUNT(*) FROM team WHERE name = '$teamname'");

    $result = DB::sql_fetch($result);

    $count =  intval($result['COUNT(*)']);

    if($count == 0){

        $query = "INSERT INTO `team` (`name`, `wins`,  `games`,  `region`, `losses`) VALUES ('$teamname', '$wincount', '$gamecount', '$region', '$losses')";

        if(DB::sql($query)){
            return "<b>Added</b> $region $teamname, $wincount : $gamecount <br/><br/>";
        }

        return "Unable to update team";

    }else{

        $result = DB::sql("SELECT * FROM team WHERE name = '$teamname' ");

        $result = DB::sql_fetch($result);

        if($result['games'] < $gamecount){

            $query = "UPDATE `team`
            SET `wins` = '$wincount', `games` = '$gamecount', `losses` = '$losses'
            WHERE `name` = '$teamname'";

            if(DB::sql($query)){

                $trans = md5(rand(0, 1000).strtotime("now"));
                
                if($result['wins'] != $wincount){
                    log_stat('wins', $result['wins'], $wincount, $teamname, 'update team', $trans);
                }
                
                if($result['games'] != $gamecount){
                    log_stat('game_count', $result['games'], $gamecount, $teamname, 'update team', $trans);
                }
                
                if($result['losses'] != $losses){
                    log_stat('losses', $result['losses'], $losses, $teamname, 'update team', $trans);
                }

                return "<b>Updated</b> $region $teamname, $wincount : $gamecount <br/><br/>";
            }

            return "Unable to update $teamname <br/><br/>";

        }

        return "<b>$teamname</b> doesn't need updating. <br/><br/>";
    }
}

?>