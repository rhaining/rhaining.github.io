<?php
	/*Outgoing Traffic Bar Graph, based on ports
		written by Rob Haining, Wake Forest University
		Spring 2004

		creates a bar graph of total packets in the last 24 hours,
		where each bar is a different port.
	*/

	/*include libraries*/
	include ( 'jpgraph/jpgraph.php');
	include ('jpgraph/jpgraph_bar.php'); 

	$port_data = file("/var/www/html/ports.txt");
	$port_hash = array();
	foreach($port_data as $port){
		$port_array = split(":",$port);
		$num = $port_array[0];
		$name = $port_array[1];
		$port_hash[$num] = trim($name);
	}

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$my_ip = "152.17.140.132";
	$port_type = "src_port";
	/*select protocol & port, where the port's between 0 & 1024*/
	$sql_que = "SELECT protocol, $port_type FROM $mysql_table "
			. "WHERE ($port_type >= 0) AND ($port_type < 1024) "
			. "AND (src_ip = \"$my_ip\") ORDER BY $port_type ASC";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}

	$port = -1;//current port #
	$sum=0;//sum of packets
	$ports = array("");//array of port #'s
	$packets = array("");//array of # of packets...correlated to $ports
	while($row = mysql_fetch_array($mysql_result)){//gets row from MySQL
		if($port == $row[$port_type]){//port is already in array
			$sum++;//increase # pkts by 1
		}else{//port is not in array
			$port = $row[$port_type];
			if($sum > 0){array_push($packets,$sum);}//put # pkts in $packets
			if($port == 0){array_push($ports,"ICMP");}
			else{array_push($ports,$port . "\n[" . $port_hash[$port] . "]");}//put port in $ports array
			$sum=1;//reset sum
		}
	}
	array_push($packets,$sum);//last remaining sum to push onto $packets

	mysql_free_result($mysql_result);

	/*select destination ports that are higher than 1024*/
	$sql_que = "SELECT $port_type FROM $mysql_table "
			. "WHERE ($port_type > 1023) AND (src_ip = \"$my_ip\")";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}
	/*we'll put all these ports together under one designation*/
	$others = mysql_num_rows($mysql_result);
	array_push($packets, $others);
	array_push($ports, "*");
	mysql_free_result($mysql_result);
	mysql_close($db);

	// Create the graph.
	$graph = new Graph(950,400,'auto'); //creates graph
	$graph->SetScale("textlin");//x-scale is text, y-scale in linear

	array_push($ports, "");
	$graph->xaxis->SetTickLabels($ports);//x-axis comes from $ports array

	// Create a bar pot
	$bplot = new BarPlot($packets);//new barplot based on $packets
	$bplot->SetFillColor("orange");

	$bplot->value->Show();//shows values at top of bars

	$graph->Add($bplot);//adds barplot to graph
	$graph->xaxis->SetTitle("DESTINATION PORTS");//xaxis title
	$graph->yaxis->SetTitle("NUMBER OF PACKETS");//yaxis title
	$graph->yaxis->SetTitleMargin(50);//pushes margin back so you can read it

	$graph->title->set("Number of Outgoing Packets over the Last 24 Hours");   
	$subtitle = "generated at " . date("g:iA") . " on " . date("F d, Y");
	$graph->subtitle->Set($subtitle);//subtitle

	$graph->Stroke("/var/www/html/outgoing_bar.png");//draws graph & places it in this file

?>
