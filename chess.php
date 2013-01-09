<?
	session_start();

	/* load settings */
	if (!isset($_CONFIG))
		require 'config.php';
	
	/* define constants */
	require 'chessconstants.php';

	/* include outside functions */
	if (!isset($_CHESSUTILS))
		require 'chessutils.php';
	require 'gui.php';
	require 'chessdb.php';
	require 'move.php';
	require 'undo.php';

	/* allow WebChess to be run on PHP systems < 4.1.0, using old http vars */
	fixOldPHPVersions();

	/* check session status */
	require 'sessioncheck.php';
	
	/* check if loading game */
	if (isset($_POST['gameID']))
		$_SESSION['gameID'] = $_POST['gameID'];
	
	/* debug flag */
	define ("DEBUG", 0);

	/* connect to database */
	require 'connectdb.php';

	/* load game */
	$isInCheck = ($_POST['isInCheck'] == 'true');
	$isCheckMate = false;
	$isPromoting = false;
	$isUndoing = false;
	loadHistory();
	loadGame();
	processMessages();

	if ($isUndoing)
	{
		doUndo();
		saveGame();
	}
	elseif (($_POST['promotion'] != "") && ($_POST['toRow'] != "") && ($_POST['toCol'] != ""))
	{
		savePromotion();
		$board[$_POST['toRow']][$_POST['toCol']] = $_POST['promotion'] | ($board[$_POST['toRow']][$_POST['toCol']] & BLACK);
		saveGame();
	}
	elseif (($_POST['fromRow'] != "") && ($_POST['fromCol'] != "") && ($_POST['toRow'] != "") && ($_POST['toCol'] != ""))
	{
		/* ensure it's the current player moving				 */
		/* NOTE: if not, this will currently ignore the command...               */
		/*       perhaps the status should be instead?                           */
		/*       (Could be confusing to player if they double-click or something */
		$tmpIsValid = true;
		if (($numMoves == -1) || ($numMoves % 2 == 1))
		{
			/* White's move... ensure that piece being moved is white */
			if ((($board[$_POST['fromRow']][$_POST['fromCol']] & BLACK) != 0) || ($board[$_POST['fromRow']][$_POST['fromCol']] == 0))
				/* invalid move */
				$tmpIsValid = false;
		}
		else
		{
			/* Black's move... ensure that piece being moved is black */
			if ((($board[$_POST['fromRow']][$_POST['fromCol']] & BLACK) != BLACK) || ($board[$_POST['fromRow']][$_POST['fromCol']] == 0))
				/* invalid move */
				$tmpIsValid = false;
		}
		
		if ($tmpIsValid)
		{
			saveHistory();
			doMove();
			saveGame();
		}
	}

	mysql_close();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?
	/* find out if it's the current player's turn */
	if (( (($numMoves == -1) || (($numMoves % 2) == 1)) && ($playersColor == "white"))
			|| ((($numMoves % 2) == 0) && ($playersColor == "black")))
		$isPlayersTurn = true;
	else
		$isPlayersTurn = false;
	
	if ($_SESSION['isSharedPC'])
		echo("<title>WebChess</title>\n");
	else if ($isPlayersTurn)
		echo("<title>WebChess - Your Move</title>\n");
	else
		echo("<title>WebChess - Opponent's Move</title>\n");
	
	echo("<meta HTTP-EQUIV='Pragma' CONTENT='no-cache'>\n");
	
	/* if it's not the player's turn, enable auto-refresh */
	if (!$isPlayersTurn && !isBoardDisabled() && !$_SESSION['isSharedPC'])
	{
		echo ("<META HTTP-EQUIV=Refresh CONTENT='");

		if ($_SESSION['pref_autoreload'] >= $CFG_MINAUTORELOAD)
			echo ($_SESSION['pref_autoreload']);
		else
			echo ($CFG_MINAUTORELOAD);

		echo ("; URL=chess.php?autoreload=yes'>\n");
	}	
?>

<script type="text/javascript">
/* transfer board data to javacripts */
<? writeJSboard(); ?>
<? writeJShistory(); ?>

if (DEBUG)
	alert("Game initilization complete!");
</script>

<script type="text/javascript" src="javascript/chessutils.js">
 /* these are utility functions used by other functions */
</script>

<script type="text/javascript" src="javascript/commands.js">
// these functions interact with the server
</script>

<script type="text/javascript" src="javascript/validation.js">
// these functions are used to test the validity of moves
</script>

<script type="text/javascript" src="javascript/isCheckMate.js">
// these functions are used to test the validity of moves
</script>

<script type="text/javascript" src="javascript/squareclicked.js">
// this is the main function that interacts with the user everytime they click on a square
</script>
</head>

<body>

<table border="0">
<tr valign="top" align="center"><td>

	<form name="gamedata" method="post" action="chess.php">

	<?
		if ($isPromoting)
			writePromotion();
	?>

	<?
		if ($isUndoRequested)
			writeUndoRequest();
	?>

	<?
		if ($isDrawRequested)
			writeDrawRequest();
	?>

	<? drawboard(); ?>

	<!-- table border="0">
	<tr><td -->
	<nobr>
	<input type="button" name="btnReload" value="Reload" onClick="window.open('chess.php', '_self')">
	<input type="button" name="btnUndo" value="Request Undo" <? if (isBoardDisabled()) echo("disabled='yes'"); else echo ("onClick='undo()'"); ?>>
	<input type="button" name="btnDraw" value="Request Draw" <? if (isBoardDisabled()) echo("disabled='yes'"); else echo ("onClick='draw()'"); ?>>
	<input type="button" name="btnResign" value="Resign" <? if (isBoardDisabled()) echo("disabled='yes'"); else echo ("onClick='resigngame()'"); ?>>
	<input type="button" name="btnMainMenu" value="Main Menu" onClick="window.open('mainmenu.php', '_self')">
	<input type="button" name="btnLogout" value="Logout" onClick="logout()">
	<input type="hidden" name="ToDo" value="Logout">	<!-- NOTE: this field is only used to Logout -->
	</nobr>
	<!-- /td></tr>
	</table -->

	<input type="hidden" name="requestUndo" value="no">
	<input type="hidden" name="requestDraw" value="no">
	<input type="hidden" name="resign" value="no">
	<input type="hidden" name="fromRow" value="<? if (isPromoting) echo ($_POST['fromRow']); ?>">
	<input type="hidden" name="fromCol" value="<? if (isPromoting) echo ($_POST['fromCol']); ?>">
	<input type="hidden" name="toRow" value="<? if (isPromoting) echo ($_POST['toRow']); ?>">
	<input type="hidden" name="toCol" value="<? if (isPromoting) echo ($_POST['toCol']); ?>">
	<input type="hidden" name="isInCheck" value="false">
	<input type="hidden" name="isCheckMate" value="false">
	</form>

	<p>Note: when castling, just move the king (the rook will move automatically).</p>
</td>

<td width="50">&nbsp;</td>

<td>
	<? writeStatus(); ?>
	<br>
	<? writeHistory(); ?>
</td></tr>
</table>

</body>
</html>

