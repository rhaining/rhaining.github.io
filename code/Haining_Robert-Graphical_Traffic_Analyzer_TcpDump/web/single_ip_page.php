<html>
<head>
</head>
<body>

<?php
	/*Written by Rob Haining
		Wake Forest University
		Spring 2004

		displays packets over time for specified IP & port
	*/
	if( ($_POST['action'] != 'display_ip') && ($_POST['action'] != 'display_bg_ip') ){exit();}
	$port = $_POST['port'];
	$type = $_POST['type'];
	$ip = $_POST['ip'];
	print "<img src=\"single_ip.php?action=" . $_POST['action'] . "&port=$port&type=$type&ip=$ip\">\n";
?>
<form>
<INPUT TYPE="button" VALUE="Back" onClick="history.go(-1)">
</form>
</body>
</html>
