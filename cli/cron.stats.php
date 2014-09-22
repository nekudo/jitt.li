<?php
include_once __DIR__ . '/config.db.php';
include_once __DIR__ . '/../libs/class.db.php';

$Redis = new Redis;
$Redis->connect('127.0.0.1');
$Db = Db::getInstance(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// update hourly stats table:
$hour = date('G');
$day = date('Y-m-d');
$tweetsLastMinute = $Redis->hGet('jiStats', 'lastMinute');

$row = $Db->prepare("SELECT id FROM stats_hourly WHERE hour = %d AND day = %s", $hour, $day)->getResult();
$statId = (!empty($row['id'])) ? $row['id'] : 0;
if(empty($statId))
{
	$Db->prepare("INSERT INTO stats_hourly (hour,day,tweets) VALUES(%d,%s,%d)
		ON DUPLICATE KEY UPDATE tweets = tweets + %d", $hour, $day, $tweetsLastMinute, $tweetsLastMinute)->execute();
}
else
{
	$Db->prepare("UPDATE stats_hourly SET tweets = tweets + %d WHERE id = %d", $tweetsLastMinute, $statId)->execute();
}