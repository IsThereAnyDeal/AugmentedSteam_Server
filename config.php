<?php
	$database_server = "[Database Server]";
	$database_userna = "[Database Username]";
	$database_passwo = "[Database Password]";
	$database_dbname = "[Database Name]";

	// API Keys
	$steamAPIkey = "[Steam API Key]";
	$openexchangeapi = "[Open Exchange Rates API Key]";
	$opencritic_key = "[OpenCritic API Key]";
	$ITAD_key = "[IsThereAnyDeal.com API Key]";
	$twitchapi = "[Twitch API Key]";

	// API Server Endpoints
	$wsgf_server = "[WSGF API Server Endpoint]";
	$keylol_server = "[KeyLOL API Server Endpoint]";
	$steamspy_server = "[SteamSpy API Server Endpoint]";
	$steamtoolsapi_server = "[Steam.tools API Server Endpoint]";
	$opencritic_server = "[OpenCritic API Server Endpoint]";
	$steamrep_server = "[SteamRep API Server Endpoint]";
	$pcgw_server = "[PCGamingWiki API Server Endpoint]";

	header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
	set_error_handler("customError");

	//Open Database connection
	$con = mysql_connect($database_server,$database_userna,$database_passwo);
	if (!$con) { die('Could not connect: ' . mysql_error()); }
	mysql_select_db($database_dbname, $con);


	function customError($errno, $errstr) {
		echo "<b>Error:</b> An Error Occurred in the Enhanced Steam API Server";
		die();
	}
?>