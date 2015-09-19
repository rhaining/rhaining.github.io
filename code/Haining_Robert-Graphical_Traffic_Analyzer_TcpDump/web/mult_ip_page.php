<html>
<head>
</head>
<body>

<?php
	/*
	Written by Rob Haining, Wake Forest University
		Spring 2004

		displays packets over time for IP addresses
		can select a specific IP to display
	*/
	/*basic check to see if the correct information is being sent to the scripts*/
	$my_ip = "152.17.140.132";

	if($_POST['action'] == 'display_ip'){
		$port = $_POST['port'];
		$type = $_POST['type'];

		$port_type = $type . "_port";
		$ip_type = $type . "_ip";

		if($type == "src"){$other_ip_type = "dst_ip";}
		else{$other_ip_type = "src_ip";}

		$ip_eq_text = "$ip_type = \"$my_ip\"";
	}else if($_POST['action'] == 'display_bg_ip'){
		$port = $_POST['port'];
		$type = $_POST['type'];

		$port_type = $type . "_port";
		$ip_type = $type . "_ip";

		if($type == "dst"){$other_ip_type = "dst_ip";}
		else{$other_ip_type = "src_ip";}

		$ip_eq_text = "(dst_ip != \"$my_ip\") AND (src_ip != \"$my_ip\")";
	}else{exit;}
	print "<img src=\"mult_ip.php?action=" . $_POST['action'] . "&port=$port&type=$type\">\n";

	print "<br>\nexpand on ip: ";
	/*form to display graph for a single IP*/
	print "<form method = \"post\" action=\"single_ip_page.php\">\n";
	print "<select name=\"ip\">\n";

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	/*select ip's for a specific port*/
	$sql_que = "SELECT DISTINCT $other_ip_type FROM $mysql_table\n "
			. "WHERE ($port_type = $port) AND ($ip_eq_text)\n "
			. "ORDER BY $ip_type ASC";
        if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
                print "Query failed : " . mysql_error();
                exit();
        }
	while($row = mysql_fetch_array($mysql_result)){//get data row from MySQL
		$ip = $row[$other_ip_type];
		echo "\t<option value=\"$ip\">$ip</option>\n";
	}

?>

</select>
<input type="hidden" name="type" value="<?php echo $type?>">
<input type="hidden" name="port" value="<?php echo $port?>">
<input type="hidden" name="action" value="<?php echo $_POST['action']?>">
<input type="Submit"><br>
<INPUT TYPE="button" VALUE="Back" onClick="history.go(-1)">
</form>

</body>
</html>
