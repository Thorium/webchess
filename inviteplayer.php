<?
	session_start(); 

	/* neededfor fixOldPHPVersions() below */
	require 'chessutils.php';
	
	/* allow WebChess to be run on PHP systems < 4.1.0, using old http vars */
	fixOldPHPVersions();
?>

<html>
<head>
	<title>WebChess - Invite Player</title>
</head>

<body>

<h1>Invite a player to play a new game</h1>

<form action="mainmenu.php" method="post">	
	<input type="hidden" name="ToDo" value="InvitePlayer">

	<table>
		<tr>
			<td>Player to invite:</td>
			<td>
				<select name="opponent">
				<?
					/* connect to the database */
					require 'connectdb.php';

					$tmpQuery="SELECT playerID, nick FROM players WHERE playerID <> ".$_SESSION['playerID'];
					$tmpPlayers = mysql_query($tmpQuery);

					while($tmpPlayer = mysql_fetch_array($tmpPlayers, MYSQL_ASSOC))
					{
						echo ("<option value='".$tmpPlayer['playerID']."'> ".$tmpPlayer['nick']."</option>\n");
					}

					mysql_close();
				?>
				</select>
			</td>
		</tr>

		<tr valign="top">
			<td>Your color:</td>
			<td>
				<input type="radio" name="color" value="random" checked> Random
				<br>
				<input type="radio" name="color" value="white"> White
				<br>
				<input type="radio" name="color" value="black"> Black
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<input type="submit" value="Invite">
				<input type="button" value="Cancel" onClick="window.open('mainmenu.php', '_self')">
			</td>
		</tr>
	</table>
</form>

</body>
</html>
