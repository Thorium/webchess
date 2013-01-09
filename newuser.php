<?
	session_start();
	
	/* load settings */
	if (!isset($_CONFIG))
		require 'config.php';
	
	if (!isset($_CHESSUTILS))
		require 'chessutils.php';
	
	fixOldPHPVersions();
?>

<html>
<head>
	<title>Create New User</title>

	<script type="text/javascript">
		function validateForm()
		{
			/* ToDo: figure out how to check for whitespace only nicks */
			if (document.userdata.txtFirstName.value == ""
				|| document.userdata.txtLastName.value == ""
				|| document.userdata.txtNick.value == ""
				|| document.userdata.pwdPassword.value == "")
			{
				alert("Sorry, all personal info fields are required and must be filled out.");
				return;
			}

			if (document.userdata.pwdPassword.value == document.userdata.pwdPassword2.value)
				document.userdata.submit();
			else
				alert("Sorry, the two password fields don't match.  Please try again.");
		}
	</script>
</head>

<body>
	<h1>Create New User</h1>

	<?
		/* this var is set to true in mainmenu.php */
		if ($tmpNewUser)
			echo("<p>Sorry, the nick you've chosen (".$_POST['txtNick'].") is already in use.  Please try another.</p>\n\n");
	?>
	
	<form name="userdata" method="post" action="mainmenu.php">
	<table>
		<td>
			<th colspan="2">PERSONAL INFO</th>
		</td>

		<tr>
			<td width="200">
				First Name:
			</td>
			
			<td>
				<input name="txtFirstName" type="text" value="<? echo($_POST['txtFirstName']); ?>">
			</td>
		</tr>

		<tr>
			<td>
				Last Name:
			</td>

			<td>
				<input name="txtLastName" type="text" value="<? echo($_POST['txtLastName']); ?>">
			</td>
		</tr>

		<tr>
			<td>
				Nick:
			</td>

			<td>
				<input name="txtNick" type="text">
			</td>
		</tr>

		<tr>
			<td>
				Password:
			</td>

			<td>
				<input name="pwdPassword" type="password">
			</td>
		</tr>

		<tr>
			<td>
				Password Confirmation:
			</td>

			<td>
				<input name="pwdPassword2" type="password">
			</td>
		</tr>

		<tr>
			<th colspan="2">PERSONAL PREFERENCES</th>
		</tr>
		
		<tr valign="top">
			<td>
				History:
			</td>

			<td>
				<?

				?>
				
				<input name="rdoHistory" type="radio" value="pgn" checked> PGN
				<br>
				<input name="rdoHistory" type="radio" value="verbous"> Verbose
			</td>
		</tr>

		<tr valign="top">
			<td>
				Theme:
			</td>

			<td>
				<input name="rdoTheme" type="radio" value="beholder" checked> Beholder
				<br>
				<input name="rdoTheme" type="radio" value="plain"> Plain
				<br>
				NOTE: Beholder was graciously donated to WebChess by Dave Whiteland.
				<br>
				Please check out his website at <a href="http://www.beholder.co.uk">http://www.beholder.co.uk</a>.
			</td>
		</tr>

		<tr>
			<td>Auto-reload:</td>
			<td><input type="text" name="txtReload" value="<? echo ($CFG_MINAUTORELOAD); ?>"> (min: <? echo ($CFG_MINAUTORELOAD); ?> secs)</td>
		</tr>

		<? if ($CFG_USEEMAILNOTIFICATION) { ?>
		<tr valign="top">
			<td>Email notification:</td>
			<td>
				<input type="text" name="txtEmailNotification" value="<? echo($_POST['txtEmailNotification']); ?>">
				<br>
				Enter a valid email address if you would like to be notified when your opponent makes a move.  Leave blank otherwise.
			</td>
		</tr>
		<? } ?>
		
		<tr>
			<td colspan="2">
				<input name="btnCreate" type="button" value="Create" onClick="validateForm()">
				<input name="btnCancel" type="button" value="Cancel" onClick="window.open('index.php', '_self')">
			</td>
		</tr>
		</table>

		<input name="ToDo" value="NewUser" type="hidden">
	</form>
</body>
</html>
