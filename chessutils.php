<?php	
// $Id: chessutils.php,v 1.12 2010/08/14 16:57:54 sandking Exp $

/*
    This file is part of WebChess. https://github.com/thorium/webchess
	Copyright 2010 Jonathan Evraire, Rodrigo Flores

    WebChess is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebChess is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebChess.  If not, see <http://www.gnu.org/licenses/>.
*/

	$_CHESSUTILS = true;
	#require('chess.inc');

	/* ---- access-control helpers ---- */

	/* True if $playerID is one of the two players in $gameID. */
	function isPlayerInGame($gameID, $playerID)
	{
		global $CFG_TABLE;

		if (!is_numeric($gameID) || (int)$playerID <= 0)
			return false;

		$game = db_row("SELECT whitePlayer, blackPlayer FROM " . $CFG_TABLE[games] . " WHERE gameID = ?", [$gameID]);
		if ($game === null)
			return false;

		return ($game['whitePlayer'] == $playerID) || ($game['blackPlayer'] == $playerID);
	}

	/* Aborts the request unless the logged-in player belongs to $gameID. */
	function requirePlayerInGame($gameID)
	{
		if (!isPlayerInGame($gameID, $_SESSION['playerID'] ?? -1))
		{
			http_response_code(403);
			die('Not authorized for this game.');
		}
	}

	/* True if the logged-in player may view $gameID: players see their own
	   games; others see finished games only with the replay-all preference,
	   matching the finished-games list in mainmenu.php. */
	function canViewGame($gameID)
	{
		global $CFG_TABLE;

		$playerID = (int)($_SESSION['playerID'] ?? -1);
		$gameID = filter_var($gameID, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
		if ($gameID === false || $playerID <= 0)
			return false;

		$game = db_row("SELECT whitePlayer, blackPlayer, gameMessage FROM " . $CFG_TABLE[games] . " WHERE gameID = ?", [$gameID]);
		if ($game === null)
			return false;

		if ($game['whitePlayer'] == $playerID || $game['blackPlayer'] == $playerID)
			return true;

		$isFinished = !in_array((string)$game['gameMessage'], ['', 'playerInvited', 'inviteDeclined'], true);
		$isReplayAll = ($_SESSION['pref_replayall'] ?? '') == 'true';
		return $isFinished && $isReplayAll;
	}

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
			case '':
				$pgnCode="";
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
			/* PGN code for moving piece */
			$pgnString .= getPGNCode($piece);

			/* source square */
			$pgnString .= chr($fromCol + 97).($fromRow + 1);

			/* check for captured pieces */
			if ($pieceCaptured != ""  || ($piece == 'pawn' && $fromCol != $toCol))
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

	function webchessMail($msgType, $msgTo, $move, $opponent, $gameID)
	{
		global $CFG_MAILADDRESS, $CFG_MAINPAGE;

		/* default message and subject */
		$mailmsg = "";
		$mailsubject = APP_NAME;
		
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
                                // ToDo: mailmsgundorequest.php ??
		}

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/plain; charset=utf-8\r\n";
		$headers .= 'From: ' . APP_NAME . ' <'.$CFG_MAILADDRESS.">\r\n";
		/* Some MTAs may require for you to uncomment the following line. Do so if mail notification doesn't work */
		//$headers .= "To: ".$msgTo."\r\n";
		$headers .= 'Reply-To: ' . APP_NAME . ' <'.$CFG_MAILADDRESS.">\r\n";

		/* strip CR/LF from the recipient to prevent mail header injection */
		$msgTo = str_replace(array("\r", "\n"), '', (string)$msgTo);

		mail($msgTo, $mailsubject, $mailmsg, $headers);
	}

	/* returns true if the running PHP is at least $vercheck.
	   Historically this guarded PHP < 4.1.0 workarounds; on any supported
	   (PHP 8+) runtime it simply reflects version_compare(). Kept so existing
	   call sites continue to work. */
	function minimum_version( $vercheck ) {
		return version_compare(PHP_VERSION, $vercheck, '>=');
	}

	/* No-op retained for backwards compatibility.
	   The old HTTP_*_VARS arrays and session_register() were removed from PHP
	   long ago; $_POST/$_GET/$_SESSION are superglobals everywhere now, so there
	   is nothing left to fix up. */
	function fixOldPHPVersions()
	{
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

	
	function coordsToSquare($xRow,$xCol)
	{
	  $xRow+=1;
	  $xCol+=1;
	  if($xCol==1) $newCol="a";
	  if($xCol==2) $newCol="b";
	  if($xCol==3) $newCol="c";
	  if($xCol==4) $newCol="d";
	  if($xCol==5) $newCol="e";
	  if($xCol==6) $newCol="f";
	  if($xCol==7) $newCol="g";
	  if($xCol==8) $newCol="h";
	  return $newCol.$xRow;
	}


	function moveToPGNString2($curColor, $piece, $fromRow, $fromCol, $toRow, $toCol, $pieceCaptured, $promotedTo, $isChecking)
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
			
			if ($piece='pawn' && $fromCol!=$toCol)
				$pgnString .="x";
			else
			{
	
			if ($pieceCaptured != "")
				$pgnString .= "x";
			else
				$pgnString .= "";
			}

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

    	function ReturnGameInfo($GameID)
        {
		global $CFG_TABLE;
		global $pWhite,$pWhiteF,$pWhiteL,$pBlack,$pBlackF,$pBlackL,$gStart,$MyColor,$isDraw;

		$tmpGame = db_row("SELECT whitePlayer,blackPlayer,dateCreated,gameMessage FROM " . $CFG_TABLE[games] . " WHERE gameID = ?", [$GameID]);

		$gStart = $tmpGame['dateCreated'];
		$isDraw="";
		if($tmpGame['gameMessage']=="draw"){$isDraw=true;}else{$isDraw="";}

		$xBlack = db_row("SELECT nick,firstName,lastName FROM " . $CFG_TABLE[players] . " WHERE playerID = ?", [$tmpGame['blackPlayer']]);
                $pBlack = $xBlack['nick'];
		$pBlackF = $xBlack['firstName'];
		$pBlackL = $xBlack['lastName'];

                $xWhite = db_row("SELECT nick,firstName,lastName FROM " . $CFG_TABLE[players] . " WHERE playerID = ?", [$tmpGame['whitePlayer']]);
                $pWhite = $xWhite['nick'];
		$pWhiteF = $xWhite['firstName'];
		$pWhiteL = $xWhite['lastName'];

                if ($tmpGame['whitePlayer'] == $_SESSION['playerID'])
                {
			$MyColor="white";
		}
                elseif ($tmpGame['blackPlayer'] == $_SESSION['playerID'])
		{
			$MyColor="black";
		}
		else
		{
			$MyColor="none";
		}
                

        }
?>
