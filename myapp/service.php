<?php

require 'vendor/autoload.php';
$app = new Slim\App();

$app->get('/search/', function ($request, $response, $args) {
$sql = "SELECT * FROM properties";
// conditions array
$conditions = array();
$conditions2 = array();	
	
$uniq_id = $request->getQueryParam('uniq_id');
//	$uniq_id = "00954d18277acc4bf9080370b1e0bcfe";
if($uniq_id != "" ){	
echo "<pre>Search by uniq Id uniq_id=".$uniq_id."</pre>";
$conditions[] = "uniq_id = '" . $uniq_id . "'";	
}	
	
$property_type = $request->getQueryParam('property_type');
//(?property_type=Hotel)
if($property_type != "" ){	
echo "<pre>Search by property_type property_type=".$property_type."</pre>";
$conditions[] = "property_type = '".$property_type."'";	
}	
	
$city = $request->getQueryParam('city');	
//(?city=Kanpur)
if($city != "" ){	
echo "<pre>Search by City city=".$city."</pre>";
$conditions[] = "city = '".$city."'";	
}	
	
$amenities = $request->getQueryParam('amenities');	
//(?amenities=Parking)
if($amenities != "" ){	
echo "<pre>Search by Amenities amenities=".$amenities."</pre>";
$conditions[] = "amenities LIKE '%".$amenities."%'";	
}	
	
$room_price = $request->getQueryParam('room_price');
$maxp = "";
$minp = "";	
//	(?room_price=1000-2000)
if($room_price != "" ){	
$room_price	= explode("-",$room_price);	
$maxp =  $room_price[1];
$minp =  $room_price[0];
echo "<pre>Search by Room Price Range min = ".$minp." to max = ".$maxp."</pre>";
$sql = "SELECT *,SUBSTRING_INDEX(`room_price`,\"p\",1) As Price FROM properties";	
 $conditions2[] = "(Price BETWEEN ".$minp." AND ".$maxp.")";	
}
	
$lat = $request->getQueryParam('lat');
$long = $request->getQueryParam('long');
$radius = $request->getQueryParam('radius');	
//Location & Radius in miles  (?lat=11.2469988&long=75.7802734&radius=10)()	
if($lat && $long && $radius  != "" ){
echo "<pre>Search by Location & Radius Location Lat = ".$lat." Location Long = ".$long." and Radius in ".$radius." miles</pre>";
$sql = "SELECT *, ( 3959 * acos( cos( radians('".$lat."') ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians('".$long."') ) + sin( radians('".$lat."') ) * sin( radians( `latitude` ) ) ) ) AS distance FROM properties";
 $conditions2[] = " (distance < " . $radius . ") ORDER BY distance";
if($room_price != "" ){	
	$sql = "SELECT *,SUBSTRING_INDEX(`room_price`,\"p\",1) As Price, ( 3959 * acos( cos( radians('".$lat."') ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians('".$long."') ) + sin( radians('".$lat."') ) * sin( radians( `latitude` ) ) ) ) AS distance FROM properties";
}	
}		
	
// concatenate array elements with " AND " 
$sql_cond = join(" AND ", $conditions);
$sql_cond2 = join(" AND ", $conditions2);	

// adding condition(s) to the main query (if there's any)
if ($sql_cond != '') $sql .= " WHERE $sql_cond ";
if ($sql_cond2 != '') $sql .= " HAVING $sql_cond2 ";	
	
//	echo $sql;

try {
 $db = getConnection();
 $result = $db->query($sql);
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			echo "<pre>".print_r($row, true)."</pre>";
		}
				
if ($result->rowCount() == 0){
 echo "No result";	
}	
 $db = null;
 } 
	
 catch(PDOException $e) {
echo "fail";
} 
});

$app->run();

function getConnection() {
 $dbhost="localhost";
 $dbuser="root";
 $dbpass="";
 $dbname="crc";
 $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 return $dbh;
}
?>