<?php
	require_once("../config.php");
	
	// Delete existing database entries
	$sql = "TRUNCATE TABLE `early_access`";
	$result = mysql_query($sql, $con);
	
	$appids = array();
	$i = 2;
	
	// Get page 1 to find first results and relevant information
	$page1 = file_get_contents("http://store.steampowered.com/search/results?term=&genre=Early%20Access&page=1");
	$results1 = preg_match_all("/app\/(\d+)\//", $page1, $matches);	
	$num_results = preg_match_all("/&page=(\d+)/", $page1, $number);
	$num_pages = max($number[1]);
	$appids = array_merge ($appids, $matches[1]);
	
	// Get the rest of the data
	do {
		$page = file_get_contents("http://store.steampowered.com/search/results?term=&genre=Early%20Access&page=".$i);
		$result = preg_match_all("/app\/(\d+)\//", $page, $matches);
		$appids = array_merge ($appids, $matches[1]);
		$i += 1;
	} while ($i < $num_pages);
	
	// Put it in the database
	foreach ($appids as $value) {
		$sql = "INSERT INTO `early_access` VALUES (".$value.")";
		$result = mysql_query($sql, $con);
	}
	
	exit();
?>