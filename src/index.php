<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use DropTF\LogsAPI;

const SINCE = 1493400992;

require __DIR__ . '/SteamID.php';
require __DIR__ . '/LogsAPI.php';

$key = getenv('key');
$id = $_GET['id'] ?? '76561198024494988';
$steamId = new SteamID($id);

$cachedDrops = apcu_fetch('dropstf_count' . $id);
if ($cachedDrops === false) {
	$api = new LogsAPI();
	[$drops, $games] = $api->getDropsForPlayersSince($steamId, SINCE);
	apcu_store('dropstf_count' . $id, json_encode([$drops, $games]), 5 * 60);
} else {
	[$drops, $games] = json_decode($cachedDrops);
}

$cachedName = apcu_fetch('name_' . $id);
if ($cachedName === false) {
	$content = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=$id&key=$key");
	$name = json_decode($content, true)['response']['players'][0]['personaname'];
	apcu_store('name_' . $id, $name, 10 * 60 * 60);
} else {
	$name = $cachedName;
}

if (isset($_GET['id'])) {
	$namebit = "<br/> by $name";
} else {
	$namebit = '';
}

$since = new DateTime();
$since->setTimestamp(SINCE);
?>
<html>
<head>
    <title>drops.tf - sponsored by demos.tf</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            position: absolute;
            top: 0;
            height: 100%;
            width: 100%;
        }

        body {
            background-color: #222;
            color: #ccc;
            text-align: center;
            height: 100%;
        }

        p.since {
            margin-top: 100px;
            font-size: 3em;
        }

        p.drops {
            font-size: 9em;
            font-weight: bold;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translateX(-50%) translateY(-50%);
            margin-top: 0;
        }

        p.games {
            font-size: 1em;
            font-weight: bold;
            position: absolute;
            top: calc(50% + 5em);
            left: 50%;
            transform: translateX(-50%) translateY(-50%);
            margin-top: 0;
        }

        p.footer {
            font-size: 1.5em;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
        }

        a {
            color: #3498db
        }

        a:visited {
            color: #3498db;
        }

    </style>
</head>
<body>
<p class="since">
    Drops since <?php echo $since->format('l Y-m-d');
	echo $namebit ?>
</p>
<p class="drops">
	<?php echo $drops ?>
</p>
<p class="games">
    In <?php echo $games ?> games
</p>
<p class="footer">
    drops.tf is proudly sponsored by <a href="https://demos.tf">demos.tf</a>
</p>
</body>
</html>
