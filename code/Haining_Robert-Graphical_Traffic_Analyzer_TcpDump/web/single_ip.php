<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		outputs a graph of packets over time for a specified IP & port
	*/
	/*basic check to make sure correct data is being sent to script*/
	if($_GET['action'] == 'display_ip'){
		$port = $_GET['port'];
		$type = $_GET['type'];
		$ip = $_GET['ip'];

		if($type == "src"){$other_ip_txt = "dst_ip";}
		else{$other_ip_txt = "src_ip";}
	}else if($_GET['action'] == 'display_bg_ip'){
		$port = $_GET['port'];
		$type = $_GET['type'];
		$ip = $_GET['ip'];

		if($type == "dst"){$other_ip_txt = "dst_ip";}
		else{$other_ip_txt = "src_ip";}
	}else{exit();}



	/*necessary libraries*/
	include ( 'jpgraph/jpgraph.php');
	include ('jpgraph/jpgraph_line.php'); 

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$port_txt = $type . "_port";
	$ip_txt = $type . "_ip";

	$packets = array();/*packet matrix...rows will be IPs, and cols will be time intervals*/
	$time_array = array();/*array of times...x-axis*/

	$END_MIN = floor(date("U")/60);//upper bound for x-axis (time of day)
	$END_MIN = $END_MIN - ($END_MIN%5);
	$START_MIN = $END_MIN - (24*60);//lower bound for x-axis (time of day)

	for( $i = $START_MIN; $i < $END_MIN; $i=$i+5){//goes through each 5-minute interval
		$time = date("H:i",$i*60);//finds HH:MM version of it
		array_push($time_array,$time);//pushes time onto time array (for x-axis)

		/*sets number of packets during this interval to zero*/
		$packets[$i] = 0;
	}

	/*FILL UP PACKET MATRIX*/
	/*select minutes for a specified port & IP*/
	$sql_que = "SELECT minutes FROM $mysql_table "
			. "WHERE ($port_txt = $port) AND ($other_ip_txt = \"$ip\") "
			. "ORDER BY minutes ASC";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}
	while($row = mysql_fetch_array($mysql_result)){//get data row from MySQL
		$interval = $row['minutes'] - ($row['minutes'] % 5);//5-minute intervals

		if($packets[$interval] == 0){$NUM_PTS++;}

		//increase number of packets during this interval
		$packets[$interval]++;
	}
	mysql_free_result($mysql_result);
	/*END FILL UP OF PACKET MATRIX*/

	mysql_close($db);

	// Create the graph.
	$graph = new Graph(950,400,'auto');/*params: length, height, autosize*/ 
	$graph->SetScale("textint");/*text scale for x-axis, integer scale for y-axis*/

	/*put packet data for that IP into $ydata*/
	$ydata = array();
	foreach($packets as $pkt){array_push($ydata,$pkt);}

	// Create the linear plot
	$sp =new LinePlot($ydata);

	// Add the plot to the graph
	$graph->Add( $sp ); 

	$graph->xaxis->SetTickLabels($time_array);//sets x-axis labels to time values
	$graph->xaxis->SetTextLabelInterval(20); //only displays every 20th label
	$graph->xaxis->title->Set("Time of Day (24-hour clock)");//x-axis title
	//$graph->xaxis->SetTitleMargin(0);

	$graph->yaxis->SetTitle("NUMBER OF PACKETS per 5-minute interval");//y-axis title
	$graph->yaxis->SetTitleMargin(50);//places title 50 pixels away from y-axis

	//$graph->xaxis->scale->SetGrace(50,0);
	$graph->img->SetMargin(70,35,20,40);
	if($port == 0){$port = "ICMP";}

	if($_GET['action'] == "display_bg_ip"){
		if($type == "src"){
			$title = "Background Traffic over Time from IP $ip on Port $port";
		}else{
			$title = "Background Traffic over Time to IP $ip on Port $port";
		}
	}else{
		if($type == "src"){
			$title = "Incoming Packets over Time from IP $ip on Port $port";
		}else{
			$title = "Outgoing Packets over Time to IP $ip on Port $port";
		}
	}
	$graph->title->Set($title); 
	$subtitle = "generated at " . date("g:iA") . " on " . date("F d, Y");
	$graph->subtitle->Set($subtitle);//subtitle

	$graph->Stroke();
?>
