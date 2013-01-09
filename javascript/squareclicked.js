// this is the main function that interacts with the user everytime they click on a square

	/* called whenever a square is clicked on */
	var is1stClick = true;
	
	function squareClickedFirst(row, col, isEmpty, curColor)
	{
		if (getPieceColor(board[row][col]) == curColor)
		{
			document.gamedata.fromRow.value = row;
			document.gamedata.fromCol.value = col;

			highlight(row, col);

			is1stClick = false;
		}
		else
			alert("I'm sorry, but you play the " + curColor +" pieces.");

	}
	
	function squareClickedSecond(row, col, isEmpty, curColor)
	{
		unhighlight(document.gamedata.fromRow.value, document.gamedata.fromCol.value);
		is1stClick = true;

		if ((document.gamedata.fromRow.value == row)
			&& (document.gamedata.fromCol.value == col))
		{
			document.gamedata.fromRow.value = "";
			document.gamedata.fromCol.value = "";
		}
		else
		{
			/* if, on a player's second click, they click on one of their own piece */
			/* act as if he was clicking for the first time (ie: select it) */
			if (board[row][col] != 0 )
				if (getPieceColor(board[row][col]) == curColor)
				{
					squareClickedFirst(row, col, isEmpty, curColor);
					return null;
				}

			var fromRow = document.gamedata.fromRow.value;
			var fromCol = document.gamedata.fromCol.value;
			document.gamedata.toRow.value = row;
			document.gamedata.toCol.value = col;

			if (isValidMove())
			{
				if (DEBUG)
					alert("Move is valid, updating game...");

				var ennemyColor = "white";
				if (curColor == "white")
					ennemyColor = "black";

				/* update board with move (client-side) */
				board[row][col] = board[fromRow][fromCol];
				board[fromRow][fromCol] = 0;
				if (isInCheck(ennemyColor))
				{
					document.gamedata.isInCheck.value = "true";
					document.gamedata.isCheckMate.value = isCheckMate(ennemyColor);
				}
				else
					document.gamedata.isInCheck.value = "false";

				document.gamedata.submit();
			}
			else
			{
				document.gamedata.toRow.value = "";
				document.gamedata.toCol.value = "";
				
				alert("Invalid move:\n" + errMsg);
			}
		}
	}
	
	function squareClicked(row, col, isEmpty)
	{
		if (DEBUG)
			alert('squareClicked -> row = ' + row + ', col = ' + col + ', isEmpty = ' + isEmpty);

		var curColor = "black";
		if ((numMoves == -1) || (numMoves % 2 == 1))
			curColor = "white";

		if (is1stClick && !isEmpty)
			squareClickedFirst(row, col, isEmpty, curColor);
		else
			squareClickedSecond(row, col, isEmpty, curColor);
	}

