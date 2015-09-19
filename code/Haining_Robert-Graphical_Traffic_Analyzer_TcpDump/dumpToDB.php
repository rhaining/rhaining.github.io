#!/usr/local/bin/php

<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		takes tcpdump data from output.dat,
		parses it,
		and inserts it into MySQL db
	*/
	$DEBUG = 1;//debug value

	if($DEBUG){$start = time();}

	/*connect to db...host, name, pw*/
	$db = mysql_connect("localhost", "rob", "rainermaria");
	mysql_select_db("tcpdump", $db);

	$mysql_table = "archive";

	/*minutes since Unix Epoch...minus one day of minutes*/
	/*=yesterday, at this time*/
	$minutes = floor(date("U") / 60) - (24*60);
	$sql_del = "DELETE FROM $mysql_table WHERE minutes < $minutes";
	if( mysql_query($sql_del) == FALSE) {
		print "Delete failed : " . mysql_error();
		exit;
	}

	$reg_time = "\d{2}:\d{2}:\d{2}";/*HH:MM:SS*/
	$reg_ip = "\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}";/*xxx.xxx.xxx.xxx*/
	$reg_port = "\d{1,5}";/*port #, 1-5 digits*/

	/*tcp pattern: HH:MM:SS:xxxxxx IP.port > IP.port: .*ack.*win*/
	$tcp_pattern = "($reg_time)\.\d{6} ($reg_ip)\.($reg_port) > ($reg_ip)\.($reg_port): .*ack.*win";

	/*icmp pattern: HH:MM:SS.xxxxxx IP > IP: icmp: echo.**/
	$icmp_pattern = "($reg_time)\.\d{6} ($reg_ip) > ($reg_ip): icmp: echo";

	/*udp pattern: HH:MM:SS.xxxxxx IP.port > IP.port: NBT UDP PACKET*/
	$udp_pattern = "($reg_time)\.\d{6} ($reg_ip)\.($reg_port) > ($reg_ip)\.($reg_port): NBT UDP PACKET";

	$tcp_output = fopen("output.dat","r");//reads file output.dat
	while( !feof($tcp_output) ){//while there's still something in there
		$line = fgets($tcp_output);//gets line by line
		if(preg_match("/$tcp_pattern/",$line, $matches)){//tries to match to TCP pattern
			$time = $matches[1];
			$src_ip = $matches[2];
			$src_port = $matches[3];
			$dst_ip = $matches[4];
			$dst_port = $matches[5];

			$date = date("Y-m-d");//current date
			/*at time of packet, # of minutes since Unix Epoch:*/
			$minutes = floor(date("U", strtotime($date . " " . $time)) / 60);

			$sql_ins = "INSERT INTO $mysql_table VALUES(\"$date\",\"$time\",
				\"$minutes\",\"TCP\",\"$src_ip\",$src_port,
				\"$dst_ip\",$dst_port)";
			if( mysql_query($sql_ins) == FALSE) {
				 print "Insert failed : " . mysql_error();
				exit;
			}
		}else if(preg_match("/$icmp_pattern/",$line,$matches)){//tries to match to ICMP pattern
			$time = $matches[1];
			$src_ip = $matches[2];
			$dst_ip = $matches[3];

			$date = date("Y-m-d");//current date
			/*at time of packet, # of minutes since Unix Epoch:*/
			$minutes = floor(date("U", strtotime($date . " " . $time)) / 60);

			$sql_ins = "INSERT INTO $mysql_table VALUES(\"$date\",\"$time\",
				\"$minutes\",\"ICMP\",\"$src_ip\",\"\",\"$dst_ip\",\"\")";
			if( mysql_query($sql_ins) == FALSE) {
				 print "Insert failed : " . mysql_error();
				exit;
			}
		}else if(preg_match("/$udp_pattern/",$line,$matches)){//tries to match UDP pattern
			$time = $matches[1];
			$src_ip = $matches[2];
			$src_port = $matches[3];
			$dst_ip = $matches[4];
			$dst_port = $matches[5];

			$date = date("Y-m-d");//current date
			/*at time of packet, # of minutes since Unix Epoch:*/
			$minutes = floor(date("U", strtotime($date . " " . $time)) / 60);

			$sql_ins = "INSERT INTO $mysql_table VALUES(\"$date\",\"$time\",
				\"$minutes\",\"UDP\",\"$src_ip\",$src_port,
				\"$dst_ip\",$dst_port)";
			if( mysql_query($sql_ins) == FALSE) {
				 print "Insert failed : " . mysql_error();
				exit;
			}

		}
	}
	fclose($tcp_output);
	mysql_close($db);

	if($DEBUG){
		$duration = time() - $start;
		print "$duration\n";
	}
?>
