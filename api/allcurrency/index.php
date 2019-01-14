<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();



// Query conversion table
$sql = "SELECT base,EUR,GBP,USD,RUB,BRL FROM `currency`";
$result = mysql_query($sql, $con);

$rows = array();
while($r = mysql_fetch_assoc($result)) {
    $rows[] = $r;
}
$all = json_encode($rows);


echo $all;
