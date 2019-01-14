<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


// Select record for passed APPID
$appid = mysql_real_escape_string($_GET['appid']);

$return = "[";

if (is_numeric ($appid)) {
    $result = mysql_query("SELECT * FROM market_data WHERE appid=".$appid." AND `type`='card'");
    while($row = mysql_fetch_array($result))
        {
            $return = $return . '{';
            $return = $return . '"game": "'.$row['game'].'",';
            $return = $return . '"name": "'.$row['name'].'",';
            $return = $return . '"img": "'.$row['img'].'",';
            $return = $return . '"url": "'.$row['url'].'",';
            $return = $return . '"price": "'.$row['price'].'",';
            $return = $return . '"price_gbp": "'.$row['price_gbp'].'",';
            $return = $return . '"price_eur": "'.$row['price_eur'].'",';
            $return = $return . '"price_brl": "'.$row['price_brl'].'",';
            $return = $return . '"price_rub": "'.$row['price_rub'].'",';
            $return = $return . '"price_jpy": "'.$row['price_jpy'].'",';
            $return = $return . '"price_nok": "'.$row['price_nok'].'",';
            $return = $return . '"price_idr": "'.$row['price_idr'].'",';
            $return = $return . '"price_myr": "'.$row['price_myr'].'",';
            $return = $return . '"price_php": "'.$row['price_php'].'",';
            $return = $return . '"price_sgd": "'.$row['price_sgd'].'",';
            $return = $return . '"price_thb": "'.$row['price_thb'].'",';
            $return = $return . '"price_vnd": "'.$row['price_vnd'].'",';
            $return = $return . '"price_krw": "'.$row['price_krw'].'",';
            $return = $return . '"price_try": "'.$row['price_try'].'",';
            $return = $return . '"price_uah": "'.$row['price_uah'].'",';
            $return = $return . '"price_mxn": "'.$row['price_mxn'].'",';
            $return = $return . '"price_cad": "'.$row['price_cad'].'",';
            $return = $return . '"price_aud": "'.$row['price_aud'].'",';
            $return = $return . '"price_nzd": "'.$row['price_nzd'].'",';
            $return = substr($return, 0, -1);
            $return = $return . '},';
        }
}

$return = substr($return, 0, -1);
if ($return != "" ) $return = $return . "]";

echo $return;

// Close database connection
mysql_close($con);
