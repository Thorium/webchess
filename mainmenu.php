<?
	session_start();

	/* load settings */
	if (!isset($_CONFIG))
		require 'config.php';

	/* load external functions for setting up new game */
	require 'chessutils.php';
	require 'chessconstants.php';
	require 'newgame.php';
	require 'chessdb.php';
	
	
	/* allow WebChess to be run on PHP systems < 4.1.0, using old http vars */
	fixOldPHPVersions();

	/* if this page is accessed directly (ie: without going through login), */
	/* player is logged off by default */
	if (!isset($_SESSION['playerID']))
		$_SESSION['playerID'] = -1;
	
	/* connect to database */
	require 'connectdb.php';

	/* cleanup dead games */
	/* determine threshold for oldest game permitted */
	$targetDate = date("Y-m-d", mktime(0,0,0, date('m'), date('d') - $CFG_EXPIREGAME, date('Y')));

	/* find out which games are older */
	$tmpQuery = "SELECT gameID FROM games WHERE lastMove < '".$targetDate."'";
	$tmpOldGames = mysql_query($tmpQuery);

	/* for each older game... */
	while($tmpOldGame = mysql_fetch_array($tmpOldGames, MYSQL_ASSOC))
	{
		/* ... clear the history... */
		mysql_query("DELETE FROM history WHERE gameID = ".$tmpOldGame['gameID']);

		/* ... and the board... */
		mysql_query("DELETE FROM pieces WHERE gameID = ".$tmpOldGame['gameID']);
		
		/* ... and the messages... */
		mysql_query("DELETE FROM messages WHERE gameID = ".$tmpOldGame['gameID']);
		
		/* ... and finally the game itself from the database */
		mysql_query("DELETE FROM games WHERE gameID = ".$tmpOldGame['gameID']);
	}
	
	$tmpNewUser = false;
	$errMsg = "";
	switch($_POST['ToDo'])
	{
		case 'NewUser':
			/* create new player */
			$tmpNewUser = true;
			
			/* sanity check: empty nick */
			if ($_POST['txtNick'] == "")
				die("ERROR: must supply a valid nick!");
			
			/* check for existing user with same nick */
			$tmpQuery = "SELECT playerID FROM players WHERE nick = '".$_POST['txtNick']."'";
			$existingUsers = mysql_query($tmpQuery);
			if (mysql_num_rows($existingUsers) > 0)
			{
				require 'newuser.php';
				die();
			}
			
			$tmpQuery = "INSERT INTO players (password, firstName, lastName, nick) VALUES ('".$_POST['pwdPassword']."', '".$_POST['txtFirstName']."', '".$_POST['txtLastName']."', '".$_POST['txtNick']."')";
			mysql_query($tmpQuery);

			/* get ID of new player */
			$_SESSION['playerID'] = mysql_insert_id();

			/* set History format preference */
			$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", 'history', '".$_POST['rdoHistory']."')";
			mysql_query($tmpQuery);
			
			/* set Theme preference */
			$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", 'theme', '".$_POST['rdoTheme']."')";
			mysql_query($tmpQuery);

			/* set auto-reload preference */
			if (is_numeric($_POST['txtReload']))
			{
				if (intval($_POST['txtReload']) >= $CFG_MINAUTORELOAD)
					$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", 'autoreload', ".$_POST['txtReload'].")";
				else
					$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", 'autoreload', ".$CFG_MINAUTORELOAD.")";
				
				mysql_query($tmpQuery);
			}

			/* set email notification preference */
			if ($CFG_USEEMAILNOTIFICATION)
			{
				$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", 'emailnotification', '".$_POST['txtEmailNotification']."')";
				mysql_query($tmpQuery);
			}
			
			/* no break, login user */
			
		case 'Login':
			/* check for a player with supplied nick and password */
			$tmpQuery = "SELECT * FROM players WHERE nick = '".$_POST['txtNick']."' AND password = '".$_POST['pwdPassword']."'";
			$tmpPlayers = mysql_query($tmpQuery);
			$tmpPlayer = mysql_fetch_array($tmpPlayers, MYSQL_ASSOC);

			/* if such a player exists, log him in... otherwise die */
			if ($tmpPlayer)
			{
				$_SESSION['playerID'] = $tmpPlayer['playerID'];
				$_SESSION['lastInputTime'] = time();
				$_SESSION['playerName'] = $tmpPlayer['firstName']." ".$tmpPlayer['lastName'];
				$_SESSION['firstName'] = $tmpPlayer['firstName'];
				$_SESSION['lastName'] = $tmpPlayer['lastName'];
				$_SESSION['nick'] = $tmpPlayer['nick'];
			}
			else
				die("Invalid Nick/Password");

			/* load user preferences */
			$tmpQuery = "SELECT * FROM preferences WHERE playerID = ".$_SESSION['playerID'];
			$tmpPreferences = mysql_query($tmpQuery);

			$isPreferenceFound['history'] = false;
			$isPreferenceFound['theme'] = false;
			$isPreferenceFound['autoreload'] = false;
			$isPreferenceFound['emailnotification'] = false;
			
			while($tmpPreference = mysql_fetch_array($tmpPreferences, MYSQL_ASSOC))
			{
				switch($tmpPreference['preference'])
				{
					case 'history':
					case 'theme':
						/* setup SESSION var of name pref_PREF, like pref_history */
						$_SESSION['pref_'.$tmpPreference['preference']] = $tmpPreference['value'];
						break;
					
					case 'emailnotification':
						if ($CFG_USEEMAILNOTIFICATION)
							$_SESSION['pref_emailnotification'] = $tmpPreference['value'];
						break;
						
					case 'autoreload':
						if (is_numeric($tmpPreference['value']))
						{
							if (intval($tmpPreference['value']) >= $CFG_MINAUTORELOAD)
								$_SESSION['pref_autoreload'] = intval($tmpPreference['value']);
							else
								$_SESSION['pref_autoreload'] = $CFG_MINAUTORELOAD;
						}
						else
							$_SESSION['pref_autoreload'] = $CFG_MINAUTORELOAD;
						break;
				}

				$isPreferenceFound[$tmpPreference['preference']] = true;
			}

			/* look for missing preference and fix */
			foreach (array_keys($isPreferenceFound, false) as $missingPref)
			{
				$defaultValue = "";
				switch($missingPref)
				{
					case 'history':
						$defaultValue = "pgn";
						break;
					case 'theme':
						$defaultValue = "beholder";
						break;
					case 'autoreload':
						$defaultValue = $CFG_MINAUTORELOAD;
						break;
					case 'emailnotification':
						$defaultValue = "";
						break;
				}
				$tmpQuery = "INSERT INTO preferences (playerID, preference, value) VALUES (".$_SESSION['playerID'].", '".$missingPref."', '".$defaultValue."')";
				mysql_query($tmpQuery);
				
				/* setup SESSION var of name pref_PREF, like pref_history */
				if ($CFG_USEEMAILNOTIFICATION || ($missingPref != 'emailnotification'))
					$_SESSION['pref_'.$missingPref] =  $defaultValue;
			}
			
			break;

		case 'Logout':
			$_SESSION['playerID'] = -1;
			die("You have succesfully logged out. Click <a href='index.php'>here</a> to log back in.");
			break;

		case 'InvitePlayer':
			/* prevent multiple pending requests between two players with the same originator */
			$tmpQuery = "SELECT gameID FROM games WHERE gameMessage = 'playerInvited'";
			$tmpQuery .= " AND ((messageFrom = 'white' AND whitePlayer = ".$_SESSION['playerID']." AND blackPlayer = ".$_POST['opponent'].")";
			$tmpQuery .= " OR (messageFrom = 'black' AND whitePlayer = ".$_POST['opponent']." AND blackPlayer = ".$_SESSION['playerID']."))";
			$tmpExistingRequests = mysql_query($tmpQuery);
			
			if (mysql_num_rows($tmpExistingRequests) == 0)
			{
				if (!minimum_version("4.2.0"))
					init_srand();
			
				if ($_POST['color'] == 'random')
					$tmpColor = (mt_rand(0,1) == 1) ? "white" : "black";
				else
					$tmpColor = $_POST['color'];

				$tmpQuery = "INSERT INTO games (whitePlayer, blackPlayer, gameMessage, messageFrom, dateCreated, lastMove) VALUES (";
				if ($tmpColor == 'white')
					$tmpQuery .= $_SESSION['playerID'].", ".$_POST['opponent'];
				else
					$tmpQuery .= $_POST['opponent'].", ".$_SESSION['playerID'];
			
				$tmpQuery .= ", 'playerInvited', '".$tmpColor."', NOW(), NOW())";
				mysql_query($tmpQuery);

				/* if email notification is activated... */
				if ($CFG_USEEMAILNOTIFICATION)
				{
					/* if opponent is using email notification... */
					$tmpOpponentEmail = mysql_query("SELECT value FROM preferences WHERE playerID = ".$_POST['opponent']." AND preference = 'emailNotification'");
					if (mysql_num_rows($tmpOpponentEmail) > 0)
					{
						$opponentEmail = mysql_result($tmpOpponentEmail, 0);
						if ($opponentEmail != '')
						{
							/* notify opponent of invitation via email */
							webchessMail('invitation', $opponentEmail, '', $_SESSION['nick']);
						}
					}
				}
			}
			break;

		case 'ResponseToInvite':
			if ($_POST['response'] == 'accepted')
			{
				/* update game data */
				$tmpQuery = "UPDATE games SET gameMessage = '', messageFrom = '' WHERE gameID = ".$_POST['gameID'];
				mysql_query($tmpQuery);

				/* setup new board */
				$_SESSION['gameID'] = $_POST['gameID'];
				createNewGame($_POST['gameID']);
				saveGame();
			}
			else
			{
				
				$tmpQuery = "UPDATE games SET gameMessage = 'inviteDeclined', messageFrom = '".$_POST['messageFrom']."' WHERE gameID = ".$_POST['gameID'];
				mysql_query($tmpQuery);
			}
			
			break;

		case 'WithdrawRequest':
				
			/* get opponent's player ID */
			$tmpOpponentID = mysql_query("SELECT whitePlayer FROM games WHERE gameID = ".$_POST['gameID']);
			if (mysql_num_rows($tmpOpponentID) > 0)
			{
				$opponentID = mysql_result($tmpOpponentID, 0);

				if ($opponentID == $_SESSION['playerID'])
				{
					$tmpOpponentID = mysql_query("SELECT blackPlayer FROM games WHERE gameID = ".$_POST['gameID']);
					$opponentID = mysql_result($tmpOpponentID, 0);
				}
			
				$tmpQuery = "DELETE FROM games WHERE gameID = ".$_POST['gameID'];
				mysql_query($tmpQuery);
			
				/* if email notification is activated... */
				if ($CFG_USEEMAILNOTIFICATION)
				{
					/* if opponent is using email notification... */
					$tmpOpponentEmail = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'emailNotification'");
					if (mysql_num_rows($tmpOpponentEmail) > 0)
					{
						$opponentEmail = mysql_result($tmpOpponentEmail, 0);
						if ($opponentEmail != '')
						{
							/* notify opponent of invitation via email */
							webchessMail('withdrawal', $opponentEmail, '', $_SESSION['nick']);
						}
					}
				}
			}
			break;

		case 'UpdatePersonalInfo':
			$tmpQuery = "SELECT password FROM players WHERE playerID = ".$_SESSION['playerID'];
			$tmpPassword = mysql_query($tmpQuery);
			$dbPassword = mysql_result($tmpPassword, 0);

			if ($dbPassword != $_POST['pwdOldPassword'])
				$errMsg = "Sorry, incorrect old password!";
			else
			{
				$tmpDoUpdate = true;

				if ($CFG_NICKCHANGEALLOWED)
				{
					$tmpQuery = "SELECT playerID FROM players WHERE nick = '".$_POST['txtNick']."' AND playerID <> ".$_SESSION['playerID'];
					$existingUsers = mysql_query($tmpQuery);
				
					if (mysql_num_rows($existingUsers) > 0)
					{
						$errMsg = "Sorry, that nick is already in use.";
						$tmpDoUpdate = false;
					}
				}
				
				if ($tmpDoUpdate)
				{
					/* update DB */
					$tmpQuery = "UPDATE players SET firstName = '".$_POST['txtFirstName']."', lastName = '".$_POST['txtLastName']."', password = '".$_POST['pwdPassword']."'";
					
					if ($CFG_NICKCHANGEALLOWED && $_POST['txtNick'] != "")
						$tmpQuery .= ", nick = '".$_POST['txtNick']."'";
					
					$tmpQuery .= " WHERE playerID = ".$_SESSION['playerID'];
					mysql_query($tmpQuery);

					/* update current session */
					$_SESSION['playerName'] = $_POST['txtFirstName']." ".$_POST['txtLastName'];
					$_SESSION['firstName'] = $_POST['txtFirstName'];
					$_SESSION['lastName'] = $_POST['txtLastName'];

					if ($CFG_NICKCHANGEALLOWED && $_POST['txtNick'] != "")
						$_SESSION['nick'] = $_POST['txtNick'];
				}
			}
			
			break;
		
		case 'UpdatePrefs':
			/* Theme */
			$tmpQuery = "UPDATE preferences SET value = '".$_POST['rdoTheme']."' WHERE playerID = ".$_SESSION['playerID']." AND preference = 'theme'";
			mysql_query($tmpQuery);
			
			/* History format */
			$tmpQuery = "UPDATE preferences SET value = '".$_POST['rdoHistory']."' WHERE playerID = ".$_SESSION['playerID']." AND preference = 'history'";
			mysql_query($tmpQuery);

			/* Auto-Reload */
			if (is_numeric($_POST['txtReload']))
			{
				if (intval($_POST['txtReload']) >= $CFG_MINAUTORELOAD)
					$tmpQuery = "UPDATE preferences SET value = ".$_POST['txtReload']." WHERE playerID = ".$_SESSION['playerID']." AND preference = 'autoreload'";
				else
					$tmpQuery = "UPDATE preferences SET value = ".$CFG_MINAUTORELOAD." WHERE playerID = ".$_SESSION['playerID']." AND preference = 'autoreload'";
				
				mysql_query($tmpQuery);
			}

			/* Email Notification */
			if ($CFG_USEEMAILNOTIFICATION)
			{
				$tmpQuery = "UPDATE preferences SET value = '".$_POST['txtEmailNotification']."' WHERE playerID = ".$_SESSION['playerID']." AND preference = 'emailnotification'";
				mysql_query($tmpQuery);
			}

			/* update current session */
			$_SESSION['pref_history'] = $_POST['rdoHistory'];
			$_SESSION['pref_theme'] =  $_POST['rdoTheme'];

			if (is_numeric($_POST['txtReload']))
			{
				if (intval($_POST['txtReload']) >= $CFG_MINAUTORELOAD)
				{
					$_SESSION['pref_autoreload'] = intval($_POST['txtReload']);
				}
				else
					$_SESSION['pref_autoreload'] = $CFG_MINAUTORELOAD;
			} else
				$_SESSION['pref_autoreload'] = $CFG_MINAUTORELOAD;

			if ($CFG_USEEMAILNOTIFICATION)
				$_SESSION['pref_emailnotification'] = $_POST['txtEmailNotification'];
			break;

		case 'TestEmail':
			if ($CFG_USEEMAILNOTIFICATION)
				webchessMail('test', $_SESSION['pref_emailnotification'], '', '');
			break;
	}

	/* check session status */
	require 'sessioncheck.php';

	/* set default playing mode to different PCs (as opposed to both players sharing a PC) */
	$_SESSION['isSharedPC'] = false;
?>

<html>
<head>
	<title>WebChess Main Menu</title>

	<script type="text/javascript">
		function validatePersonalInfo()
		{
			if (document.PersonalInfo.txtFirstName.value == ""
				|| document.PersonalInfo.txtLastName.value == ""
			<?
				/* ToDo: figure out how to check for whitespace only nicks */
				if ($CFG_NICKCHANGEALLOWED)
					echo ('|| document.PersonalInfo.txtNick.value == ""');
			?>
				|| document.PersonalInfo.pwdOldPassword.value == ""
				|| document.PersonalInfo.pwdPassword.value == "")
			{
				alert("Sorry, all personal info fields are required and must be filled out.");
				return;
			}

			if (document.PersonalInfo.pwdPassword.value == document.PersonalInfo.pwdPassword2.value)
				document.PersonalInfo.submit();
			else
				alert("Sorry, the two password fields don't match.  Please try again.");
		}
		
		function sendResponse(responseType, messageFrom, gameID)
		{
			document.responseToInvite.response.value = responseType;
			document.responseToInvite.messageFrom.value = messageFrom;
			document.responseToInvite.gameID.value = gameID;
			document.responseToInvite.submit();
		}

		function loadGame(gameID)
		{
			if (document.existingGames.rdoShare[0].checked)
				document.existingGames.action = "opponentspassword.php";

			document.existingGames.gameID.value = gameID;
			document.existingGames.submit();
		}

		function withdrawRequest(gameID)
		{
			document.withdrawRequestForm.gameID.value = gameID;
			document.withdrawRequestForm.submit();
		}

		function loadEndedGame(gameID)
		{
			document.existingGames.gameID.value = gameID;
			document.existingGames.submit();
		}

<? if ($CFG_USEEMAILNOTIFICATION) { ?>
		function testEmail()
		{
			document.preferences.ToDo.value = "TestEmail";
			document.preferences.submit();
		}
<? } ?>
	</script>
</head>

<body>

<h1>WebChess Main Menu</h1>

What would you like to do <? echo ($_SESSION['playerName']); ?>?

<ul>
<li> Edit Personal Information
<?
	if ($errMsg != "")
		echo("<p><h2><font color='red'>".$errMsg."</font></h2><p>\n");
?>

	<form name="PersonalInfo" action="mainmenu.php" method="post">
	<table border="1" width="450">
		<tr>
			<th colspan="2">Current Personal Information</th>
		</tr>

		<tr>
			<td width="200">
				First Name:
			</td>
			
			<td>
				<input name="txtFirstName" type="text" size="30" value="<? echo($_SESSION['firstName']); ?>">
			</td>
		</tr>

		<tr>
			<td>
				Last Name:
			</td>

			<td>
				<input name="txtLastName" type="text" size="30" value="<? echo($_SESSION['lastName']); ?>">
			</td>
		</tr>

		<? if ($CFG_NICKCHANGEALLOWED) { ?>
		<tr>
			<td>
				Nick:
			</td>

			<td>
				<input name="txtNick" size="30" type="text" value="<? echo($_SESSION['nick']); ?>">
			</td>
		</tr>
		<? } ?>

		<tr>
			<td>
				Old Password:
			</td>

			<td>
				<input name="pwdOldPassword" size="30" type="password">
			</td>
		</tr>

		<tr>
			<td>
				Password:
			</td>

			<td>
				<input name="pwdPassword" size="30" type="password">
			</td>
		</tr>

		<tr>
			<td>
				Password Confirmation:
			</td>

			<td>
				<input name="pwdPassword2" size="30" type="password">
			</td>
		</tr>
		
		<tr>
			<td colspan="2" align="center">
				<input type="button" value="Update Personal Info" onClick="validatePersonalInfo()">
			</td>
		</tr>
	</table>

	<input type="hidden" name="ToDo" value="UpdatePersonalInfo">
	</form>

<li> Edit Preferences
	<form name="preferences" action="mainmenu.php" method="post">
	<table border="1" width="450">
		<tr>
			<th colspan="2">Current Preferences</th>
		</tr>

		<tr>
			<td>History:</td>
			<td>
				<?
					if ($_SESSION['pref_history'] == 'pgn')
					{
				?>
						<input name='rdoHistory' type='radio' value='pgn' checked> PGN
						<br>
						<input name="rdoHistory" type="radio" value="verbous"> Verbose
				<?
					}
					else
					{
				?>
						<input name='rdoHistory' type='radio' value='pgn'> PGN
						<br>
						<input name="rdoHistory" type="radio" value="verbous" checked> Verbose
				<?	}
				?>
			</td>
		</tr>

		<tr>
			<td>Theme:</td>
			<td>
				<?
					if ($_SESSION['pref_theme'] == 'beholder')
					{
				?>
						<input name="rdoTheme" type="radio" value="beholder" checked> Beholder
						<br>
						<input name="rdoTheme" type="radio" value="plain"> Plain
				<?
					}
					else
					{
				?>
						<input name="rdoTheme" type="radio" value="beholder"> Beholder
						<br>
						<input name="rdoTheme" type="radio" value="plain" checked> Plain
				<?	}
				?>
				
				<br>
				NOTE: Beholder was graciously donated to WebChess by Dave Whiteland.
				<br>
				Please check out his website at <a href="http://www.beholder.co.uk">http://www.beholder.co.uk</a>.
			</td>
		</tr>

		<tr>
			<td>Auto-reload:</td>
			<td><input type="text" name="txtReload" value="<? echo ($_SESSION['pref_autoreload']); ?>"> (min: <? echo ($CFG_MINAUTORELOAD); ?> secs)</td>
		</tr>
		
		<? if ($CFG_USEEMAILNOTIFICATION) { ?>
		<tr valign="top">
			<td>Email notification:</td>
			<td>
				<input type="text" name="txtEmailNotification" value="<? echo ($_SESSION['pref_emailnotification']); ?>">
				<? if ($_SESSION['pref_emailnotification'] != "") { ?>
					<input type="button" name="btnTestEmailNotification" value="Test" onClick="testEmail()">
				<? } ?>
				<br>
				Enter a valid email address if you would like to be notified when your opponent makes a move.  Leave blank otherwise.
			</td>
		</tr>
		<? } ?>
		
		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="Update Preferences">
			</td>
		</tr>
	</table>

	<input type="hidden" name="ToDo" value="UpdatePrefs">
	</form>

<li> <a href="inviteplayer.php">Invite a player to play a new game</a>
	<form name="withdrawRequestForm" action="mainmenu.php" method="post">
	<table border="1" width="450">
	<tr>
		<th colspan="4">Current Invitations</th>
	</tr>

	<tr>
		<th>Opponent</th>
		<th>Your Color</th>
		<th>Status</th>
		<th>Withdraw Request</th>
	</tr>

<?	
	/* if game is marked playerInvited and the invite is from the current player */
	$tmpQuery = "SELECT * FROM games WHERE (gameMessage = 'playerInvited' AND ((whitePlayer = ".$_SESSION['playerID']." AND messageFrom = 'white') OR (blackPlayer = ".$_SESSION['playerID']." AND messageFrom = 'black'))";
	
	/* OR game is marked inviteDeclined and the response is from the opponent */
	$tmpQuery .= ") OR (gameMessage = 'inviteDeclined' AND ((whitePlayer = ".$_SESSION['playerID']." AND messageFrom = 'black') OR (blackPlayer = ".$_SESSION['playerID']." AND messageFrom = 'white')))  ORDER BY dateCreated";
	
	$tmpGames = mysql_query($tmpQuery);
	
	if (mysql_num_rows($tmpGames) == 0)
		echo("<tr><td colspan='4'>You have no current unanswered invitations</td></tr>\n");
	else
		while($tmpGame = mysql_fetch_array($tmpGames, MYSQL_ASSOC))
		{
			/* Opponent */
			echo("<tr><td>");
			/* get opponent's nick */
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['blackPlayer']);
			else
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['whitePlayer']);
			$opponent = mysql_result($tmpOpponent,0);
			echo($opponent);

			/* Your Color */
			echo ("</td><td>");
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
				echo ("White");
			else
				echo ("Black");

			/* Status */
			echo ("</td><td>");
			if ($tmpGame['gameMessage'] == 'playerInvited')
				echo ("Response pending");
			else if ($tmpGame['gameMessage'] == 'inviteDeclined')
				echo ("Invitation declined");
			
			/* Withdraw Request */
			echo ("</td><td align='center'>");
			echo ("<input type='button' value='Withdraw' onclick=\"withdrawRequest(".$tmpGame['gameID'].")\">");

			echo("</td></tr>\n");
		}
?>
	</table>
	<input type="hidden" name="gameID" value="">
	<input type="hidden" name="ToDo" value="WithdrawRequest">
	</form>
	<br>

<li> Respond to a player's invitation for a new game
	<form name="responseToInvite" action="mainmenu.php" method="post">
	<table border="1" width="450">
	<tr>
		<th>Opponent</th>
		<th>Your Color</th>
		<th>Response</th>
	</tr>

<?	
	$tmpQuery = "SELECT * FROM games WHERE gameMessage = 'playerInvited' AND ((whitePlayer = ".$_SESSION['playerID']." AND messageFrom = 'black') OR (blackPlayer = ".$_SESSION['playerID']." AND messageFrom = 'white')) ORDER BY dateCreated";
	$tmpGames = mysql_query($tmpQuery);
	
	if (mysql_num_rows($tmpGames) == 0)
		echo("<tr><td colspan='3'>You are not currently invited to any games</td></tr>\n");
	else
		while($tmpGame = mysql_fetch_array($tmpGames, MYSQL_ASSOC))
		{
			/* Opponent */
			echo("<tr><td>");
			/* get opponent's nick */
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['blackPlayer']);
			else
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['whitePlayer']);
			$opponent = mysql_result($tmpOpponent,0);
			echo($opponent);

			/* Your Color */
			echo ("</td><td>");
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
			{
				echo ("White");
				$tmpFrom = "white";
			}
			else
			{
				echo ("Black");
				$tmpFrom = "black";
			}

			/* Response */
			echo ("</td><td align='center'>");
			echo ("<input type='button' value='Accept' onclick=\"sendResponse('accepted', '".$tmpFrom."', ".$tmpGame['gameID'].")\">");
			echo ("<input type='button' value='Decline' onclick=\"sendResponse('declined', '".$tmpFrom."', ".$tmpGame['gameID'].")\">");

			echo("</td></tr>\n");
		}
?>
	</table>
	<input type="hidden" name="response" value="">
	<input type="hidden" name="messageFrom" value="">
	<input type="hidden" name="gameID" value="">
	<input type="hidden" name="ToDo" value="ResponseToInvite">
	</form>

<li> Continue an existing game:

	<form name="existingGames" action="chess.php" method="post">
	<table border="1" width="450">
	<tr>
		<th>Opponent</th>
		<th>Your Color</th>
		<th>Current Turn</th>
		<th>Start Date</th>
		<th>Last Move</th>
	</tr>
<?
	$tmpGames = mysql_query("SELECT * FROM games WHERE gameMessage = '' AND (whitePlayer = ".$_SESSION['playerID']." OR blackPlayer = ".$_SESSION['playerID'].") ORDER BY dateCreated");
	
	if (mysql_num_rows($tmpGames) == 0)
		echo("<tr><td colspan='6'>You do not currently have any games in progress</td></tr>\n");
	else
	{
		while($tmpGame = mysql_fetch_array($tmpGames, MYSQL_ASSOC))
		{
			/* Opponent */
			echo("<tr><td>");
			/* get opponent's nick */
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['blackPlayer']);
			else
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['whitePlayer']);
			$opponent = mysql_result($tmpOpponent,0);
			
			echo("<a href='javascript:loadGame(".$tmpGame['gameID'].")'>".$opponent."</a>");

			/* Your Color */
			echo ("</td><td>");
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
			{
				echo ("White");
				$tmpColor = "white";
			}
			else
			{
				echo ("Black");
				$tmpColor = "black";
			}

			/* Current Turn */
			echo ("</td><td>");
			/* get number of moves from history */
			$tmpNumMoves = mysql_query("SELECT COUNT(gameID) FROM history WHERE gameID = ".$tmpGame['gameID']);
			$numMoves = mysql_result($tmpNumMoves,0);

			/* based on number of moves, output current color's turn */
			if (($numMoves % 2) == 0)
				$tmpCurMove = "white";
			else
				$tmpCurMove = "black";

			if ($tmpCurMove == $tmpColor)
				echo("Your move");
			else
				echo("Opponent's move");

			/* Start Date */
			echo ("</td><td>".$tmpGame['dateCreated']);

			/* Last Move */
			echo ("</td><td>".$tmpGame['lastMove']."</td></tr>\n");
		}
		
		/* share PC */
		echo ("<tr><td colspan='3'>Will both players play from the same PC?</td>");
		echo ("<td><input type='radio' name='rdoShare' value='yes'> Yes</td>");
		echo ("<td><input type='radio' name='rdoShare' value='no' checked> No</td></tr>\n");
	}
?>
	</table>

		<input type="hidden" name="gameID" value="">
		<input type="hidden" name="sharePC" value="no">
	</form>

<li> View a game that's ended:

	<form name="endedGames" action="chess.php" method="post">
	<table border="1" width="450">
	<tr>
		<th>Opponent</th>
		<th>Your Color</th>
		<th>Status</th>
		<th>Start Date</th>
		<th>Last Move</th>
	</tr>
<?
	$tmpGames = mysql_query("SELECT * FROM games WHERE (gameMessage <> '' AND gameMessage <> 'playerInvited' AND gameMessage <> 'inviteDeclined') AND (whitePlayer = ".$_SESSION['playerID']." OR blackPlayer = ".$_SESSION['playerID'].") ORDER BY lastMove DESC");
	
	if (mysql_num_rows($tmpGames) == 0)
		echo("<tr><td colspan='6'>You do not currently have any games in progress</td></tr>\n");
	else
	{
		while($tmpGame = mysql_fetch_array($tmpGames, MYSQL_ASSOC))
		{
			/* Opponent */
			echo("<tr><td>");
			/* get opponent's nick */
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['blackPlayer']);
			else
				$tmpOpponent = mysql_query("SELECT nick FROM players WHERE playerID = ".$tmpGame['whitePlayer']);
			$opponent = mysql_result($tmpOpponent,0);
			
			echo("<a href='javascript:loadEndedGame(".$tmpGame['gameID'].")'>".$opponent."</a>");

			/* Your Color */
			echo ("</td><td>");
			if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
			{
				echo ("White");
				$tmpColor = "white";
			}
			else
			{
				echo ("Black");
				$tmpColor = "black";
			}

			/* Status */
			if (is_null($tmpGame['gameMessage']))
				echo("</td><td>&nbsp;");
			else
			{
				if ($tmpGame['gameMessage'] == "draw")
					echo("</td><td>Ended in draw");
				else if ($tmpGame['gameMessage'] == "playerResigned")
					echo("</td><td>".$tmpGame['messageFrom']." has resigned");
				else if (($tmpGame['gameMessage'] == "checkMate") && ($tmpGame['messageFrom'] == $tmpColor))
					echo("</td><td>Checkmate, you won!");
				else if ($tmpGame['gameMessage'] == "checkMate")
					echo("</td><td>Checkmate, you lost");
				else
					echo("</td><td>&nbsp;");
			}
			
			/* Start Date */
			echo ("</td><td>".$tmpGame['dateCreated']);

			/* Last Move */
			echo ("</td><td>".$tmpGame['lastMove']."</td></tr>\n");
		}
	}
?>
	</table>

		<input type="hidden" name="gameID" value="">
		<input type="hidden" name="sharePC" value="no">
	</form>
</ul>

<b>WARNING!</b>
<br>
Games will expire WITHOUT NOTICE if a move isn't made after <? echo ($CFG_EXPIREGAME); ?> days!


<form name="logout" action="mainmenu.php" method="post">
	<input type="hidden" name="ToDo" value="Logout">
	<input type="button" name="btnReload" value="Reload" onClick="window.open('mainmenu.php', '_self')">
	<input type="submit" value="Logout">
</form>

</body>
</html>

<? mysql_close(); ?>

