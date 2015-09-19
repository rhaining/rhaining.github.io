<?php
	/*
	Written by Rob Haining
		Spring 2004

		index file, hosts:
			total packets over time (last 24 hrs)
			bar graph of source ports over last 24 hrs
			bar graph of destination ports over last 24 hrs
	*/
?>
<html>
<head>
<META HTTP-EQUIV=Refresh CONTENT="60; URL=index.php">
</head>
<body>
<img src="total_pkts.png">
<br>

<img src="incoming_bar.png">

<form method="post" action="mult_ip_page.php">
expand port: 
<select name="port">

<?php
	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$my_ip = "152.17.140.132";
	$port_type = "dst_port";
	/*selects source ports between 0 & 1024*/
	$sql_que = "SELECT DISTINCT $port_type FROM $mysql_table "
			. "WHERE ($port_type >= 0) AND ($port_type < 1024) "
			. "AND (dst_ip = \"$my_ip\") ORDER BY $port_type ASC";

        if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
                print "Query failed : " . mysql_error();
                exit();
        }
	while($row = mysql_fetch_array($mysql_result)){
		$port = $row[$port_type];
		if($port == 0){$port_txt = "ICMP";}
		else{$port_txt = $port;}
		echo "\t<option value=\"$port\">$port_txt</option>\n";
	}

?>

</select>
<input type="hidden" name="type" value="dst">
<input type="hidden" name="action" value="display_ip">
<input type="Submit">
</form>

<br><br>
<img src="outgoing_bar.png">
<br>
<form method="post" action="mult_ip_page.php">
expand port: 
<select name="port">

<?php
	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$my_ip = "152.17.140.132";

	$port_type = "src_port";
	/*selects destination ports between 0 & 1024*/
	$sql_que = "SELECT DISTINCT $port_type FROM $mysql_table "
			. "WHERE ($port_type >= 0) AND ($port_type < 1024) "
			. "AND (src_ip = \"$my_ip\") ORDER BY $port_type ASC";

        if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
                print "Query failed : " . mysql_error();
                exit();
        }
	while($row = mysql_fetch_array($mysql_result)){
		$port = $row[$port_type];
		if($port == 0){$port_txt = "ICMP";}
		else{$port_txt = $port;}
		echo "\t<option value=\"$port\">$port_txt</option>\n";
	}

?>

</select>
<input type="hidden" name="type" value="dst">
<input type="hidden" name="action" value="display_ip">
<input type="Submit">
</form>

<br><br>
<img src="background_src_bar.png">
<br>

<form method="post" action="mult_ip_page.php">
expand port: 
<select name="port">

<?php
	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$my_ip = "152.17.140.132";

	$port_type = "src_port";
	/*selects destination ports between 0 & 1024*/
	$sql_que = "SELECT DISTINCT $port_type FROM $mysql_table "
			. "WHERE ($port_type >= 0) AND ($port_type < 1024) "
			. "AND (src_ip != \"$my_ip\") AND (dst_ip != \"$my_ip\") "
			. "ORDER BY $port_type ASC";

        if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
                print "Query failed : " . mysql_error();
                exit();
        }
	while($row = mysql_fetch_array($mysql_result)){
		$port = $row[$port_type];
		if($port == 0){$port_txt = "ICMP";}
		else{$port_txt = $port;}
		echo "\t<option value=\"$port\">$port_txt</option>\n";
	}

?>

</select>
<input type="hidden" name="type" value="src">
<input type="hidden" name="action" value="display_bg_ip">
<input type="Submit">
</form>

<br><br>
<img src="background_dst_bar.png">
<br>

<form method="post" action="mult_ip_page.php">
expand port: 
<select name="port">

<?php
	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$my_ip = "152.17.140.132";

	$port_type = "dst_port";
	/*selects destination ports between 0 & 1024*/
	$sql_que = "SELECT DISTINCT $port_type FROM $mysql_table "
			. "WHERE ($port_type >= 0) AND ($port_type < 1024) "
			. "AND (src_ip != \"$my_ip\") AND (dst_ip != \"$my_ip\") "
			. "ORDER BY $port_type ASC";

        if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
                print "Query failed : " . mysql_error();
                exit();
        }
	while($row = mysql_fetch_array($mysql_result)){
		$port = $row[$port_type];
		if($port == 0){$port_txt = "ICMP";}
		else{$port_txt = $port;}
		echo "\t<option value=\"$port\">$port_txt</option>\n";
	}

?>

</select>
<input type="hidden" name="type" value="dst">
<input type="hidden" name="action" value="display_bg_ip">
<input type="Submit">
</form>
</body>
</html>
