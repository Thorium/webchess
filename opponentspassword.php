<?	
	session_start();

	if (!isset($_CHESSUTILS))
		require 'chessutils.php';
		
	fixOldPHPVersions();
	
	/* check session status */
	require 'sessioncheck.php';
	
	/* connect to database */
	require 'connectdb.php';

	/* invalid password flag */
	$isInvalidPassword = false;
	
	/* check if submitting opponents login information */
	if (isset($_POST['opponentsID']))
	{
		$opponentsID = $_POST['opponentsID'];
		$opponentsNick = $_POST['opponentsNick'];

		/* get opponents password from DB */
		$tmpQuery = "SELECT password FROM players WHERE playerID = ".$opponentsID;
		$tmpPassword = mysql_query($tmpQuery);
		$dbPassword = mysql_result($tmpPassword, 0);

		/* check to see if supplied password matched that of the DB */
		if ($dbPassword == $_POST['pwdPassword'])
		{
			$_SESSION['isSharedPC'] = true;

			/* load game */
			require 'chess.php';
			die();
		}
		/* else password is invalid */
		else
			/* set flag to true */
			$isInvalidPassword = true;
		
	}
	/* else user is arriving here for the first time */
	else
	{
		/* get the players associated with this game */
		$tmpQuery = "SELECT whitePlayer, blackPlayer FROM games WHERE gameID = ".$_POST['gameID'];
		$tmpGameData = mysql_query($tmpQuery);
		$tmpPlayers = mysql_fetch_array($tmpGameData, MYSQL_ASSOC);
	
		/* determine which one is the opponent of the player logged in */
		if ($tmpPlayers['whitePlayer'] == $_SESSION['playerID'])
			$opponentsID = $tmpPlayers['blackPlayer'];
		else
			$opponentsID = $tmpPlayers['whitePlayer'];
	
		/* get the opponents information */
		$tmpQuery = "SELECT nick FROM players WHERE playerID = ".$opponentsID;
		$tmpNick = mysql_query($tmpQuery);
		$opponentsNick = mysql_result($tmpNick, 0);
	}

	mysql_close();
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>WebChess Login</title>
</head>

<body>

<?
	if ($isInvalidPassword)
		echo("<h2>INVALID PASSWORD!!!  TRY AGAIN!</h2>\n");
?>

<h2>Enter password for <? echo($opponentsNick); ?>:</h2>

<form method="post" action="opponentspassword.php">
<p>
	Password: <input name="pwdPassword" type="password" size="15" />
	
	<input name="opponentsNick" type="hidden" value="<? echo($opponentsNick); ?>" />
	<input name="opponentsID" type="hidden" value="<? echo($opponentsID); ?>" />
	<input name="gameID" value="<? echo ($_POST['gameID']); ?>" type="hidden" />
</p>

<p>
	<input value="Continue" type="submit" />
	<input value="Cancel" type="button" onClick="window.open('mainmenu.php', '_self')"/>
</p>
</form>

</body>
</html>
