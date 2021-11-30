<?php 
/*      https://realtime-listings.webservices.zpg.co.uk/docs/latest/documentation.html      */

set_time_limit(1800);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$branch_id = '******'; 
# $dir = "/web/htdocs/www.propertiser.co.uk/home/xml/";
// $dir = "../zoopla/";
// include 	$dir . "curl_zoopla.php"; //for test: curl-test.php //for live: curl.php
$textfile = __DIR__ . "/zoopla.log";

if (file_exists($textfile)) unlink($textfile);

$db = dbi();
$ref_id = $ref_number = array();

$result = mysqli_query($db,"SELECT `ID` FROM `wp_posts` WHERE `post_status`='publish' AND `ID` IN
(SELECT `post_id` FROM `wp_postmeta` WHERE `meta_key` = 'price')");
$num_results = mysqli_num_rows($result);
while(($row =  mysqli_fetch_assoc($result))) {
    array_push($ref_id, $row['ID']);
    array_push($ref_number, trim($row['ID']));
}
compare_refs($branch_id,$ref_number);
$total = 0;
// var_dump($ref_number);exit();
foreach ($ref_id as $prop) {

	$post = mysqli_query($db,"SELECT * FROM `wp_posts` WHERE `ID`=".$prop);		
    $postrow = mysqli_fetch_array($post);
    $id = $postrow["ID"];
    $date = $postrow["post_modified"];
    $descr = html_entity_decode(nl2br($postrow["post_content"]));
    $descr = str_replace(array("& ","<br />"),array("&amp; ","<br>"),$descr);
	$descr_notags = strip_tags($descr);
	
    $pic = $skip = $price = $build = $plot = $bedrooms = $bathrooms = $lon = $lat = $type = $province = $ref = $cat = $region = $country = $city = $description = $descr2 = $pool = $zip = $floorplan = $status = $date = $url = $street_name = $street_number = $lonlat = $features = $address = $number = $video = "";
    $newhome = false;
    $feat_array = $option = array();

    $meta = mysqli_query($db,"SELECT * FROM `wp_postmeta` WHERE `post_id`=".$id);
	$meta_results = @mysqli_num_rows($meta);
	   	for ($x=1; $x <= $meta_results; $x++)
		{
		$metarow = mysqli_fetch_array($meta);
		if ($metarow["meta_key"] == "_thumbnail_id") 				$mainimg = $metarow["meta_value"]; 
		if ($metarow["meta_key"] == "price") 					$price = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "housetype") 				if ($type == '') $type = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "radio_1") 					if ($type == '') $type = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "flat_house_no") 			$number = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "country") 					$country = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "county") 					$province = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "city") 					$city = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "extras") 					$features = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "description") 				$description = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "address") 					$address = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "property_size") 				$build = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "property_lot_size") 			$plot = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "property_latitude") 			$lat = $metarow["meta_value"];
		if ($metarow["meta_key"] == "property_longitude") 			$lon = $metarow["meta_value"];
		if ($metarow["meta_key"] == "property_zip") 				$zip = $metarow["meta_value"];
		if ($metarow["meta_key"] == "photos") 					$pic = $metarow["meta_value"]; 
		if ($metarow["meta_key"] == "mls") 							$ref = $metarow["meta_value"]; 
		if ($metarow["meta_key"] == "bathrooms") 				$bathrooms = $metarow["meta_value"]; 
		if ($metarow["meta_key"] == "bedroom") 					$bedrooms = $metarow["meta_value"]; 
		if ($metarow["meta_key"] == "embed_video_id") 				$video = trim($metarow["meta_value"]); 
		if ($metarow["meta_key"] == "floor_plan") 				$floorplan = trim($metarow["meta_value"]); 
		}

	// $feat = mysqli_query($db, "SELECT `term_taxonomy_id`,`taxonomy`,`name` FROM `wp_term_taxonomy` JOIN `wp_terms` ON `wp_term_taxonomy`.`term_taxonomy_id` = `wp_terms`.`term_id` WHERE `term_taxonomy_id` IN 
	// 	(SELECT `term_taxonomy_id` FROM `wp_term_relationships` WHERE `object_id`=$id)");
	// $feat_results = mysqli_num_rows($feat);
	// $taxonomy_array = "";
	//  	for ($y=1; $y <= $feat_results; $y++)
	// 	{
	// 	$featrow = mysqli_fetch_array($feat);
	// 	$featcase = trim($featrow["taxonomy"]);
	// 	if (strcasecmp($featcase,"property_city") == 0)				$city = $featrow["name"];
	// 	if (strcasecmp($featcase,"property_area") == 0)				$province = $featrow["name"];
	// 	if (strcasecmp($featcase,"property_category") == 0)				{ 
	// 				if ($featrow["name"] == 'New Build' || $featrow["name"] == 'New Build Villa') $newhome = 1;
	// 				else if ($featrow["name"] !== 'Ground Floor' && $featrow["name"] !== 'Renovation Project Opportunity') 
	// 					$type = $featrow["name"];
	// 																}
	// 	if (strcasecmp($featcase,"property_action_category") == 0)	$status = $featrow["name"];
	// 	if (strcasecmp($featcase,"property-feature") == 0)			array_push($feat_array, $featrow["name"]);
	// 	if($status == "Reserved" || $status == "Sold") $skip = 1;	
	// 	if($status == "Holiday Rent" || $status == "For Rent") $price_freq = "month"; else $price_freq = "sale";
	// 	}

		$ref = $id;
		if ($features !== '')
		$feat_array = array_map('trim', explode("|", $features));

		if ($pic !== '') $pic = implode(",", unserialize($pic));
		// var_dump($pic);
$pic_array = array();
// $mg = 1;
$gallery_ = mysqli_query($db, "SELECT `meta_value` FROM `wp_postmeta` WHERE `meta_key`='_wp_attached_file' AND `post_id` IN ($pic)");
    if ($gallery_) 
        while ($imageRow = mysqli_fetch_array($gallery_, MYSQLI_ASSOC)) {
		array_push($pic_array, array('url' => "https://www.******/******/uploads/".trim($imageRow['meta_value']), 'type' => "image"));
		// $mg++;
        }
if ($floorplan !== '') {
// $floorplan = ($floorplan);
// if (is_array($floorplan)) $floorplan = implode(",", $floorplan);

$gallery_ = mysqli_query($db, "SELECT `meta_value` FROM `wp_postmeta` WHERE `meta_key`='_wp_attached_file' AND `post_id` IN ($floorplan)");
    if ($gallery_) 
        while ($imageRow = mysqli_fetch_array($gallery_, MYSQLI_ASSOC)) {
		array_push($pic_array, array('url' => "https://www.******/******/uploads/".trim($imageRow['meta_value']), 'type' => "floor_plan"));
		// $mg++;
        }
}

if ($video !== "") array_push($pic_array, array('url' => trim("https://vimeo.com/".str_replace("&", "&amp;", $video)), 'type' => "virtual_tour"));

if (strlen($description)>150) {
if (strlen($description)>600) $pos = strpos($description, ' ', 500);
else if (strlen($description)>400) $pos = strpos($description, ' ', 300);
else $pos = strpos($description, ' ', 100);
$short = substr($description, 0, $pos); 
}
else $short = $description; 

$address = "$number, $address, $city";
$category = "residential";

if ($type == "villas" || $type == "villa" || $type == "attached villa") $type = "villa";
if ($type == "apartment" || $type == "penthouse" || $type == "penthouse apartment" || $type == "duplex" || $type == "duplex corner" || $type == "duplex penthouse" || $type == "duplex apartment") $type = "flat";
if ($type == "studio apartment") $type = "studio";
if ($type == "townhouse" || $type == "town house") $type = "town_house";
if ($type == "terraced/townhouse" || $type == "terraced") $type = "terraced";
if ($type == "country house" || $type == "village house") $type = "country_house";
if ($type == "semi detached" || $type == "semi-detached" || $type == "semi-detached villa") $type = "semi_detached";
if ($type == "bungalow") $type = "bungalow";
if ($type == "finca / country property" || $type == "finca") $type = "finca";
if ($type == "detached villa" || $type == "detached house") $type = "detached";
if ($type == "plot" || $type == "country land" || $type == "solar urbano") $type = "land";
if ($type == "hotel") { $type = "hotel"; $category = "commercial";}
if ($type == "commercial property") { $type = "leisure"; $category = "commercial";}

$jsonData = array(
    'branch_reference' 		=> 	$branch_id,
    'category'				=> 	$category,
    'detailed_description' 	=> 	array(array(
    							'text' => $description
                                )),
    'life_cycle_status'		=> 	"available",
    'listing_reference'		=>	strtoupper($ref),
    'location'				=>	array(
    							'country_code' 	=> "UK",
                                // 'county'        => $province,
    							'town_or_city'	=> $city,
    							'property_number_or_name' => $number,
    							'coordinates'	=> 	array(
    												'latitude'  => (float) $lat,
    												'longitude' => (float) $lon
    												)
                                // 'postal_code'   => $zip
    							),
    'pricing' 				=> 	array(
    							'transaction_type' => "sale",
    							'currency_code'	=> "GBP",
    							'price' => (int) $price
    							),
    'property_type'			=>  $type,
    // 'feature_list'			=> 	$feat_array,
    'total_bedrooms'		=>	(int) $bedrooms,
    'bathrooms' 			=> 	(int) $bathrooms,
    'new_home'				=> 	$newhome,
    'display_address'       =>  $address,
    'summary_description'	=> 	$short,
    'content'				=>	$pic_array
    );
    if ($plot>0 && $build>0)    $jsonData['areas'] = array(
                                'external' => array('minimum' => array('value' => (int) $plot, 'units' => 'sq_metres')),
                                'internal' => array('minimum' => array('value' => (int) $build, 'units' => 'sq_metres'))
                                );
    elseif ($build>0)			$jsonData['areas'] = array(
                                'internal' => array('minimum' => array('value' => (int) $build, 'units' => 'sq_metres'))
                                );
    if (count($feat_array) > 0) {
       $jsonData['feature_list'] = array_slice($feat_array,0,10);
    }    
    if (isset($pool) && $pool == 1) {
       $jsonData['swimming_pool'] = true;
    }

$jsonDataEncoded = json_encode($jsonData);
$ETag = md5($date);
$respond = curl('add',$jsonDataEncoded,$ETag);

$fp = fopen($textfile,"a");
fwrite($fp,"REFERENCE:$ref\r\n".$respond."\r\n");
fclose($fp);
}


//=================== C O M P A R E  Y O U R  D R E A M  H O M E ======================================

function compare_refs($branch_id,$ref_number)
{
    if (count($ref_number) > 0) {
$jsonData = array('branch_reference' => $branch_id);
$jsonDataEncoded = json_encode($jsonData);

$rmbase = json_decode(curl('show',$jsonDataEncoded,""),true);
foreach ($rmbase as $rmb) 
	if (is_array($rmb)) 
		foreach ($rmb as $prop)
		if (!empty($prop["listing_reference"]))
		if (!in_array($prop["listing_reference"],$ref_number)) 
			{ 	
				// echo "Need to remove " . $prop["listing_reference"] . " => "; 
				$jsonData = array('listing_reference' => $prop["listing_reference"]);
				$jsonDataEncoded = json_encode($jsonData);
				$respond = curl('remove',$jsonDataEncoded,""); 
			}
    }
}

function dbi () {
// $host = "localhost";
$host = "******";
$username = "******";
$pass = "******";
$dbname = "******";
$charset = "utf8";
	$link = mysqli_connect($host, $username, $pass, $dbname);
	$link->set_charset($charset);
	return $link;
}

//=================== A P I  C O N N E C T I O N ====================================

function curl($send,$jsonDataEncoded,$md5)
{
switch ($send) 	{
case "add" 		: 	{ 
					$url = 'https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/update'; 
					$profile = 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/update.json';
					}
					break;
case "remove" 	: 	{
					$url = 'https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/delete'; 
					$profile = 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/delete.json';
					}
					break;
case "show" 	: 	{
					$url = 'https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/list'; 
					$profile = 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/list.json';
					}
					break;
				}
$pemfile 	= "/web/htdocs/www.******.co.uk/home/xml/******_private-key.pem";
$sertfile 	= "/web/htdocs/www.******.co.uk/home/xml/zpg_realtime_listings_******.crt";
if ($md5 == '') $md5 = md5(time());
// echo ('ZPG-Listing-ETag:' . $md5 . "<br>");
$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSLKEY,$pemfile);
		curl_setopt($ch, CURLOPT_SSLCERT, $sertfile);		
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;profile=' . $profile,'ZPG-Listing-ETag:' . $md5));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
		$strResponse = curl_exec($ch);

return $strResponse;
curl_close($ch);
}
?>