<?php

$dirForScan = '../wordpress';
$siteDir = scandir($dirForScan);


if(array_search('wp-config.php', $siteDir)){
	// Получаем настройки доступа к базе
	$configFile = file_get_contents($dirForScan.'/wp-config.php');
	
	$pattern = '/(DB_NAME|DB_USER|DB_PASSWORD|DB_HOST)\'\,\s\'(.+?)\'/m';
	preg_match_all($pattern, $configFile, $dbConf, PREG_SET_ORDER, 0);

	$database = $dbConf[0][2];
	$user = $dbConf[1][2];
	$pass = $dbConf[2][2];
	$host = $dbConf[3][2];


	$date = date('d.m.Y-H:i:s');

	// Делаем dump
	$fileSQL = dirname(__FILE__) . '/storage/dump_'.$date.'.sql';

	$success = exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$fileSQL} 2>&1", $output);

	if(file_exists($fileSQL)){
		echo $success ? "Создана резервная копия в $fileSQL" : 'Не удалось произвести резервное копирование';
	}
	else{
		exit("Не удалось создать дамп по тому что $output[0]");
	}
	

	// Архивируем
	$zip = new ZipArchive();
	$fileZip = dirname(__FILE__) . "/storage/archives/$date.zip";

	if ($zip->open($fileZip, ZipArchive::CREATE)!==TRUE) {
	    exit("Невозможно открыть $fileZip");
	}

	$zip->addFile($fileSQL, "dump$date.sql");

	$zip->close();
}; 