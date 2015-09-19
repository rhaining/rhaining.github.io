<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		outputs a graph of packets per IP over time for a specific port
	*/
	/*basic check to make sure correct data is being sent to script*/
	$my_ip = "152.17.140.132";
	set_time_limit(90);

	if($_GET['action'] == 'display_ip'){
		$port = $_GET['port'];
		$type = $_GET['type'];

		$port_txt = $type . "_port";
		$ip_txt = $type . "_ip";
		if($type == "src"){$other_ip_txt = "dst_ip";}
		else{$other_ip_txt = "src_ip";}

		$ip_eq_text = "$ip_txt = \"$my_ip\"";
	}else if($_GET['action'] == 'display_bg_ip'){
		$port = $_GET['port'];
		$type = $_GET['type'];

		if($type == "dst"){$other_ip_txt = "dst_ip";}
		else{$other_ip_txt = "src_ip";}

		$port_txt = $type . "_port";
		$ip_txt = $type . "_ip";

		$ip_eq_text = "(dst_ip != \"$my_ip\") AND (src_ip != \"$my_ip\")";
	}else{exit;}

	/*necessary libraries*/
	include ( 'jpgraph/jpgraph.php');
	include ('jpgraph/jpgraph_line.php'); 

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$packets = array();/*packet matrix...rows will be IPs, and cols will be time intervals*/
	$ip_array = array();/*array of IP addresses*/
	$time_array = array();/*array of times...x-axis*/

	array_push($ip_array,"TOTAL");/*for total number of packets per interval*/
	$packets["TOTAL"] = array();

	/*GET LIST OF IP's*/
	/*get distinct IP's for a specified port #*/
	$sql_que = "SELECT DISTINCT $other_ip_txt FROM $mysql_table "
			. "WHERE ($port_txt = $port) AND ($ip_eq_text) ORDER BY $ip_txt ASC";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}
	while($row = mysql_fetch_array($mysql_result)){//get data row from MySQL
		$ip = $row[$other_ip_txt];
		array_push($ip_array,$ip);
		$packets[$ip] = array();//creates an array for this IP address
	}
	mysql_free_result($mysql_result);
	/*END GET LIST OF IP's*/

	$END_MIN = floor(date("U")/60);//upper bound for x-axis (time of day)
	$END_MIN = $END_MIN - ($END_MIN%5);
	$START_MIN = $END_MIN - (24*60);//lower bound for x-axis (time of day)
	
	for( $i = $START_MIN; $i < $END_MIN; $i=$i+5){//goes through each 5-minute interval
		$time = date("H:i",$i*60);//finds HH:MM version of it
		array_push($time_array,$time);//pushes time onto time array (for x-axis)
		foreach($ip_array as $ip){
			/*sets number of packets for an IP during this interval to zero*/
			$packets[$ip][$i] = 0; 
		}
	}

	/*FILL UP PACKET MATRIX*/
	/*select minutes, IP for a specified port*/
	$sql_que = "SELECT minutes,$other_ip_txt FROM $mysql_table "
			. "WHERE ($port_txt = $port) AND ($ip_eq_text) ORDER BY minutes ASC";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}
	while($row = mysql_fetch_array($mysql_result)){//get data row from MySQL
		$interval = $row['minutes'] - ($row['minutes'] % 5);//5-minute intervals
		$ip = $row[$other_ip_txt];

		//increase number of packets for this IP during this interval
		$packets[$ip][$interval]++;

		//increase total number of packets for this interval
		$packets["TOTAL"][$interval]++;
	}
	mysql_free_result($mysql_result);
	/*END FILL UP OF PACKET MATRIX*/

	mysql_close($db);

	$num_ips = count($ip_array);
	$width = 900 + 140*ceil($num_ips/15);

	// Create the graph.
	$graph = new Graph($width,400,'auto');/*params: length, height, auto = cached file name*/
	$graph->SetScale("textint");/*text scale for x-axis, linear scale for y-axis*/

	/*some valid colors for this library*/
	$colors = array( "brown","red","black","blue","blueviolet","cadetblue4",
			"chartreuse2","darkgoldenrod3",	"chocolate3","darkgoldenrod4",
			"darkolivegreen","darkred","gray1","hotpink","midnightblue");
	$n = count($colors);

	$i=0;
	foreach($ip_array as $ip){//for each IP
		/*put packet data for that IP into $ydata*/
		$ydata = array();
		foreach($packets[$ip] as $pkts){
			array_push($ydata,$pkts);
		}
		
		// Create the linear plot
		$sp[$i] =new LinePlot($ydata);//new linear plot
		$sp[$i] ->SetColor($colors[$i%$n]);//sets color to next color
		$sp[$i] ->SetLegend($ip);//adds IP to legend

		// Add the plot to the graph
		$graph->Add( $sp[$i]); //adds plot to graph
		unset($ydata);//deletes data from $ydata
		$i++;
	}
	$graph->xaxis->SetTickLabels($time_array);//sets x-axis labels to time values
	$graph->xaxis->SetTextLabelInterval(30); //only displays every 20th label
	$graph->xaxis->SetTitle("Time of Day (24-hour clock)");//x-axis title
	//$graph->xaxis->SetTitleMargin(0);

	$graph->yaxis->SetTitle("NUMBER OF PACKETS per 5-minute interval");//y-axis title
	$graph->yaxis->SetTitleMargin(50);//places title 50 pixels away from y-axis

	//$graph->xaxis->scale->SetGrace(50,0);

	$graph->legend->Pos(0,0.1,"right","top");//sets position of legend
	$num_ips = count($ip_array);
	$graph->legend->SetColumns(ceil($num_ips/15));//so legend is displayed within graph

	/*set margin: left,right, top, bottom*/
	$graph->img->SetMargin(70,140*ceil($num_ips/15),20,40);

	if($port == 0){$port = "ICMP";}
	if($_GET['action'] == 'display_bg_ip'){
		if($type == "dst"){
			$title = "Background Traffic over Time to Port $port";
		}else{
			$title = "Background Traffic over Time from Port $port";
		}
	}else{
		if($type == "dst"){
			$title = "Incoming Packets over Time on Port $port";
		}else{$title = "Outgoing Packets over Time on Port $port";
		}
	}
	$graph->title->Set($title); 
	$subtitle = "generated at " . date("g:iA") . " on " . date("F d, Y");
	$graph->subtitle->Set($subtitle);//subtitle
	$graph->Stroke();
?>
