<?php	
	require_once("../config.php");

	// Get User ID and Steam64 from form validation
	$userid = mysql_real_escape_string($_POST['userid']);	
	$steam64 = mysql_real_escape_string($_POST['steam64']);
	
	// Check for correct HTTP_REFERER
	if ($_SERVER['HTTP_REFERER'] = "http://steamcommunity.com/id/".$userid."/edit") {
		
		// Create OpenID authentication class
		include 'openid.php';
		$openid = new LightOpenID('www.EnhancedSteam.com'); //make new instance with our domain		
		$openid->identity = 'http://steamcommunity.com/openid/'; //set the openid provider to steam
		$openid->return_to = 'http://api.enhancedsteam.com/profile_style/profile_style_remove.php';
		
		if (!isset($_GET['openid_mode'])) {
			if ($steam64 !="") {
				// Delete any previous pending record with this ID to prevent tampering
				$sql = "DELETE FROM `profile_style_users_pending` WHERE `steam64`='".$steam64."'";
				$result = mysql_query($sql, $con);
				
				// Store variables into pending record for later retrieval			
				$sql = "INSERT INTO `profile_style_users_pending` (`steam64`, `profile_style`, `ip`) VALUES ('".mysql_real_escape_string($steam64)."', '".mysql_real_escape_string($_POST['es_style'])."', '".$_SERVER["REMOTE_ADDR"]."')";
				$result = mysql_query($sql, $con);
							
				// Redirect user to OpenID provider
				header('Location:'.$openid->authUrl());
			} else {
				header('Location: http://www.steamcommunity.com/my/profile');
			}
		} else {
			if ($openid->validate()) {
				$steam_community_id = substr($_GET['openid_identity'],-17); //steam returns an openid identifier, we just need the community id (last 17 digits of it)
							
				// Get pending update record (limit: 1)
				$sql = "SELECT * FROM `profile_style_users_pending` WHERE `steam64`='".mysql_real_escape_string($steam_community_id)."' LIMIT 1";
				$result = mysql_query($sql, $con);
				
				while ($pending = mysql_fetch_array($result)) {
					if ($pending['steam64'] != "") {
						// Update existing record
						$sql3 = "DELETE FROM `profile_style_users` WHERE `steam64`='".mysql_real_escape_string($pending['steam64'])."'";						
						mysql_query ($sql3, $con);
					}
					
					// Delete pending record for this update
					$sql4 = "DELETE FROM `profile_style_users_pending` WHERE `steam64`='".mysql_real_escape_string($steam_community_id)."'";
					$result = mysql_query($sql4, $con);
					header('Location: http://www.steamcommunity.com/my/profile');
				}
				header('Location: http://www.steamcommunity.com/my/profile');
			} else {				
				// Delete pending record
				
				// Redirect to home page				
				header('Location: http://www.steampowered.com');
			}			
		}						
	}	
?>