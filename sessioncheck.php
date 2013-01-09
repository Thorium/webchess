<?
	/* load settings */
	if (!isset($_CONFIG))
		require 'config.php';
	
	if (!isset($_SESSION['playerID']))
		$_SESSION['playerID'] = -1;

	if ($_SESSION['playerID'] != -1)
	{
		if (time() - $_SESSION['lastInputTime'] >= $CFG_SESSIONTIMEOUT)
			$_SESSION['playerID'] = -1;
		else if (!isset($_GET['autoreload']))
			$_SESSION['lastInputTime'] = time();
	}
	
	if ($_SESSION['playerID'] == -1)
		die("Session timed out.  Please <a href='index.php'>login again</a> to continue.");
?>

