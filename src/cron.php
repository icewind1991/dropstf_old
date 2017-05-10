<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use DropTF\LogsAPI;
const STEAMID3 = '[U:1:64229260]';
const STEAMID64 = '76561198024494988';
const SINCE = 1493402992;

require __DIR__ . '/LogsAPI.php';

$api = new LogsAPI();
$drops = $api->getDropsForPlayersSince(STEAMID64, STEAMID3, SINCE);

apcu_store('dropstf_count', $drops);
