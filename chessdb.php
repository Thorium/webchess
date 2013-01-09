<? /* these functions are used to interact with the DB */
	function updateTimestamp()
	{
		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		mysql_query("UPDATE games SET lastMove = NOW() WHERE gameID = ".$_SESSION['gameID']);
	}

	function loadHistory()
	{
		global $history, $numMoves;
		
		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		$allMoves = mysql_query("SELECT * FROM history WHERE gameID = ".$_SESSION['gameID']." ORDER BY timeOfMove");

		$numMoves = -1;
		while ($thisMove = mysql_fetch_array($allMoves, MYSQL_ASSOC))
		{
			$numMoves++;
			$history[$numMoves] = $thisMove;
		}
	}

	function savePromotion()
	{
		global $history, $numMoves, $isInCheck;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		if ($isInCheck)
		{
			$tmpIsInCheck = 1;
			$history[$numMoves]['isInCheck'] = 1;
		}
		else
			$tmpIsInCheck = 0;

		$history[$numMoves]['promotedTo'] = getPieceName($_POST['promotion']);

		$tmpQuery = "UPDATE history SET promotedTo = '".getPieceName($_POST['promotion'])."', isInCheck = ".$tmpIsInCheck." WHERE gameID = ".$_SESSION['gameID']." AND timeOfMove = '".$history[$numMoves]['timeOfMove']."'";
		mysql_query($tmpQuery);

		updateTimestamp();
	
		/* if email notification is activated and move does not result in a pawn's promotion... */
		if ($CFG_USEEMAILNOTIFICATION)
		{
			if ($history[$numMoves]['replaced'] == null)
				$tmpReplaced = '';
			else
				$tmpReplaced = $history[$numMoves]['replaced'];

			/* get opponent's color */
			if (($numMoves == -1) || ($numMoves % 2 == 1))
				$oppColor = "black";
			else
				$oppColor = "white";
			
			/* get opponent's player ID */
			if ($oppColor == 'white')
				$tmpOpponentID = mysql_query("SELECT whitePlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
			else
				$tmpOpponentID = mysql_query("SELECT blackPlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
			
			$opponentID = mysql_result($tmpOpponentID, 0);
			
			/* if opponent is using email notification... */
			$tmpOpponentEmail = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'emailNotification'");
			if (mysql_num_rows($tmpOpponentEmail) > 0)
			{
				$opponentEmail = mysql_result($tmpOpponentEmail, 0);
				if ($opponentEmail != '')
				{
					/* get opponent's nick */
					$tmpOpponentNick = mysql_query("SELECT nick FROM players WHERE playerID = ".$_SESSION['playerID']);
					$opponentNick = mysql_result($tmpOpponentNick, 0);
					
					/* get opponent's prefered history type */
					$tmpOpponentHistory = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'history'");
					
					/* default to PGN */
					if (mysql_num_rows($tmpOpponentHistory) > 0)
						$opponentHistory = mysql_result($tmpOpponentHistory, 0);
					else
						$opponentHistory = 'pgn';
					
					/* notify opponent of move via email */
					if ($opponentHistory == 'pgn')
						webchessMail('move', $opponentEmail, moveToPGNString($history[$numMoves]['curColor'], $history[$numMoves]['curPiece'], $history[$numMoves]['fromRow'], $history[$numMoves]['fromCol'], $history[$numMoves]['toRow'], $history[$numMoves]['toCol'], $tmpReplaced, $history[$numMoves]['promotedTo'], $isInCheck), $opponentNick);
					else
						webchessMail('move', $opponentEmail, moveToVerbousString($history[$numMoves]['curColor'], $history[$numMoves]['curPiece'], $history[$numMoves]['fromRow'], $history[$numMoves]['fromCol'], $history[$numMoves]['toRow'], $history[$numMoves]['toCol'], $tmpReplaced, $history[$numMoves]['promotedTo'], $isInCheck), $opponentNick);
				}
			}
		}
	}
	
	function saveHistory()
	{
		global $board, $isPromoting, $history, $numMoves, $isInCheck, $CFG_USEEMAILNOTIFICATION;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* set destination row for pawn promotion */
		if ($board[$_POST['fromRow']][$_POST['fromCol']] & BLACK)
			$targetRow = 0;
		else
			$targetRow = 7;
		
		/* determine if move results in pawn promotion */
		if ((($board[$_POST['fromRow']][$_POST['fromCol']] & COLOR_MASK) == PAWN) && ($_POST['toRow'] == $targetRow))
			$isPromoting = true;
		else
			$isPromoting = false;

		/* determine who's playing based on number of moves so far */
		if (($numMoves == -1) || ($numMoves % 2 == 1))
		{
			$curColor = "white";
			$oppColor = "black";
			$targetRow = 7;
		}
		else
		{
			$curColor = "black";
			$oppColor = "white";
			$targetRow = 0;
		}

		/* add move to history */
		$numMoves++;
		$history[$numMoves]['gamedID'] = $_SESSION['gameID'];
		$history[$numMoves]['curPiece'] = getPieceName($board[$_POST['fromRow']][$_POST['fromCol']]);
		$history[$numMoves]['curColor'] = $curColor;
		$history[$numMoves]['fromRow'] = $_POST['fromRow'];
		$history[$numMoves]['fromCol'] = $_POST['fromCol'];
		$history[$numMoves]['toRow'] = $_POST['toRow'];
		$history[$numMoves]['toCol'] = $_POST['toCol'];
		$history[$numMoves]['promotedTo'] = null;

		if ($isInCheck)
			$history[$numMoves]['isInCheck'] = 1;
		else
			$history[$numMoves]['isInCheck'] = 0;

		if (DEBUG)
		{
			if ($history[$numMoves]['curPiece'] == '')
				echo ("WARNING!!!  missing piece at ".$_POST['fromRow'].", ".$_POST['fromCol'].": ".$board[$_POST['fromRow']][$_POST['fromCol']]."<p>\n");
		}

		if ($board[$_POST['toRow']][$_POST['toCol']] == 0)
		{
			$tmpQuery = "INSERT INTO history (timeOfMove, gameID, curPiece, curColor, fromRow, fromCol, toRow, toCol, replaced, promotedTo, isInCheck) VALUES (Now(), ".$_SESSION['gameID'].", '".getPieceName($board[$_POST['fromRow']][$_POST['fromCol']])."', '$curColor', ".$_POST['fromRow'].", ".$_POST['fromCol'].", ".$_POST['toRow'].", ".$_POST['toCol'].", null, null, ".$history[$numMoves]['isInCheck'].")"; 
			$history[$numMoves]['replaced'] = null;
			$tmpReplaced = "";
		}
		else
		{
			$tmpQuery = "INSERT INTO history (timeOfMove, gameID, curPiece, curColor, fromRow, fromCol, toRow, toCol, replaced, promotedTo, isInCheck) VALUES (Now(), ".$_SESSION['gameID'].", '".getPieceName($board[$_POST['fromRow']][$_POST['fromCol']])."', '$curColor', ".$_POST['fromRow'].", ".$_POST['fromCol'].", ".$_POST['toRow'].", ".$_POST['toCol'].", '".getPieceName($board[$_POST['toRow']][$_POST['toCol']])."', null, ".$history[$numMoves]['isInCheck'].")"; 

			$history[$numMoves]['replaced'] = getPieceName($board[$_POST['toRow']][$_POST['toCol']]);
			$tmpReplaced = $history[$numMoves]['replaced'];
		}

		mysql_query($tmpQuery);

		/* if email notification is activated and move does not result in a pawn's promotion... */
		/* NOTE: moves resulting in pawn promotion are handled by savePromotion() above */
		if ($CFG_USEEMAILNOTIFICATION && !$isPromoting)
		{
			/* get opponent's player ID */
			if ($oppColor == 'white')
				$tmpOpponentID = mysql_query("SELECT whitePlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
			else
				$tmpOpponentID = mysql_query("SELECT blackPlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
			
			$opponentID = mysql_result($tmpOpponentID, 0);
			
			/* if opponent is using email notification... */
			$tmpOpponentEmail = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'emailNotification'");
			if (mysql_num_rows($tmpOpponentEmail) > 0)
			{
				$opponentEmail = mysql_result($tmpOpponentEmail, 0);
				if ($opponentEmail != '')
				{
					/* get opponent's nick */
					$tmpOpponentNick = mysql_query("SELECT nick FROM players WHERE playerID = ".$_SESSION['playerID']);
					$opponentNick = mysql_result($tmpOpponentNick, 0);
					
					/* get opponent's prefered history type */
					$tmpOpponentHistory = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'history'");
					
					/* default to PGN */
					if (mysql_num_rows($tmpOpponentHistory) > 0)
						$opponentHistory = mysql_result($tmpOpponentHistory, 0);
					else
						$opponentHistory = 'pgn';
					
					/* notify opponent of move via email */
					if ($opponentHistory == 'pgn')
						webchessMail('move', $opponentEmail, moveToPGNString($history[$numMoves]['curColor'], $history[$numMoves]['curPiece'], $history[$numMoves]['fromRow'], $history[$numMoves]['fromCol'], $history[$numMoves]['toRow'], $history[$numMoves]['toCol'], $tmpReplaced, '', $isInCheck), $opponentNick);
					else
						webchessMail('move', $opponentEmail, moveToVerbousString($history[$numMoves]['curColor'], $history[$numMoves]['curPiece'], $history[$numMoves]['fromRow'], $history[$numMoves]['fromCol'], $history[$numMoves]['toRow'], $history[$numMoves]['toCol'], $tmpReplaced, '', $isInCheck), $opponentNick);
				}
			}
		}
	}

	function loadGame()
	{
		global $board, $playersColor;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* clear board data */
		for ($i = 0; $i < 8; $i++)
			for ($j = 0; $j < 8; $j++)
				$board[$i][$j] = 0;

		/* get data from database */
		$pieces = mysql_query("SELECT * FROM pieces WHERE gameID = ".$_SESSION['gameID']);

		/* setup board */
		while ($thisPiece = mysql_fetch_array($pieces, MYSQL_ASSOC))
		{
			$board[$thisPiece["row"]][$thisPiece["col"]] = getPieceCode($thisPiece["color"], $thisPiece["piece"]);
		}
		
		/* get current player's color */
		$tmpQuery = "SELECT whitePlayer, blackPlayer FROM games WHERE gameID = ".$_SESSION['gameID'];
		$tmpTurns = mysql_query($tmpQuery);
		$tmpTurn = mysql_fetch_array($tmpTurns, MYSQL_ASSOC);

		if ($tmpTurn['whitePlayer'] == $_SESSION['playerID'])
			$playersColor = "white";
		else
			$playersColor = "black";
	}

	function saveGame()
	{
		global $board, $playersColor;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* clear old data */
		mysql_query("DELETE FROM pieces WHERE gameID = ".$_SESSION['gameID']);

		/* save new game data */
		/* for each row... */
		for ($i = 0; $i < 8; $i++)
		{
			/* for each col... */
			for ($j = 0; $j < 8; $j++)
			{
				/* if there's a piece at that pos on the board */
				if ($board[$i][$j] != 0)
				{
					/* updated the database */
					if ($board[$i][$j] & BLACK)
						$tmpColor = "black";
					else
						$tmpColor = "white";

					$tmpPiece = getPieceName($board[$i][$j]);
					mysql_query("INSERT INTO pieces (gameID, color, piece, row, col) VALUES (".$_SESSION['gameID'].", '$tmpColor', '$tmpPiece', $i, $j)");
				}
			}
		}

		/* update lastMove timestamp */
		updateTimestamp();
	}

	function processMessages()
	{
		global $isUndoRequested, $isDrawRequested, $isUndoing, $isGameOver, $isCheckMate, $playersColor, $statusMessage, $CFG_USEEMAILNOTIFICATION;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		if (DEBUG)
			echo("Entering processMessages()<br>\n");

		$isUndoRequested = false;
		$isGameOver = false;
		
		if ($playersColor == "white")
			$opponentColor = "black";
		else
			$opponentColor = "white";

		/* *********************************************** */
		/* queue user generated (ie: using forms) messages */
		/* *********************************************** */
		if (DEBUG)
			echo("Processing user generated (ie: form) messages...<br>\n");

		/* queue a request for an undo */
		if ($_POST['requestUndo'] == "yes")
		{
			/* if the two players are on the same system, execute undo immediately */
			/* NOTE: assumes the two players discussed it live before undoing */
			if ($_SESSION['isSharedPC'])
				$isUndoing = true;
			else
			{
				$tmpQuery = "INSERT INTO messages (gameID, msgType, msgStatus, destination) VALUES (".$_SESSION['gameID'].", 'undo', 'request', '".$opponentColor."')";
				mysql_query($tmpQuery);
			}
			
			updateTimestamp();
		}
		
		/* queue a request for a draw */
		if ($_POST['requestDraw'] == "yes")
		{
			/* if the two players are on the same system, execute Draw immediately */
			/* NOTE: assumes the two players discussed it live before declaring the game a draw */
			if ($_SESSION['isSharedPC'])
			{
				$tmpQuery = "UPDATE games SET gameMessage = 'draw', messageFrom = '".$playersColor."' WHERE gameID = ".$_SESSION['gameID'];
				mysql_query($tmpQuery);
			}
			else
			{
				$tmpQuery = "INSERT INTO messages (gameID, msgType, msgStatus, destination) VALUES (".$_SESSION['gameID'].", 'draw', 'request', '".$opponentColor."')";
				mysql_query($tmpQuery);
			}

			updateTimestamp();
		}

		/* response to a request for an undo */
		if (isset($_POST['undoResponse']))
		{
			if ($_POST['isUndoResponseDone'] == 'yes')
			{
				if ($_POST['undoResponse'] == "yes")
				{
					$tmpStatus = "approved";
					$isUndoing = true;
				}
				else
					$tmpStatus = "denied";
			
				$tmpQuery = "UPDATE messages SET msgStatus = '".$tmpStatus."', destination = '".$opponentColor."' WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'undo' AND msgStatus = 'request' AND destination = '".$playersColor."'";
				mysql_query($tmpQuery);
			
				updateTimestamp();
			}
		}
		
		/* response to a request for a draw */
		if (isset($_POST['drawResponse']))
		{
			if ($_POST['isDrawResponseDone'] == 'yes')
			{
				if ($_POST['drawResponse'] == "yes")
				{
					$tmpStatus = "approved";
					$tmpQuery = "UPDATE games SET gameMessage = 'draw', messageFrom = '".$playersColor."' WHERE gameID = ".$_SESSION['gameID'];
					mysql_query($tmpQuery);
				}
				else
					$tmpStatus = "denied";
			
				$tmpQuery = "UPDATE messages SET msgStatus = '".$tmpStatus."', destination = '".$opponentColor."' WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'draw' AND msgStatus = 'request' AND destination = '".$playersColor."'";
				mysql_query($tmpQuery);

				updateTimestamp();
			}
		}
		
		/* resign the game */
		if ($_POST['resign'] == "yes")
		{
			$tmpQuery = "UPDATE games SET gameMessage = 'playerResigned', messageFrom = '".$playersColor."' WHERE gameID = ".$_SESSION['gameID'];
			mysql_query($tmpQuery);

			updateTimestamp();

			/* if email notification is activated... */
			if ($CFG_USEEMAILNOTIFICATION)
			{
				/* get opponent's player ID */
				if ($playersColor == 'white')
					$tmpOpponentID = mysql_query("SELECT blackPlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
				else
					$tmpOpponentID = mysql_query("SELECT whitePlayer FROM games WHERE gameID = ".$_SESSION['gameID']);
				
				$opponentID = mysql_result($tmpOpponentID, 0);
			
				$tmpOpponentEmail = mysql_query("SELECT value FROM preferences WHERE playerID = ".$opponentID." AND preference = 'emailNotification'");
				
				/* if opponent is using email notification... */
				if (mysql_num_rows($tmpOpponentEmail) > 0)
				{
					$opponentEmail = mysql_result($tmpOpponentEmail, 0);
					if ($opponentEmail != '')
					{
						/* notify opponent of resignation via email */
						webchessMail('resignation', $opponentEmail, '', $_SESSION['nick']);
					}
				}
			}
		}
		
		
		/* ******************************************* */
		/* process queued messages (ie: from database) */
		/* ******************************************* */
		$tmpQuery = "SELECT * FROM messages WHERE gameID = ".$_SESSION['gameID']." AND destination = '".$playersColor."'";
		$tmpMessages = mysql_query($tmpQuery);

		while($tmpMessage = mysql_fetch_array($tmpMessages, MYSQL_ASSOC))
		{
			switch($tmpMessage['msgType'])
			{
				case 'undo':
					switch($tmpMessage['msgStatus'])
					{
						case 'request':
							$isUndoRequested = true;
							break;
						case 'approved':
							$tmpQuery = "DELETE FROM messages WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'undo' AND msgStatus = 'approved' AND destination = '".$playersColor."'";
							mysql_query($tmpQuery);
							$statusMessage .= "Undo approved.<br>\n";
							break;
						case 'denied':
							$isUndoing = false;
							$tmpQuery = "DELETE FROM messages WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'undo' AND msgStatus = 'denied' AND destination = '".$playersColor."'";
							mysql_query($tmpQuery);
							$statusMessage .= "Undo denied.<br>\n";
							break;
					}
					break;
				
				case 'draw':
					switch($tmpMessage['msgStatus'])
					{
						case 'request':
							$isDrawRequested = true;
							break;
						case 'approved':
							$tmpQuery = "DELETE FROM messages WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'draw' AND msgStatus = 'approved' AND destination = '".$playersColor."'";
							mysql_query($tmpQuery);
							$statusMessage .= "Draw approved.<br>\n";
							break;
						case 'denied':
							$tmpQuery = "DELETE FROM messages WHERE gameID = ".$_SESSION['gameID']." AND msgType = 'draw' AND msgStatus = 'denied' AND destination = '".$playersColor."'";
							mysql_query($tmpQuery);
							$statusMessage .= "Draw denied.<br>\n";
							break;
					}
					break;
			}
		}

		/* requests pending */
		$tmpQuery = "SELECT * FROM messages WHERE gameID = ".$_SESSION['gameID']." AND msgStatus = 'request' AND destination = '".$opponentColor."'";
		$tmpMessages = mysql_query($tmpQuery);

		while($tmpMessage = mysql_fetch_array($tmpMessages, MYSQL_ASSOC))
		{
			switch($tmpMessage['msgType'])
			{
				case 'undo':
					$statusMessage .= "Your undo request is pending.<br>\n";
					break;
				case 'draw':
					$statusMessage .= "Your request for a draw is pending.<br>\n";
					break;
			}
		}	
		
		/* game level status: draws, resignations and checkmate */
		/* if checkmate, update games table */
		if ($_POST['isCheckMate'] == 'true')
			mysql_query("UPDATE games SET gameMessage = 'checkMate', messageFrom = '".$playersColor."' WHERE gameID = ".$_SESSION['gameID']);

		$tmpQuery = "SELECT gameMessage, messageFrom FROM games WHERE gameID = ".$_SESSION['gameID'];
		$tmpMessages = mysql_query($tmpQuery);
		$tmpMessage = mysql_fetch_array($tmpMessages, MYSQL_ASSOC);
		
		if ($tmpMessage['gameMessage'] == "draw")
		{
			$statusMessage .= "Game ended in a draw.<br>\n";
			$isGameOver = true;
		}

		if ($tmpMessage['gameMessage'] == "playerResigned")
		{
			$statusMessage .= $tmpMessage['messageFrom']." has resigned the game.<br>\n";
			$isGameOver = true;
		}

		if ($tmpMessage['gameMessage'] == "checkMate")
		{
			$statusMessage .= "Checkmate! ".$tmpMessage['messageFrom']." has won the game.<br>\n";
			$isGameOver = true;
			$isCheckMate = true;
		}
	}
?>
