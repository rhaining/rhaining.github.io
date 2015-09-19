#!/usr/local/bin/php

<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		runs tcpdump for 60 seconds
		kills tcpdump
		calls dumpToDB.php
	*/
	set_time_limit(0);//infinitely long process
	$output = "output.dat";//where to output data to
	$cmd = "/usr/sbin/tcpdump -i eth0 -nn > $output";//tcpdump command

	$init = true;
	while(true){
		$proc = popen($cmd, 'r');//runs tcpdump
		sleep(60);//sleeps for 60 seconds
		system("killall tcpdump");//kills tcpdump
		pclose($proc);//closes php resource

		/*if not the first time through,close dumpToDB proc*/ 
		if(!$init){pclose($proc_dumpToDB);}
		else{$init=false;}//else set init to false

		/*creates process to dump data into DB*/
		$proc_dumpToDB = popen("./dumpToDB.php", "r");
	}
?>

