<?php //define('GW_UPLOADPATH', 'images/');
require_once('appvars.php');
?>
<html>
<head>
	<title>Filter Entry Form</title>
</head>

<body>

<p>Filter Entry Form</p>
<?php 

$dbc=mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die ("could not connect to database");

if (isset($_GET['action']) && isset($_GET['filter_id']) && isset ($_GET['filter_rest_id']) && isset($_GET['filter_name'])) {
	if ($_GET['action']=='apply') {
		$query = "INSERT INTO filter (filter_list_id, restaurant_id) VALUES ('".$_GET['filter_id']."','".$_GET['filter_rest_id']."')";
		$result = mysqli_query($dbc, $query) or die("Failed query");
		echo "<p><b>Filter Name: ".$_GET['filter_name']." Applied.</b></p>";
		$url = $_SERVER['PHP_SELF'].'?restaurant_id='.$_GET['filter_rest_id']."&msg=Filter%20Name%20".$_GET['filter_name']."%20Applied";;
		header("Location: ".$url);
	}

	else if ($_GET['action']=='remove') {
	$query = "DELETE FROM filter WHERE filter_list_id='".$_GET['filter_id']."' AND restaurant_id='".$_GET['filter_rest_id']."'";
	$result = mysqli_query($dbc, $query) or die("Failed query");
	$url = $_SERVER['PHP_SELF'].'?restaurant_id='.$_GET['filter_rest_id']."&msg=Filter%20Name%20".$_GET['filter_name']."%20Removed";
	header("Location: ".$url);
	}
}


if (isset($_GET['msg'])) {
echo "<p><b>".$_GET['msg']."</p></b>";
} 
?>

<p>
	<form method="GET" id="form1" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<select name="restaurant_id" onchange="this.form.submit()">
<?php
	$query = "SELECT id, name FROM restaurant";
	$result = mysqli_query($dbc, $query) or die("Failed to get list of restaurants");
	//mysqli_close($dbc);

	while ($row = mysqli_fetch_array($result)) {
		echo "<option value=\"".$row['id']."\">".$row['name']."</option>";
	}
?>
</select>
</form>
	
<form method="POST" id="form2" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<?php 


if (isset($_GET['restaurant_id']) && is_numeric($_GET['restaurant_id'])) {
	
	//echo '<input type="hidden" name="restaurant_id" value="'.$_GET['restaurant_id'].'">';

	$curr_filter =  array();
	$query = "SELECT filter_list_id FROM filter WHERE restaurant_id='".$_GET['restaurant_id']."'";
	$result = mysqli_query($dbc, $query) or die("Failed to get list of current filters");
	$curr_filter[0]=NULL;
	while ($row = mysqli_fetch_array($result)) {
		array_push($curr_filter, $row['filter_list_id']);
	}
	//print_r($curr_filter);

	$query = "SELECT DISTINCT type FROM filter_list";
	$result = mysqli_query($dbc, $query) or die("Failed to get list of filter types");

	while ($row = mysqli_fetch_array($result)) {
		echo "<p style=\"font-size:25px\"><b>".$row['type']."</b></p>";

		$query2 = "SELECT id,name FROM filter_list WHERE type='".$row['type']."'";
		$result2 = mysqli_query($dbc, $query2) or die("Failed to get list of filters");

		while ($row2 = mysqli_fetch_array($result2)) {
			if (array_search($row2['id'], $curr_filter)) echo $row2['name'].' | <a href="'.$_SERVER['PHP_SELF'].'?action=remove&filter_id='.$row2['id'].'&filter_rest_id='.$_GET['restaurant_id'].'&restaurant_id='.$_GET['restaurant_id'].'&filter_name='.$row2['name'].'">Remove</a><br>';
			else echo $row2['name'].' | <a href="'.$_SERVER['PHP_SELF'].'?action=apply&filter_id='.$row2['id'].'&filter_rest_id='.$_GET['restaurant_id'].'&restaurant_id='.$_GET['restaurant_id'].'&filter_name='.$row2['name'].'">Apply</a><br>';
		}

	}
}


?>
