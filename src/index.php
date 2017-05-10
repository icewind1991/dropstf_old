<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use DropTF\LogsAPI;
const STEAMID3 = '[U:1:64229260]';
const STEAMID64 = '76561198024494988';
const SINCE = 1493400992;

require __DIR__ . '/LogsAPI.php';

$cachedDrops = apcu_fetch('dropstf_count');
if ($cachedDrops === false) {
	$api = new LogsAPI();
	$drops = $api->getDropsForPlayersSince(STEAMID64, STEAMID3, SINCE);
	apcu_store('dropstf_count', $drops);
} else {
	$drops = $cachedDrops;
}
$since = new DateTime();
$since->setTimestamp(SINCE);
?>
<html>
<head>
	<title>drops.tf - sponsored by demos.tf</title>
	<style>
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
	Drops since <?php echo $since->format('l Y-m-d') ?>
</p>
<p class="drops">
	<?php echo $drops ?>
</p>
<p class="footer">
	drops.tf is proudly sponsored by <a href="https://demos.tf">demos.tf</a>
</p>
</body>
</html>
