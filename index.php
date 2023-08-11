<?php

error_reporting(0);

header('Content-type: application/json;');
class wereWolf
{
    public static function sendRequest($method, $playerid)
    {
        $cp = curl_init('https://www.tgwerewolf.com/Stats/' . $method . '/?pid=' . $playerid);
        curl_setopt_array($cp, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1'
        ]);
        return json_decode(curl_exec($cp));
        curl_close($cp);
    }
    public static function getPlayerStats($playerid)
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">' . self::sendRequest('PlayerStats', $playerid));
        $xpath = new DOMXPath($dom);
        $table_trs = $xpath->query('//table[@class="table table-hover"]')[0]->getElementsByTagName('tr');
        $array['Games_played_total'] = $table_trs[0]->getElementsByTagName('td')[1]->nodeValue;
        $array['Games_won']['times'] = $table_trs[1]->getElementsByTagName('td')[1]->nodeValue;
        $array['Games_won']['percentage'] = $table_trs[1]->getElementsByTagName('td')[2]->nodeValue;
        $array['Games_lost']['times'] = $table_trs[2]->getElementsByTagName('td')[1]->nodeValue;
        $array['Games_lost']['percentage'] = $table_trs[2]->getElementsByTagName('td')[2]->nodeValue;
        $array['Games_survived']['times'] = $table_trs[3]->getElementsByTagName('td')[1]->nodeValue;
        $array['Games_survived']['percentage'] = $table_trs[3]->getElementsByTagName('td')[2]->nodeValue;
        $array['Most_common_role']['name'] = $table_trs[4]->getElementsByTagName('td')[1]->nodeValue;
        $array['Most_common_role']['times'] = $table_trs[4]->getElementsByTagName('td')[2]->nodeValue;
        $array['Most_killed']['name'] = $table_trs[5]->getElementsByTagName('td')[1]->nodeValue;
        $array['Most_killed']['times'] = $table_trs[5]->getElementsByTagName('td')[2]->nodeValue;
        $array['Most_killed_by']['name'] = $table_trs[6]->getElementsByTagName('td')[1]->nodeValue;
        $array['Most_killed_by']['times'] = $table_trs[6]->getElementsByTagName('td')[2]->nodeValue;
        return $array;
    }
    public static function getSimilarInformations($information, $playerid) //PlayerKills, PlayerDeaths, PlayerKilledBy
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">' . self::sendRequest($information, $playerid));
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//table[@class="table table-hover"]')[0]->getElementsByTagName('tr') as $key => $tr) {
            $td_s = $tr->getElementsByTagName('td');
            if ($td_s->length != 0) {
                $array[$key]['name'] = $td_s[0]->nodeValue;
                $array[$key]['times'] = $td_s[1]->nodeValue;
            }
        }
        return array_values($array);
    }
    public static function getPlayerAchievements($kind, $playerid) //PlayerAchievements, PlayerLockedAchievements
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">' . self::sendRequest($kind, $playerid));
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//table[@class="table table-hover"]')[0]->getElementsByTagName('tr') as $tr) {
            $array[] = $tr->nodeValue;
        }
        return $array;
    }
}
if (isset($_REQUEST['playerid'])) {
    $werewolf = new wereWolf;
    $playerid = $_REQUEST['playerid'];
    $output = ['ok' => true, 'results' => [
        'PlayerStats' => $werewolf->getPlayerStats($playerid),
        'PlayerKills' => $werewolf->getSimilarInformations('PlayerKills', $playerid),
        'PlayerKilledBy' => $werewolf->getSimilarInformations('PlayerKilledBy', $playerid),
        'PlayerDeaths' => $werewolf->getSimilarInformations('PlayerDeaths', $playerid),
        'PlayerAchievements' => $werewolf->getPlayerAchievements('PlayerAchievements', $playerid),
        'PlayerLockedAchievements' => $werewolf->getPlayerAchievements('PlayerLockedAchievements', $playerid)
    ]];
} else {
    $output = ['ok' => false, 'message' => 'I need playerid param'];
}
echo json_encode($output, 448);
