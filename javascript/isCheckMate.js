
/*
*****************************************************************************
isCheckMate() pseudocode
*****************************************************************************

1) can king move out of checkmate?
2) can attacker be eaten?
3) can attacker be blocked?


for each of the possible squares the king can move to, use isSafe() to see if king can move there
  if one of them is safe, isCheckMate() = false

from king's position, scan out in all 8 directions to find attackers (note: there may be two!)

for each attacker found:
  - use isSafe() on attacker to see if attacker can be eaten; if so, isCheckMate() = false
  - if the attacker is not a knight
      o then for each square between the attacker and the king:
          . use isSafe() to determine if a move is possible to block the attacker; if so, isCheckMate() = false

if none of these is possible, isCheckMate() = true
*/

/* directions:
    0     1
 2  8  9 10  3
   15  * 11
 4 14 13 12  5
    6     7
 NOTE: directions 0 through 7 are actually positions and represent knights!
*****************************************************************************
  END PSEUDOCODE
*****************************************************************************
*/

/* reset getNextAttacker function */
curDir = -1;

/* IMPORTANT NOTE!!!
   getNextAttacker() will currently only work when called for one situation
   (ie: one set of coords, no changes to board between calls) and as such
   should NOT be used outside of isCheckmate()
   
   POSSIBLE FIXES:
   - if getNextAttacker() does not need to be nested, reseting the function
     before each call should do the trick
   - build a 3D lookup table based on targetRow, targetCol and targetColor;
     if current parameters exist in table, set curDir to value;
     else add entry to table, reset curDir;
     before exiting getNextAttacker(), update lookup table with curDir
*/
function getNextAttacker(targetRow, targetCol, targetColor, attackerCoords)
{
	var attackerColor = getOtherColor(targetColor);
	while(curDir <= 15)
	{
		var rowStep, colStep;
		
		/* start next direction */
		curDir++;
		
		switch(curDir)
		{
			case 0:
				if (isInBoard(targetRow + 2, targetCol - 1))
					if (board[targetRow + 2][targetCol - 1] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow + 2;
						attackerCoords.col = targetCol - 1;
						return true;
					}
				break;
			case 1:
				if (isInBoard(targetRow + 2, targetCol + 1))
					if (board[targetRow + 2][targetCol + 1] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow + 2;
						attackerCoords.col = targetCol + 1;
						return true;
					}
				break;
			case 2:
				if (isInBoard(targetRow + 1, targetCol - 2))
					if (board[targetRow + 1][targetCol - 2] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow + 1;
						attackerCoords.col = targetCol - 2;
						return true;
					}
				break;
			case 3:
				if (isInBoard(targetRow + 1, targetCol + 2))
					if (board[targetRow + 1][targetCol + 2] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow + 1;
						attackerCoords.col = targetCol + 2;
						return true;
					}
				break;
			case 4:
				if (isInBoard(targetRow - 1, targetCol - 2))
					if (board[targetRow - 1][targetCol - 2] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow - 1;
						attackerCoords.col = targetCol - 2;
						return true;
					}
				break;
			case 5:
				if (isInBoard(targetRow - 1, targetCol + 2))
					if (board[targetRow - 1][targetCol + 2] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow - 1;
						attackerCoords.col = targetCol + 2;
						return true;
					}
				break;
			case 6:
				if (isInBoard(targetRow - 2, targetCol - 1))
					if (board[targetRow - 2][targetCol - 1] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow - 2;
						attackerCoords.col = targetCol - 1;
						return true;
					}
				break;
			case 7:
				if (isInBoard(targetRow - 2, targetCol + 1))
					if (board[targetRow - 2][targetCol + 1] == getPieceCode(attackerColor, "knight"))
					{
						attackerCoords.row = targetRow - 2;
						attackerCoords.col = targetCol + 1;
						return true;
					}
				break;
			case 8:
				rowStep = 1;
				colStep = -1;
				break;
			case 9:
				rowStep = 1;
				colStep = 0;
				break;
			case 10:
				rowStep = 1;
				colStep = 1;
				break;
			case 11:
				rowStep = 0;
				colStep = 1;
				break;
			case 12:
				rowStep = -1;
				colStep = 1;
				break;
			case 13:
				rowStep = -1;
				colStep = 0;
				break;
			case 14:
				rowStep = -1;
				colStep = -1;
				break;
			case 15:
				rowStep = 0;
				colStep = -1;
				break;
		}

		if (curDir > 7)
		{
			var attackerFound = false;
			var i = 1;
			while (isInBoard(targetRow + (i * rowStep), targetCol + (i * colStep)) && !attackerFound)
			{
				if (board[targetRow + (i * rowStep)][targetCol + (i * colStep)] != 0)
				{
					attackerFound = true;
					if (getPieceColor(board[targetRow + (i * rowStep)][targetCol + (i * colStep)]) == attackerColor)
						if (isAttacking(board[targetRow + (i * rowStep)][targetCol + (i * colStep)], targetRow + (i * rowStep), targetCol + (i * colStep), attackerColor, targetRow, targetCol))
						{
							attackerCoords.row = targetRow + (i * rowStep);
							attackerCoords.col = targetCol + (i * colStep);
						
							return true;
						}
				}
				i++;
			}
		}
	}
	
	/* return true if attacker found, false otherwise */
	return false;
}

function isInBoard(row, col)
{
	if ((row >= 0) && (row <= 7) && (col >= 0) && (col <= 7))
		return true;
	else
		return false;
}

/* NOTE: isAttacking() assumes no piece exists between attacker and target, such is the case in getNextAttacker() */
function isAttacking(attackerPiece, attackerRow, attackerCol, attackerColor, targetRow, targetCol)
{
	var rowDiff = Math.abs(attackerRow - targetRow);
	var colDiff = Math.abs(attackerCol - targetCol);

	switch(attackerPiece & COLOR_MASK)
	{
		case PAWN:
			var forwardDir = -1;
			if (attackerColor == "white")
				forwardDir = 1;

			if ((colDiff == 1) && ((targetRow - attackerRow) == forwardDir))
				return true;
			break;
			
		case ROOK:
			if ((rowDiff == 0) || (colDiff == 0))
				return true;
			break;
			
		case KNIGHT:
			if (((rowDiff == 2) && (colDiff == 1)) || ((rowDiff == 1) && (colDiff == 2)))
				return true;
			break;
			
		case BISHOP:
			if (rowDiff == colDiff)
				return true;
			break;
			
		case QUEEN:
			if ((rowDiff == 0) || (colDiff == 0) || (rowDiff == colDiff))
				return true;
			break;
			
		case KING:
			if ((rowDiff <= 1) && (colDiff <= 1))
				return true;
			break;
	}

	return false;
}

function canBlockAttacker(attackerPiece, attackerRow, attackerCol, attackerColor, targetRow, targetCol)
{
	var tmpAttackerPiece = attackerPiece & COLOR_MASK;

	/* Knights can never be blocked */
	if (tmpAttackerPiece == KNIGHT)
		return false;

	/* setup loop parameters */
	var rowDiff = attackerRow - targetRow;
	var colDiff = attackerCol - targetCol;
	
	var rowStep = 0;
	if (rowDiff != 0)
		rowStep = rowDiff / Math.abs(rowDiff);
	
	var colStep = 0;
	if (colDiff != 0)
		colStep = colDiff / Math.abs(colDiff);

	var numSteps = Math.max(Math.abs(rowDiff), Math.abs(colDiff));
	var ennemyDir = 1;
	var ennemyPawn = BLACK | PAWN;
	var friendlyPawn = attackerColor | PAWN;
	if (attackerColor == BLACK)
	{
		ennemyPawn = WHITE | PAWN;
		ennemyDir = -1;
	}

	/* for each square between the attacker and the target... */
	for (var i = 1; i < numSteps; i++)
	{
		/* isSafe() will take into account pawns eating diagonally, which don't apply here */
		/* so check for pawns diagonally and replace them with friendly pawns before checking */
		/* friendly pawns are used instead of completely removing them from the board because the pawn */
		/* might be blocking a bishop or queen from blocking the check */
		var tmpPawnFound1 = false;
		var tmpPawnFound2 = false;

		if (isInBoard(targetRow + (i * rowStep) + ennemyDir, targetCol + (i * colStep) - 1))
		{
			if (DEBUG)
				alert("canBeBlocked() -> checking for ennemy pawn at (" + (targetRow + (i * rowStep) + ennemyDir) + ", " + (targetCol + (i * colStep) - 1) + ")");
			
			if (board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) - 1] == ennemyPawn)
			{
				if (DEBUG)
					alert("ennemy pawn found!  Removing from board...");
				
				board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) - 1] = friendlyPawn;
				tmpPawnFound1 = true;
			}
		}
		
				if (isInBoard(targetRow + (i * rowStep) + ennemyDir, targetCol + (i * colStep) + 1))
		{
			if (DEBUG)
				alert("canBeBlocked() -> checking for ennemy pawn at (" + (targetRow + (i * rowStep) + ennemyDir) + ", " + (targetCol + (i * colStep) + 1) + ")");
		
			if (board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) + 1] == ennemyPawn)
			{
				if (DEBUG)
					alert("ennemy pawn found!  Removing from board...");
			
				board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) + 1] = friendlyPawn;
				tmpPawnFound2 = true;
			}
		}
		
		
		/* if a piece of the target's color can move there, the attack can be blocked */
		/* NOTE: pawn's are a special case since isSafe would determine a pawn cannot move forward to a target square */
		var tmpCanBlockAttacker = false;
		if (!isSafe(targetRow + (i * rowStep), targetCol + (i * colStep), attackerColor))		
		{
			/* if pawn's were removed from the board, replace them */
			if (tmpPawnFound1)
				board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) - 1] = ennemyPawn;

			if (tmpPawnFound2)
				board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) + 1] = ennemyPawn;

			return true;
		}
		else if (isInBoard(targetRow + (i * rowStep) + ennemyDir, targetCol + (i * colStep)))
		{
			if (board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep)] == ennemyPawn)
			{
				/* if pawn's were removed from the board, replace them */
				if (tmpPawnFound1)
					board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) - 1] = ennemyPawn;

				if (tmpPawnFound2)
					board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) + 1] = ennemyPawn;

				return true;
			}
		}

		/* if pawn's were removed from the board, replace them */
		if (tmpPawnFound1)
			board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) - 1] = ennemyPawn;

		if (tmpPawnFound2)
			board[targetRow + (i * rowStep) + ennemyDir][targetCol + (i * colStep) + 1] = ennemyPawn;
	}

	return false;
}

function isCheckMate(curColor)
{
	var kingRow = 0;
	var kingCol = 0;
	var targetKing = getPieceCode(curColor, "king");
	
	/* find king */
	for (var i = 0; i < 8; i++)
		for (var j = 0; j < 8; j++)
			if (board[i][j] == targetKing)
			{
				kingRow = i;
				kingCol = j;
			}
	
	/* temporarily remove king from board */
	board[kingRow][kingCol] = 0;
	
	/* check the squares around the king for a safe move */
	/* possible bug: this doesn't take into account the king moving out of check by eating a non-attacker, like a rook diagonally */
	for (var i = -1; i <= 1; i++)
		for (var j = -1; j <= 1; j++)
			if (((i != 0) || (j != 0)) && isInBoard(kingRow + i, kingCol + j))
				if ((board[kingRow + i][kingCol + j] == 0) && (isSafe(kingRow + i, kingCol + j, curColor)))
					return false;

	/* return king to board */
	board[kingRow][kingCol] = targetKing;

	var attackerColor = getOtherColor(curColor);
	
	/* for each attacker... (can be two) */
	var attackerCoords = {row:0, col:0};
	while(getNextAttacker(kingRow, kingCol, curColor, attackerCoords))
	{
		var attackerRow = attackerCoords.row;
		var attackerCol = attackerCoords.col;

		/* can attacker be captured */
		var canBeCaptured = !isSafe(attackerRow, attackerCol, attackerColor);
		
		/* temporarily switch king to enney pawn, otherwise canBeBlocked() things the king can block */
		board[kingRow][kingCol] = PAWN | attackerColor;
	
		/* can attacker be blocked */
		var canBeBlocked = false;
		if (canBlockAttacker(board[attackerRow][attackerCol], attackerRow, attackerCol, attackerColor, kingRow, kingCol))
			canBeBlocked = true;

		/* return king to board */
		board[kingRow][kingCol] = targetKing;
		
		if (!canBeCaptured && !canBeBlocked)
			return true;
	}

	return false;
}
