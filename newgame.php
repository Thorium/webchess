<? /* these functions are used to start a new game */
	function initBoard()
	{
		global $board;

		/* clear board */
		for ($i = 0; $i < 8; $i++)
		{
			for ($j = 0; $j < 8; $j++)
			{
				$board[$i][$j] = 0;
			}
		}

		/* setup white pieces */
		$board[0][0] = WHITE | ROOK;
		$board[0][7] = WHITE | ROOK;
		$board[0][1] = WHITE | KNIGHT;
		$board[0][6] = WHITE | KNIGHT;
		$board[0][2] = WHITE | BISHOP;
		$board[0][5] = WHITE | BISHOP;
		$board[0][3] = WHITE | QUEEN;
		$board[0][4] = WHITE | KING;

		/* setup black pieces */
		$board[7][0] = BLACK | ROOK;
		$board[7][7] = BLACK | ROOK;
		$board[7][1] = BLACK | KNIGHT;
		$board[7][6] = BLACK | KNIGHT;
		$board[7][2] = BLACK | BISHOP;
		$board[7][5] = BLACK | BISHOP;
		$board[7][3] = BLACK | QUEEN;
		$board[7][4] = BLACK | KING;

		/* setup pawns */
		for ($i = 0; $i < 8; $i++)
		{
			$board[1][$i] = WHITE | PAWN;
			$board[6][$i] = BLACK | PAWN;
		}
	}

	function createNewGame($gameID)
	{
		/* clear history */
		global $numMoves;

		/* old PHP versions don't have _POST, _GET and _SESSION as auto_globals */
		if (!minimum_version("4.1.0"))
			global $_POST, $_GET, $_SESSION;
		
		$numMoves = -1;
		mysql_query("DELETE FROM history WHERE gameID = ".$_SESSION['gameID']);

		initBoard();
	}
?>
