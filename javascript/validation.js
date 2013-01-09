// these functions are used to test the validity of moves

// object definition (used by isSafe)
function GamePiece()
{
	this.piece = 0;
	this.dist = 0;
}

	/* isSafe tests whether the square at testRow, testCol is safe */
	/* for a piece of color testColor to travel to */
	function isSafe(testRow, testCol, testColor)
	{
		/* NOTE: if a piece occupates the square itself,
			that piece does not participate in determining the safety of the square */

		/* IMPORTANT: note that if we're checking to see if the square is safe for a pawn
			we're moving, we need to verify the safety for En-passant */

		/* OPTIMIZE: cache results (if client-side game only, invalidate cache after each move) */

		/* AI NOTE: just because a square isn't entirely safe doesn't mean we don't want to
			move there; for instance, we may be protected by another piece */

		/* DESIGN NOTE: this function is mostly designed with CHECK checking in mind and
			may not be suitable for other purposes */

		if (DEBUG)
			alert("in isSafe(" + testRow + ", " + testCol + ", " + testColor + ")");
		
		var ennemyColor = 0;
		if (testColor == 'white')
			ennemyColor = 128; /* 1000 0000 */
		
		/* check for knights first */
		if (((testRow - 1) >= 0) && ((testCol - 2) >= 0))
			if (board[testRow - 1][testCol - 2] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight at (" + (testRow - 1) + ", " + (testCol - 2) + ")");

				return false;
			}

		if (((testRow + 1) < 8) && ((testCol - 2) >= 0))
			if (board[testRow + 1][testCol - 2] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");
				
				return false;
			}

		if (((testRow - 2) >= 0) && ((testCol - 1) >= 0))
			if (board[testRow - 2][testCol - 1] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		if (((testRow - 2) >= 0) && ((testCol + 1) < 8))
			if (board[testRow - 2][testCol + 1] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		if (((testRow - 1) >= 0) && ((testCol + 2) < 8))
			if (board[testRow - 1][testCol + 2] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		if (((testRow + 1) < 8) && ((testCol + 2) < 8))
			if (board[testRow + 1][testCol + 2] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		if (((testRow + 2) < 8) && ((testCol - 1) >= 0))
			if (board[testRow + 2][testCol - 1] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		if (((testRow + 2) < 8) && ((testCol + 1) < 8))
			if (board[testRow + 2][testCol + 1] == (KNIGHT | ennemyColor))
			{
				if (DEBUG)
					alert("isSafe -> knight");

				return false;
			}

		/* tactic: start at test pos and check all 8 directions for an attacking piece */
		/* directions:
			0 1 2
			7 * 3
			6 5 4
		*/
		var pieceFound = new Array();
		for (i = 0; i < 8; i++)
			pieceFound[i] = new GamePiece();

		for (i = 1; i < 8; i++)
		{
			if (((testRow - i) >= 0) && ((testCol - i) >= 0))
				if ((pieceFound[0].piece == 0) && (board[testRow - i][testCol - i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[0] = " + board[testRow - i][testCol - i] + "\ndist = " + i);

					pieceFound[0].piece = board[testRow - i][testCol - i];
					pieceFound[0].dist = i;
				}

			if ((testRow - i) >= 0)
				if ((pieceFound[1].piece == 0) && (board[testRow - i][testCol] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[1] = " + board[testRow - i][testCol] + "\ndist = " + i);

					pieceFound[1].piece = board[testRow - i][testCol];
					pieceFound[1].dist = i;
				}

			if (((testRow - i) >= 0) && ((testCol + i) < 8))
				if ((pieceFound[2].piece == 0) && (board[testRow - i][testCol + i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[2] = " + board[testRow - i][testCol + i] + "\ndist = " + i);

					pieceFound[2].piece = board[testRow - i][testCol + i];
					pieceFound[2].dist = i;
				}

			if ((testCol + i) < 8)
				if ((pieceFound[3].piece == 0) && (board[testRow][testCol + i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[3] = " + board[testRow][testCol + i] + "\ndist = " + i);

					pieceFound[3].piece = board[testRow][testCol + i];
					pieceFound[3].dist = i;
				}

			if (((testRow + i) < 8) && ((testCol + i) < 8))
				if ((pieceFound[4].piece == 0) && (board[testRow + i][testCol + i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[4] = " + board[testRow + i][testCol + i] + "\ndist = " + i);

					pieceFound[4].piece = board[testRow + i][testCol + i];
					pieceFound[4].dist = i;
				}

			if ((testRow + i) < 8)
				if ((pieceFound[5].piece == 0) && (board[testRow + i][testCol] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[5] = " + board[testRow + i][testCol] + "\ndist = " + i);

					pieceFound[5].piece = board[testRow + i][testCol];
					pieceFound[5].dist = i;
				}

			if (((testRow + i) < 8) && ((testCol - i) >= 0))
				if ((pieceFound[6].piece == 0) && (board[testRow + i][testCol - i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[6] = " + board[testRow + i][testCol - i] + "\ndist = " + i);

					pieceFound[6].piece = board[testRow + i][testCol - i];
					pieceFound[6].dist = i;
				}

			if ((testCol - i) >= 0)
				if ((pieceFound[7].piece == 0) && (board[testRow][testCol - i] != 0))
				{
					if (DEBUG)
						alert("isSafe -> pieceFound[7] = " + board[testRow][testCol - i] + "\ndist = " + i);

					pieceFound[7].piece = board[testRow][testCol - i];
					pieceFound[7].dist = i;
				}
		}
		
		/* check pieces found for possible threats */
		for (var i = 0; i < 8; i++)
			if ((pieceFound[i].piece != 0) && ((pieceFound[i].piece & BLACK) == ennemyColor))
				switch(i)
				{
					/* diagonally: queen, bishop, pawn, king */
					case 0:
					case 2:
					case 4:
					case 6:
						if (((pieceFound[i].piece & COLOR_MASK) == QUEEN)
								|| ((pieceFound[i].piece & COLOR_MASK) == BISHOP))
						{
							if (DEBUG)
								alert("isSafe -> notKnight -> diagonal -> Q or B -> " + getPieceColor(pieceFound[i].piece) + " " + getPieceName(pieceFound[i].piece) + "\ndist = " + pieceFound[i].dist + "\ndir = " + i);
							return false;
						}

						if ((pieceFound[i].dist == 1)
								&& ((pieceFound[i].piece & COLOR_MASK) == PAWN))
						{
							if (DEBUG)
								alert("isSafe -> notKnight -> diagonal -> Pawn -> " + getPieceColor(pieceFound[i].piece) + " " + getPieceName(pieceFound[i].piece) + "\ndist = " + pieceFound[i].dist + "\ndir = " + i);
							if ((ennemyColor == WHITE) && ((i == 0) || (i == 2)))
								return false;
							else if ((ennemyColor == BLACK) && ((i == 4) || (i == 6)))
								return false;
						}

						if ((pieceFound[i].dist == 1)
								&& ((pieceFound[i].piece & COLOR_MASK) == KING))
						{
							if (DEBUG)
								alert("isSafe -> notKnight -> diagonal -> King -> " + getPieceColor(pieceFound[i].piece) + " " + getPieceName(pieceFound[i].piece) + "\ndist = " + pieceFound[i].dist + "\ndir = " + i);
							/* save current board destination */
							var tmpPiece = board[testRow][testCol];

							/* update board with move (client-side) */
							board[testRow][testCol] = pieceFound[i].piece;

							var kingRow = 0;
							var KingCol = 0;
							switch(i)
							{
								case 0: kingRow = testRow - 1; kingCol = testCol - 1;
									break;
								case 1: kingRow = testRow - 1; kingCol = testCol;
									break;
								case 2: kingRow = testRow - 1; kingCol = testCol + 1;
									break;
								case 3: kingRow = testRow;     kingCol = testCol + 1;
									break;
								case 4: kingRow = testRow + 1; kingCol = testCol + 1;
									break;
								case 5: kingRow = testRow + 1; kingCol = testCol;
									break;
								case 6: kingRow = testRow + 1; kingCol = testCol - 1;
									break;
								case 7: kingRow = testRow;     kingCol = testCol - 1;
									break;
							}
							
							board[kingRow][kingCol] = 0;
		
							/* if king needs to move into check to capture piece, isSafe() is true */
							var tmpIsSafe = isInCheck(getOtherColor(testColor));

							/* restore board to previous state */
							board[kingRow][kingCol] = pieceFound[i].piece;
							board[testRow][testCol] = tmpPiece;

							/* if king CAN eat target without moving into check, return false */
							/* otherwise, continue checking other piecesFound */
							if (!tmpIsSafe)
								return false;
						}
						break;

					/* horizontally/vertically: queen, rook, king */
					case 1:
					case 3:
					case 5:
					case 7:
						if (((pieceFound[i].piece & COLOR_MASK) == QUEEN)
								|| ((pieceFound[i].piece & COLOR_MASK) == ROOK))
						{
							if (DEBUG)
								alert("isSafe -> notKnight -> horiz/vert -> Q or R -> " + getPieceColor(pieceFound[i].piece) + " " + getPieceName(pieceFound[i].piece) + "\ndist = " + pieceFound[i].dist + "\ndir = " + i);

							return false;
						}

						if ((pieceFound[i].dist == 1)
								&& ((pieceFound[i].piece & COLOR_MASK) == KING))
						{
							if (DEBUG)
								alert("isSafe -> notKnight -> horiz/vert -> King -> " + getPieceColor(pieceFound[i].piece) + " " + getPieceName(pieceFound[i].piece) + "\ndist = " + pieceFound[i].dist + "\ndir = " + i);
							/* save current board destination */
							var tmpPiece = board[testRow][testCol];

							/* update board with move (client-side) */
							board[testRow][testCol] = pieceFound[i].piece;

							var kingRow = 0;
							var KingCol = 0;
							switch(i)
							{
								case 0: kingRow = testRow - 1; kingCol = testCol - 1;
									break;
								case 1: kingRow = testRow - 1; kingCol = testCol;
									break;
								case 2: kingRow = testRow - 1; kingCol = testCol + 1;
									break;
								case 3: kingRow = testRow;     kingCol = testCol + 1;
									break;
								case 4: kingRow = testRow + 1; kingCol = testCol + 1;
									break;
								case 5: kingRow = testRow + 1; kingCol = testCol;
									break;
								case 6: kingRow = testRow + 1; kingCol = testCol - 1;
									break;
								case 7: kingRow = testRow;     kingCol = testCol - 1;
									break;
							}
							
							board[kingRow][kingCol] = 0;
		
							/* if king needs to move into check to capture piece, isSafe() is true */
							var tmpIsSafe = isInCheck(getOtherColor(testColor));

							/* restore board to previous state */
							board[kingRow][kingCol] = pieceFound[i].piece;
							board[testRow][testCol] = tmpPiece;

							/* if king CAN eat target without moving into check, return false */
							/* otherwise, continue checking other piecesFound */
							if (!tmpIsSafe)
								return false;
						}
						break;
				}
		if (DEBUG)
			alert("isSafe is true");

		return true;
	}

	function isValidMoveKing(fromRow, fromCol, toRow, toCol, tmpColor)
	{
		/* temporarily move king to destination to see if in check */
		var tmpPiece = board[toRow][toCol];
		board[toRow][toCol] = board[fromRow][fromCol];
		board[fromRow][fromCol] = 0;
		
		/* The king does not move to a square that is attacked by an enemy piece */
		if (isInCheck(tmpColor))
		{
			/* return king to original position */
			board[fromRow][fromCol] = board[toRow][toCol];
			board[toRow][toCol] = tmpPiece;

			if (DEBUG)
				alert("king -> destination not safe!");

			errMsg = "Cannot move into check.";
			return false;
		}

		/* return king to original position */
		board[fromRow][fromCol] = board[toRow][toCol];
		board[toRow][toCol] = tmpPiece;

		/* NORMAL MOVE: */
		if ((Math.abs(toRow - fromRow) <= 1) && (Math.abs(toCol - fromCol) <= 1))
		{
			if (DEBUG)
				alert("king -> normal move");

			return true;
		}
		/* CASTLING: */
		else if ((fromRow == toRow) && (fromCol == 4) && (Math.abs(toCol - fromCol) == 2))
		{
			/*
			The following conditions must be met:
			    * The King and rook must occupy the same rank (or row).
			    * The king that makes the castling move has not yet moved in the game.
			*/
			if (DEBUG)
				alert("isValidMoveKing -> Castling");

			var rookCol = 0;
			if (toCol - fromCol == 2)
				rookCol = 7;

			/* ToDo: chessHistory check can probably be cut in half by only checking every other move (ie: current color's moves) */
			for (i = 0; i <= numMoves; i++)
			{
				/* if king has already moved */
				if ((chessHistory[i][FROMROW] == fromRow) && (chessHistory[i][CURPIECE] == "king"))
				{
					errMsg = "Can only castle if king has not moved yet.";
					return false;
				}
				/* if rook has already moved */
				else if ((chessHistory[i][FROMROW] == fromRow) && (chessHistory[i][FROMCOL] == rookCol))
				{
					errMsg = "Can only castle if rook has not moved yet.";
					return false;
				}
			}

			/*
			    * All squares between the rook and king before the castling move are empty.
			*/
			tmpStep = (toCol - fromCol) / 2;
			for (i = 4 + tmpStep; i != rookCol; i += tmpStep)
				if (board[fromRow][i] != 0)
				{
					if (DEBUG)
						alert("king -> castling -> square not empty");
							
					errMsg = "Can only castle if there are no pieces between the rook and the king";
					return false;
				}

			/*
			    * The king is not in check.
			    * The king does not move over a square that is attacked by an enemy piece during the castling move
			*/

			/* NOTE: the king's destination has already been checked, so */
			/* all that's left is it's initial position and it's final one */
			if (isSafe(fromRow, fromCol, tmpColor)
					&& isSafe(fromRow, fromCol + tmpStep, tmpColor))
			{
				if (DEBUG)
					alert("king -> castling -> VALID!");

				return true;
			}
			else
			{
				if (DEBUG)
					alert("king -> castling -> moving over attacked square");

				errMsg = "When castling, the king cannot move over a square that is attacked by an ennemy piece";
				return false;
			}
		}
		/* INVALID MOVE */
		else
		{
			if (DEBUG)
				alert("king -> completely invalid move\nfrom " + fromRow + ", " + fromCol + "\nto " + toRow + ", " + toCol);
			errMsg = "Kings cannot move like that.";
			return false;
		}

		if (DEBUG)
			alert("king -> castling -> unknown error");
	}

	/* checks whether a pawn is making a valid move */
	function isValidMovePawn(fromRow, fromCol, toRow, toCol, tmpDir)
	{
		if (((toRow - fromRow)/Math.abs(toRow - fromRow)) != tmpDir)
		{
			errMsg = "Pawns cannot move backwards, only forward.";
			return false;
		}

		/* standard move */
		if ((tmpDir * (toRow - fromRow) == 1) && (toCol == fromCol) && (board[toRow][toCol] == 0))
			return true;
		/* first move double jump - white */
		if ((tmpDir == 1) && (fromRow == 1) && (toRow == 3) && (toCol == fromCol) && (board[2][toCol] == 0) && (board[3][toCol] == 0))
			return true;
		/* first move double jump - black */
		if ((tmpDir == -1) && (fromRow == 6) && (toRow == 4) && (toCol == fromCol) && (board[5][toCol] == 0) && (board[4][toCol] == 0))
			return true;
		/* standard eating */
		else if ((tmpDir * (toRow - fromRow) == 1) && (Math.abs(toCol - fromCol) == 1) && (board[toRow][toCol] != 0))
			return true;
		/* en passant - white */
		else if ((tmpDir == 1) && (fromRow == 4) && (toRow == 5) && (board[4][toCol] == (PAWN | BLACK)))
		{
			/* can only move en passant if last move is the one where the white pawn moved up two */
			if ((chessHistory[numMoves][TOROW] == 4) && (chessHistory[numMoves][TOCOL] == toCol))
				return true;
			else
			{
				errMsg = "Pawns can only move en passant immediately after an opponent played his pawn.";
				return false;
			}
		}
		/* en passant - black */
		else if ((tmpDir == -1) && (fromRow == 3) && (toRow == 2) && (board[3][toCol] == PAWN))
		{
			/* can only move en passant if last move is the one where the black pawn moved up two */
			if ((chessHistory[numMoves][TOROW] == 3) && (chessHistory[numMoves][TOCOL] == toCol))
				return true;
			else
			{
				errMsg = "Pawns can only move en passant immediately after an opponent played his pawn.";
				return false;
			}
		}
		else
		{
			errMsg = "Pawns cannot move like that.";
			return false;
		}
	}

	/* checks wether a knight is making a valid move */
	function isValidMoveKnight(fromRow, fromCol, toRow, toCol)
	{
		errMsg = "Knights cannot move like that.";
		if (Math.abs(toRow - fromRow) == 2)
		{
			if (Math.abs(toCol - fromCol) == 1)
				return true;
			else
				return false;
		}
		else if (Math.abs(toRow - fromRow) == 1)
		{
			if (Math.abs(toCol - fromCol) == 2)
				return true;
			else
				return false;
		}
		else
		{
			return false;
		}
	}

	/* checks whether a bishop is making a valid move */
	function isValidMoveBishop(fromRow, fromCol, toRow, toCol)
	{
		if (Math.abs(toRow - fromRow) == Math.abs(toCol - fromCol))
		{
			if (toRow > fromRow)
			{
				if (toCol > fromCol)
				{
					for (i = 1; i < (toRow - fromRow); i++)
						if (board[fromRow + i][fromCol + i] != 0)
						{
							errMsg = "Bishops cannot jump over other pieces.";
							return false;
						}
				}
				else
				{
					for (i = 1; i < (toRow - fromRow); i++)
						if (board[fromRow + i][fromCol - i] != 0)
						{
							errMsg = "Bishops cannot jump over other pieces.";
							return false;
						}
				}

				return true;
			}
			else
			{
				if (toCol > fromCol)
				{
					for (i = 1; i < (fromRow - toRow); i++)
						if (board[fromRow - i][fromCol + i] != 0)
						{
							errMsg = "Bishops cannot jump over other pieces.";
							return false;
						}
				}
				else
				{
					for (i = 1; i < (fromRow - toRow); i++)
						if (board[fromRow - i][fromCol - i] != 0)
						{
							errMsg = "Bishops cannot jump over other pieces.";
							return false;
						}
				}

				return true;
			}
		}
		else
		{
			errMsg = "Bishops cannot move like that.";
			return false;
		}
	}
	
	/* checks wether a rook is making a valid move */
	function isValidMoveRook(fromRow, fromCol, toRow, toCol)
	{
		if (toRow == fromRow)
		{
			if (toCol > fromCol)
			{
				for (i = (fromCol + 1); i < toCol; i++)
					if (board[fromRow][i] != 0)
					{
						errMsg = "Rooks cannot jump over other pieces.";
						return false;
					}
			}
			else
			{
				for (i = (toCol + 1); i < fromCol; i++)
					if (board[fromRow][i] != 0)
					{
						errMsg = "Rooks cannot jump over other pieces.";
						return false;
					}

			}

			return true;
		}
		else if (toCol == fromCol)
		{
			if (toRow > fromRow)
			{
				for (i = (fromRow + 1); i < toRow; i++)
					if (board[i][fromCol] != 0)
					{
						errMsg = "Rooks cannot jump over other pieces.";
						return false;
					}
			}
			else
			{
				for (i = (toRow + 1); i < fromRow; i++)
					if (board[i][fromCol] != 0)
					{
						errMsg = "Rooks cannot jump over other pieces.";
						return false;
					}

			}

			return true;
		}
		else
		{
			errMsg = "Rooks cannot move like that.";
			return false;
		}
	}
	
	/* this function checks whether a queen is making a valid move */
	function isValidMoveQueen(fromRow, fromCol, toRow, toCol)
	{
		if (isValidMoveRook(fromRow, fromCol, toRow, toCol) || isValidMoveBishop(fromRow, fromCol, toRow, toCol))
			return true;
		
		if (errMsg.search("jump") == -1)
			errMsg = "Queens cannot move like that.";
		else
			errMsg = "Queens cannot jump over other pieces.";

		return false;
	}
	
	/* this functions checks to see if curColor is in check */
	function isInCheck(curColor)
	{
		var targetKing = getPieceCode(curColor, "king");

		/* find king */
		for (i = 0; i < 8; i++)
			for (j = 0; j < 8; j++)
				if (board[i][j] == targetKing)
					/* verify it's location is safe */
					return !isSafe(i, j, curColor);

		/* the next lines will hopefully NEVER be reached */
		errMsg = "CRITICAL ERROR: KING MISSING!"
		return false;
	}
	
	function isValidMove()
	{
		var fromRow = parseInt(document.gamedata.fromRow.value);
		var fromCol = parseInt(document.gamedata.fromCol.value);
		var toRow = parseInt(document.gamedata.toRow.value);
		var toCol = parseInt(document.gamedata.toCol.value);

		var tmpDir = 1;
		var curColor = "white";
		if (board[fromRow][fromCol] & BLACK)
		{
			tmpDir = -1;
			curColor = "black";
		}

		var isValid = true;
		switch(board[fromRow][fromCol] & COLOR_MASK)
		{
			case PAWN:
				isValid = isValidMovePawn(fromRow, fromCol, toRow, toCol, tmpDir);
				break;
			case KNIGHT:
				isValid = isValidMoveKnight(fromRow, fromCol, toRow, toCol);
				break;
			case BISHOP:
				isValid = isValidMoveBishop(fromRow, fromCol, toRow, toCol);
				break;
			case ROOK:
				isValid = isValidMoveRook(fromRow, fromCol, toRow, toCol);
				break;
			case QUEEN:
				isValid = isValidMoveQueen(fromRow, fromCol, toRow, toCol);
				break;
			case KING:
				if (DEBUG)
					alert("isValidMove -> King");

				isValid = isValidMoveKing(fromRow, fromCol, toRow, toCol, curColor);
				
				if (DEBUG)
					alert("isValidMove -> King -> isValid = " + isValid);
				break;
			default:	/* ie: not implemented yet */
				if (DEBUG)
					alert("unknown game piece");

				isValid = true;
		}

		/* now that we know the move itself is valid, let's make sure we're not moving into check */
		/* NOTE: we don't need to check for the king since it's covered by isValidMoveKing() */
		
		if ((board[fromRow][fromCol] & COLOR_MASK) != KING)
		{
			if (DEBUG)
				alert("isValidMove -> are we moving into check?");
			
			/* save current board destination */
			var tmpPiece = board[toRow][toCol];

			/* update board with move (client-side) */
			board[toRow][toCol] = board[fromRow][fromCol];
			board[fromRow][fromCol] = 0;
		
			/* are we in check now? */
			if (isInCheck(curColor))
			{
				if (DEBUG)
					alert("isValidMove -> moving into check -> CHECK!");

				/* if so, invalid move */
				errMsg = "Cannot move into check.";
				isValid = false;
			}
			
			/* restore board to previous state */
			board[fromRow][fromCol] = board[toRow][toCol];
			board[toRow][toCol] = tmpPiece;
		}

		if (DEBUG)
			alert("isValidMove returns " + isValid);
		
		return isValid;
	}

