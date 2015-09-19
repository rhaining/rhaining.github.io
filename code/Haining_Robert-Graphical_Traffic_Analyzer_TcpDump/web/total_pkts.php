<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		displays linear graph of total packets over time
	*/
	/*necessary libraries*/
	include ( 'jpgraph/jpgraph.php');
	include ('jpgraph/jpgraph_line.php'); 

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	$packets = array();//array of packets->intervals
	$time_array = array();//array of time

	$END_MIN = floor(date("U")/60);//upper bound for time/x-axis
	$END_MIN = $END_MIN - ($END_MIN%5);
	$START_MIN = $END_MIN - (24*60);//lower bound for time/x-axis

	/*scan through time intervals, keep track of times for x-axis,
		and initialize number of packets per interval to 0*/
	for( $i = $START_MIN; $i < $END_MIN; $i=$i+5){
		$time = date("H:i",$i*60);//converts interval to HH:MM
		array_push($time_array,$time);//pushes time onto array
		$packets[$i] = 0;//inits packets per interval to 0
	}

	/*FILL UP PACKET MATRIX*/
	/*select minutes, order ascending*/
	$sql_que = "SELECT minutes FROM $mysql_table "
			. "ORDER BY minutes ASC";
	if( ($mysql_result = mysql_query($sql_que)) == FALSE) {
		print "Query failed : " . mysql_error();
		exit();
	}
	while($row = mysql_fetch_array($mysql_result)){//get data row
		/*5-minute interval:*/
		$interval = $row['minutes'] - ($row['minutes'] % 5);
		$packets[$interval]++;//inc packets for this interval
	}
	mysql_free_result($mysql_result);
	/*END FILL UP OF PACKET MATRIX*/

	mysql_close($db);

	// Create the graph.
	$graph = new Graph(950,400,'auto'); /*params: length, width, autosize*/
	$graph->SetScale("textlin");/* x-axis is text scale, 
					y-axis is linear scale*/

	/*put packet data into $ydata*/
	$ydata = array();
	foreach($packets as $pkt){array_push($ydata,$pkt);}

	// Create the linear plot
	$sp =new LinePlot($ydata);

	// Add the plot to the graph
	$graph->Add( $sp ); 

	$graph->xaxis->SetTickLabels($time_array);//x-axis labels: time
	$graph->xaxis->SetTextLabelInterval(20); /*only display every 20th label*/
	$graph->xaxis->SetTitle("Time of Day (24-hour clock)");//x-axis title
	//$graph->xaxis->SetTitleMargin(0);

	$graph->yaxis->SetTitle("NUMBER OF PACKETS per 5-minute interval");/*yaxis title*/
	$graph->yaxis->SetTitleMargin(50);//set title 50 pixels away from y-axis

	//$graph->xaxis->scale->SetGrace(50,0);
	$graph->img->SetMargin(70,35,20,40);

	$graph->title->Set("Total Packets over Time");//main title 
	$subtitle = "generated at " . date("g:iA") . " on " . date("F d, Y");
	$graph->subtitle->Set($subtitle);//subtitle

	$graph->Stroke("/var/www/html/total_pkts.png");/*output to file*/
?>
