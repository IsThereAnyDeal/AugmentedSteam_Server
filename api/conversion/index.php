<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();



// Get values to convert
$from = mysql_real_escape_string($_GET['from']);
$to = mysql_real_escape_string($_GET['to']);

// Query conversion table
$sql = "SELECT `".$to."` FROM `currency` WHERE `Base`='" . $from . "' LIMIT 1";
$result = mysql_query($sql, $con);

// Conversion script!
while($currency = mysql_fetch_array($result)) {
    echo "function ".$from."to".$to."(a) { return a*".$currency[0]."; }";
}

mysql_close($con);
exit();
