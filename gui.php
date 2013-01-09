<? /* functions for outputting to html and javascript */

	function drawboard()
	{
		global $board, $playersColor, $numMoves;
		
		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;

		/* find out if it's the current player's turn */
		if (( (($numMoves == -1) || (($numMoves % 2) == 1)) && ($playersColor == "white"))
				|| ((($numMoves % 2) == 0) && ($playersColor == "black")) )
			$isPlayersTurn = true;
		else
			$isPlayersTurn = false;

		/* determine who's perspective of the board to show */
		if ($_SESSION['isSharedPC'] && !$isPlayersTurn)
		{
			if ($playersColor == "white")
				$perspective = "black";
			else
				$perspective = "white";
		}
		else
		{
			$perspective = $playersColor;
		}
		
		/* NOTE: if both players are using the same PC, in a sense it's always the players turn */
		if ($_SESSION['isSharedPC'])
			$isPlayersTurn = true;
		
		/* determine if board is disabled */
		$isDisabled = isBoardDisabled();

		echo ("<table border='1'>\n");
		if ($isDisabled)
			echo ("<tr bgcolor='#DDDDDD'>");
		else
			echo ("<tr bgcolor='beige'>");

		/* setup vars to show player's perspective of the board */
		if ($perspective == "white")
		{
			$topRow = 7;
			$bottomRow = 0;
			$rowStep = -1;
			
			$leftCol = 0;
			$rightCol = 7;
			$colStep = 1;
		}
		else
		{
			$topRow = 0;
			$bottomRow = 7;
			$rowStep = 1;
			
			$leftCol = 7;
			$rightCol = 0;
			$colStep = -1;
		}
		
		/* column headers */
		echo ("<th>&nbsp;</th>");
		
		/* NOTE: end condition is ($rightCol + $colStep) since we want to output $rightCol */
		for ($i = $leftCol; $i != ($rightCol + $colStep); $i += $colStep)
			echo ("<th>".chr($i + 97)."</th>");
		
		echo ("</tr>\n");

		/* for each row... */
		/* NOTE: end condition is ($bottomRow + $rowStep) since we want to output $bottomRow */
		for ($i = $topRow; $i != ($bottomRow + $rowStep); $i += $rowStep)
		{
			echo ("<tr>\n");
			if ($isDisabled)
				echo ("<th width='20' bgcolor='#DDDDDD'>".($i+1)."</th>\n");
			else
				echo ("<th width='20' bgcolor='beige'>".($i+1)."</th>\n");

			/* for each col... */
			/* NOTE: end condition is ($rightCol + $colStep) since we want to output $rightCol */
			for ($j = $leftCol; $j != ($rightCol + $colStep); $j += $colStep)
			{
				echo ("   <td bgcolor='");

				/* if board is disabled, show board in grayscale */
				if ($isDisabled)
				{
					if (($j + ($i % 2)) % 2 == 0)
						echo ("#444444'>");
					else
						echo ("#BBBBBB'>");
				}
				else
				{
					if (($j + ($i % 2)) % 2 == 0)
						echo ("#772222'>");
					else
						echo ("#CCBBBB'>");
				}

				/* if disabled or not player's turn, can't click pieces */
				if (!$isDisabled && $isPlayersTurn)
				{
					echo ("<a href='JavaScript:squareClicked($i, $j, ");
					if ($board[$i][$j] == 0)
						echo ("true)'>");
					else
						echo ("false)'>");
				}

				echo ("<img name='pos$i-$j' src='images/".$_SESSION['pref_theme']."/");

				/* if position is empty... */
				if ($board[$i][$j] == 0)
				{
					/* draw empty square */
					$tmpALT="blank";
				}
				else
				{
					/* draw correct piece */
					if ($board[$i][$j] & BLACK)
						$tmpALT = "black_";
					else
						$tmpALT = "white_";

					$tmpALT .= getPieceName($board[$i][$j]);
				}

				echo($tmpALT.".gif' height='50' width='50' border='0' alt='".$tmpALT."'>");
				
				if (!$isDisabled && $isPlayersTurn)
					echo ("</a>");
				
				echo ("</td>\n");
			}

			echo ("</tr>\n");
		}

		echo ("</table>\n\n");
	}

	function writeJSboard()
	{
		global $board, $numMoves;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* write out constants */
		echo ("var DEBUG = ".DEBUG.";\n");

		echo ("var CURRENTTHEME = '".$_SESSION['pref_theme']."';\n");
		echo ("var PAWN = ".PAWN.";\n");
		echo ("var KNIGHT = ".KNIGHT.";\n");
		echo ("var BISHOP = ".BISHOP.";\n");
		echo ("var ROOK = ".ROOK.";\n");
		echo ("var QUEEN = ".QUEEN.";\n");
		echo ("var KING = ".KING.";\n");
		echo ("var BLACK = ".BLACK.";\n");
		echo ("var WHITE = ".WHITE.";\n");
		echo ("var COLOR_MASK = ".COLOR_MASK.";\n");
		
		/* write code for array */
		echo ("var board = new Array();\n");
		for ($i = 0; $i < 8; $i++)
		{
			echo ("board[$i] = new Array();\n");

			for ($j = 0; $j < 8; $j++)
			{
				echo ("board[$i][$j] = ".$board[$i][$j].";\n");
			}
		}

		echo("var numMoves = $numMoves;\n");
		echo("var errMsg = '';\n");	/* global var used for error messages */
	}

	/* provide history data to javascript function */
	/* NOTE: currently, only pawn validation script uses history */
	function writeJSHistory()
	{
		global $history, $numMoves;

		/* write out constants */
		echo ("var CURPIECE = 0;\n");
		echo ("var CURCOLOR = 1;\n");
		echo ("var FROMROW = 2;\n");
		echo ("var FROMCOL = 3;\n");
		echo ("var TOROW = 4;\n");
		echo ("var TOCOL = 5;\n");
		
		/* write code for array */
		echo ("var chessHistory = new Array();\n");
		for ($i = 0; $i <= $numMoves; $i++)
		{
			echo ("chessHistory[$i] = new Array();\n");
			echo ("chessHistory[$i][CURPIECE] = '".$history[$i]['curPiece']."';\n");
			echo ("chessHistory[$i][CURCOLOR] = '".$history[$i]['curColor']."';\n");
			echo ("chessHistory[$i][FROMROW] = ".$history[$i]['fromRow'].";\n");
			echo ("chessHistory[$i][FROMCOL] = ".$history[$i]['fromCol'].";\n");
			echo ("chessHistory[$i][TOROW] = ".$history[$i]['toRow'].";\n");
			echo ("chessHistory[$i][TOCOL] = ".$history[$i]['toCol'].";\n");
		}
}
	
	function writeVerbousHistory()
	{
		global $history, $numMoves;

		echo ("<table width='300' border='1'>\n");
		echo ("<tr><th bgcolor='beige' colspan='2'>HISTORY</th></tr>\n");

		for ($i = $numMoves; $i >= 0; $i--)
		{
			if ($i % 2 == 1)
			{
				echo ("<tr bgcolor='black'>");
				echo ("<td width='20'><font color='white'>".($i + 1)."</font></td><td><font color='white'>");
			}
			else
			{
				echo ("<tr bgcolor='white'>");
				echo ("<td width='20'>".($i + 1)."</td><td><font color='black'>");
			}

			$tmpReplaced = "";
			if (!is_null($history[$i]['replaced']))
				$tmpReplaced = $history[$i]['replaced'];
			
			$tmpPromotedTo = "";
			if (!is_null($history[$i]['promotedTo']))
				$tmpPromotedTo = $history[$i]['promotedTo'];

			$tmpCheck = ($history[$i]['isInCheck'] == 1);
			
			echo(moveToVerbousString($history[$i]['curColor'], $history[$i]['curPiece'], $history[$i]['fromRow'], $history[$i]['fromCol'], $history[$i]['toRow'], $history[$i]['toCol'], $tmpReplaced, $tmpPromotedTo, $tmpCheck));
			
			echo ("</font></td></tr>\n");
		}
		
		echo ("<tr bgcolor='#BBBBBB'><td>0</td><td>New Game</td></tr>\n");
		echo ("</table>\n");
	}

	function writeHistoryPGN()
	{
		global $history, $numMoves;

		echo ("<table border='1'>\n");
		echo ("<tr><th bgcolor='beige' colspan='3'>HISTORY</th></tr>\n");
		echo ("<tr><th bgcolor='#BBBBBB' width='50'>Move</th>");
		echo ("<th bgcolor='white' width='80'><font color='black'>White</font></th>");
		echo ("<th bgcolor='black' width='80'><font color='white'>Black</font></th></tr>\n");

		for ($i = 0; $i <= $numMoves; $i+=2)
		{
			echo ("<tr><td align='center' bgcolor='#BBBBBB'>".(($i/2) + 1)."</td><td bgcolor='white' align='center'><font color='black'>");

			$tmpReplaced = "";
			if (!is_null($history[$i]['replaced']))
				$tmpReplaced = $history[$i]['replaced'];
			
			$tmpPromotedTo = "";
			if (!is_null($history[$i]['promotedTo']))
				$tmpPromotedTo = $history[$i]['promotedTo'];

			$tmpCheck = ($history[$i]['isInCheck'] == 1);
			
			echo(moveToPGNString($history[$i]['curColor'], $history[$i]['curPiece'], $history[$i]['fromRow'], $history[$i]['fromCol'], $history[$i]['toRow'], $history[$i]['toCol'], $tmpReplaced, $tmpPromotedTo, $tmpCheck));

			echo ("</font></td><td bgcolor='black' align='center'><font color='white'>");

			if ($i == $numMoves)
				echo ("&nbsp;");
			else
			{
				$tmpReplaced = "";
				if (!is_null($history[$i+1]['replaced']))
					$tmpReplaced = $history[$i+1]['replaced'];
			
				$tmpPromotedTo = "";
				if (!is_null($history[$i+1]['promotedTo']))
					$tmpPromotedTo = $history[$i+1]['promotedTo'];

				$tmpCheck = ($history[$i+1]['isInCheck'] == 1);
			
				echo(moveToPGNString($history[$i+1]['curColor'], $history[$i+1]['curPiece'], $history[$i+1]['fromRow'], $history[$i+1]['fromCol'], $history[$i+1]['toRow'], $history[$i+1]['toCol'], $tmpReplaced, $tmpPromotedTo, $tmpCheck));
			}
			
			echo ("</font></td></tr>\n");
		}
		
		echo ("</table>\n");
		
	}

	function writeHistory()
	{
		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* based on player's preferences, display the history */
		switch($_SESSION['pref_history'])
		{
			case 'verbous':
				writeVerbousHistory();
				break;
			
			case 'pgn':
				writeHistoryPGN();
				break;
		}
	}

	function writeStatus()
	{
		global $numMoves, $history, $isCheckMate, $statusMessage, $isPlayersTurn;

		?>
		<table border="1" width="300" align="center">
		<tr bgcolor="beige">
			<th>
			STATUS - 
			<? if ($isPlayersTurn) echo ("Your Move"); else echo("Opponent's Move"); ?>
			</th>
		</tr>

		<tr>
		<?
		if (($numMoves == -1) || ($numMoves % 2 == 1))
			$curColor = "White";
		else
			$curColor = "Black";

		if (!$isCheckMate && ($history[$numMoves]['isInCheck'] == 1))
			echo("<td align='center' bgcolor='red'>\n<b>".$curColor." is currently in check!</b><br>\n".$statusMessage."</td>\n");
		else
			echo("<td>".$statusMessage."&nbsp;</td>\n");
		?>
		</tr>
		</table>
		<?
	}

	function writePromotion()
	{
	?>
		<p>
		<table width="435" border="1">
		<tr><td>
			Promote pawn to:
			<br>
			<input type="radio" name="promotion" value="<? echo (QUEEN); ?>" checked="checked"> Queen
			<input type="radio" name="promotion" value="<? echo (ROOK); ?>"> Rook
			<input type="radio" name="promotion" value="<? echo (KNIGHT); ?>"> Knight
			<input type="radio" name="promotion" value="<? echo (BISHOP); ?>"> Bishop
			<input type="button" name="btnPromote" value="Promote" onClick="promotepawn()" />
		</td></tr>
		</table>
		</p>
	<?
	}

	function writeUndoRequest()
	{
	?>
		<p>
		<table width="435" border="1">
		<tr><td>
			Your opponent would like to undo their latest move.  Will you allow it?
			<br>
			<input type="radio" name="undoResponse" value="yes"> Yes
			<input type="radio" name="undoResponse" value="no" checked="checked"> No
			<input type="hidden" name="isUndoResponseDone" value="no">
			<input type="button" value="Reply" onClick="this.form.isUndoResponseDone.value = 'yes'; this.form.submit()">
		</td></tr>
		</table>
		</p>
	<?
	}

	function writeDrawRequest()
	{
	?>
		<p>
		<table width="435" border="1">
		<tr><td>
			Your opponent is proposing a draw.  Do you agree?
			<br>
			<input type="radio" name="drawResponse" value="yes"> Yes
			<input type="radio" name="drawResponse" value="no" checked="checked"> No
			<input type="hidden" name="isDrawResponseDone" value="no">
			<input type="button" value="Reply" onClick="this.form.isDrawResponseDone.value = 'yes'; this.form.submit()">
		</td></tr>
		</table>
		</p>
	<?
	}
?>
