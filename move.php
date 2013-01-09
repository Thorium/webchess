<? /* these functions deal specifically with moving a piece */
	function doMove()
	{
		global $board, $isPromoting, $doUndo, $history, $numMoves;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		/* if moving en-passant */
		/* (ie: if pawn moves diagonally without replacing anything) */
		if ((($board[$_POST['fromRow']][$_POST['fromCol']] & COLOR_MASK) == PAWN) && ($_POST['toCol'] != $_POST['fromCol']) && ($board[$_POST['toRow']][$_POST['toCol']] == 0))
			/* delete eaten pawn */
			$board[$_POST['fromRow']][$_POST['toCol']] = 0;
		
		/* move piece to destination, replacing whatever's there */
		$board[$_POST['toRow']][$_POST['toCol']] = $board[$_POST['fromRow']][$_POST['fromCol']];

		/* delete piece from old position */
		$board[$_POST['fromRow']][$_POST['fromCol']] = 0;

		/* if not Undoing, but castling */
		if (($doUndo != "yes") && (($board[$_POST['toRow']][$_POST['toCol']] & COLOR_MASK) == KING) && (($_POST['toCol'] - $_POST['fromCol']) == 2))
		{
			/* castling to the right, move the right rook to the left side of the king */
			$board[$_POST['toRow']][5] = $board[$_POST['toRow']][7];

			/* delete rook from original position */
			$board[$_POST['toRow']][7] = 0;
		}
		elseif (($doUndo != "yes") && (($board[$_POST['toRow']][$_POST['toCol']] & COLOR_MASK) == KING) && (($_POST['fromCol'] - $_POST['toCol']) == 2))
		{
			/* castling to the left, move the left rook to the right side of the king */
			$board[$_POST['toRow']][3] = $board[$_POST['toRow']][0];

			/* delete rook from original position */
			$board[$_POST['toRow']][0] = 0;
		}

		return true;
	}
?>

