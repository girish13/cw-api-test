<html>
<head><title>This is a test page</title>
</head>

<body>
	<form action="http://localhost/cw-api-test/public/api/v001/getRestaurantList" method='post'>
		Locality: <input type="text" id="locality_id" name="locality_id"><br />
		Date (MM/DD/YYYY): <input type="text" id="date" name="date"><br />
		Time (HH:MM 24 HRS): <input type="text" id="time" name="time"><br />
		Pax: <input type="text" name="pax" id="pax"><br /><br />
		<input type="submit" value="Submit">
	</form>
</body>
</html>