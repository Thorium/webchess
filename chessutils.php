<?	$_CHESSUTILS = true;

	/* these are utility functions used by other functions */
	function getPieceName($piece)
	{
		switch($piece & COLOR_MASK)
		{
			case PAWN:
				$name = "pawn";
				break;
			case KNIGHT:
				$name = "knight";
				break;
			case BISHOP:
				$name = "bishop";
				break;
			case ROOK:
				$name = "rook";
				break;
			case QUEEN:
				$name = "queen";
				break;
			case KING:
				$name = "king";
				break;
		}

		return $name;
	}

	function getPieceCode($color, $piece)
	{
		switch($piece)
		{
			case "pawn":
				$code = PAWN;
				break;
			case "knight":
				$code = KNIGHT;
				break;
			case "bishop":
				$code = BISHOP;
				break;
			case "rook":
				$code = ROOK;
				break;
			case "queen":
				$code = QUEEN;
				break;
			case "king":
				$code = KING;
				break;
		}

		if ($color == "black")
			$code = BLACK | $code;

		return $code;
	}

	function getPGNCode($piecename)
	{
		switch($piecename)
		{
			case 'pawn':
				$pgnCode = "";
				break;
			case 'knight':
				$pgnCode = "N";
				break;
			case 'bishop':
				$pgnCode = "B";
				break;
			case 'rook':
				$pgnCode = "R";
				break;
			case 'queen':
				$pgnCode = "Q";
				break;
			case 'king':
				$pgnCode = "K";
				break;
		}

		return $pgnCode;
	}

	function isBoardDisabled()
	{
		global $board, $isPromoting, $isUndoRequested, $isDrawRequested, $isGameOver, $playersColor;

		/* if current player is promoting, a message needs to be replied to (Undo or Draw) or the game is over, then board is Disabled */
		$tmpIsBoardDisabled = (($isPromoting || $isUndoRequested || $isDrawRequested || $isGameOver) == true);
		
		/* if opponent is in the process of promoting, then board is diabled */
		if (!$tmpIsBoardDisabled)
		{
			if ($playersColor == "white")
				$promotionRow = 7;
			else
				$promotionRow = 0;

			for ($i = 0; $i < 8; $i++)
				if (($board[$promotionRow][$i] & COLOR_MASK) == PAWN)
					$tmpIsBoardDisabled = true;
		}

		return $tmpIsBoardDisabled;
	}

	function moveToPGNString($curColor, $piece, $fromRow, $fromCol, $toRow, $toCol, $pieceCaptured, $promotedTo, $isChecking)
	{
		$pgnString = "";
		
		/* check for castling */
		if (($piece == "king") && (abs($toCol - $fromCol) == 2))
		{
			/* if king-side castling */
			if (($toCol - $fromCol) == 2)
				$pgnString .= ("O-O");
			else
				$pgnString .= ("O-O-O");
		}
		else
		{
			/* PNG code for moving piece */
			$pgnString .= getPGNCode($piece);

			/* source square */
			$pgnString .= chr($fromCol + 97).($fromRow + 1);

			/* check for captured pieces */
			if ($pieceCaptured != "")
				$pgnString .= "x";
			else
				$pgnString .= "-";

			/* destination square */
			$pgnString .= chr($toCol + 97).($toRow + 1);

			/* check for pawn promotion */
			if ($promotedTo != "")
				$pgnString .= "=".getPGNCode($promotedTo);
		}
		
		/* check for CHECK */
		if ($isChecking)
			$pgnString .= "+";

		/* if checkmate, $pgnString .= "#"; */

		return $pgnString;
	}

	function moveToVerbousString($curColor, $piece, $fromRow, $fromCol, $toRow, $toCol, $pieceCaptured, $promotedTo, $isChecked)
	{
		$verbousString = "";
		
		/* ex: white queen from a4 to c6 */
		$verbousString .= $curColor." ".$piece." from ".chr($fromCol + 97).($fromRow + 1)." to ".chr($toCol + 97).($toRow + 1);

		/* check for castling */
		if (($piece == "king") && (abs($toCol - $fromCol) == 2))
			$verbousString .= " (castled)";

		/* check for en passant */
		if (($piece == "pawn") && ($toCol != $fromCol) && ($pieceCaptured == ""))
			$verbousString .= " eating pawn en-passant";
			
		if ($pieceCaptured != "")
			$verbousString .= " eating ".$pieceCaptured;

		if ($promotedTo != "")
			$verbousString .= "<br>Pawn promoted to ".$promotedTo;
		
		return $verbousString;
	}

	function webchessMail($msgType, $msgTo, $move, $opponent)
	{
		global $CFG_MAILADDRESS;

		/* default message and subject */
		$mailmsg = "";
		$mailsubject = "WebChess";
		
		/* load specific message and subject */
		switch($msgType)
		{
			case 'test':
				require 'mailmsgtest.php';
				break;
			case 'invitation':
				require 'mailmsginvite.php';
				break;
			case 'withdrawal':
				require 'mailmsgwithdraw.php';
				break;
			case 'resignation':
				require 'mailmsgresign.php';
				break;
			case 'move':
				require 'mailmsgmove.php';
				break;
		}

		$headers .= "From: WebChess <".$CFG_MAILADDRESS.">\r\n";
		$headers .= "To: ".$msgTo."\r\n";
		$headers .= "Reply-To: WebChess <".$CFG_MAILADDRESS.">\r\n";

		mail($msgTo, $mailsubject, $mailmsg, $headers);
	}

	/* returns true if current version of PHP is greater than vercheck */
	/* donated to PHP page (http://www.php.net/manual/en/function.version-compare.php) */
	/* by savetz@northcoast.com and is PHP < 4.1.0 safe */
	function minimum_version( $vercheck ) {
		$minver = explode(".", $vercheck);
		$curver = explode(".", phpversion());
		
		if (($curver[0] < $minver[0])
			|| (($curver[0] == $minver[0])
				&& ($curver[1] < $minver[1]))
			|| (($curver[0] == $minver[0])
				&& ($curver[1] == $minver[1])
				&& ($curver[2][0] < $minver[2][0])))
			return false;
		else
			return true;
	}

	/* allow WebChess to be run on PHP systems < 4.1.0, using old http vars */
	/* heavily based on php4-1-0_varfix.php by Tom Harrison (thetomharrison@hotmail.com) */
	/* only doing the opposite: creating _SESSION, _GET and _POST based on */
	/* their HTTP_*_VARS equivalent */
	function createNewHttpVars($type)
	{
		global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

		$temp = array();
		switch(strtoupper($type))
		{
			case 'POST':   $temp2 = &$HTTP_POST_VARS;   break;
			case 'GET':    $temp2 = &$HTTP_GET_VARS;    break;
			case 'SESSION':    $temp2 = &$HTTP_SESSION_VARS;    break;
			default: return 0;
		}

		while (list($varname, $varvalue) = each($temp2)) {
			$temp[$varname] = $varvalue;
		}
		
		return ($temp);
	}
	
	function fixOldPHPVersions()
	{
		global $_fixOldPHPVersions;

		if (isset($_fixOldPHPVersions))
			return;
		
		if (!minimum_version("4.1.0"))
		{
			global $_POST, $_GET, $_SESSION;

			$_POST = createNewHttpVars("POST");
			$_GET = createNewHttpVars("GET");
			//$_SESSION = createNewHttpVars("SESSION");
			
			if (!isset($HTTP_SESSION_VARS["_SESSION"]))
				session_register("_SESSION");
		}

		$_fixOldPHPVersions = true;
	}

	// this function was taken from the PHP documentation
	// http://www.php.net/manual/en/function.mt-srand.php
	// seed with microseconds
	function make_seed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
	
	
	// this function was provided to the PHP documentation
	// by houtex_boy@yahoo.com and slightly modified to use
	// the above make_seed()
	// http://www.php.net/manual/en/function.srand.php
	// ensures srand() is only called once
	function init_srand($seed = '')
	{
		static $wascalled = FALSE;
		if (!$wascalled){
			$seed = $seed === '' ? make_seed() : $seed;
			srand($seed);
			$wascalled = TRUE;
		}
	}
?>

