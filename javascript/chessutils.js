 /* these are utility functions used by other functions */

	function getPieceColor(piece)
	{
		if (BLACK & piece)
			return "black";
		else
			return "white";
	}

	function getPieceName(piece)
	{
		var pieceName = new Array();
		pieceName[PAWN] = "pawn";
		pieceName[ROOK] = "rook";
		pieceName[KNIGHT] = "knight";
		pieceName[BISHOP] = "bishop";
		pieceName[QUEEN] = "queen";
		pieceName[KING] = "king";

		return pieceName[piece & COLOR_MASK];
	}

	function getPieceCode(color, piece)
	{
		var code;
		switch(piece)
		{
			case "pawn":
				code = PAWN;
				break;
			case "knight":
				code = KNIGHT;
				break;
			case "bishop":
				code = BISHOP;
				break;
			case "rook":
				code = ROOK;
				break;
			case "queen":
				code = QUEEN;
				break;
			case "king":
				code = KING;
				break;
		}

		if (color == "black")
			code = BLACK | code;

		return code;
	}

	function highlight(row, col)
	{
		if (board[parseInt(row)][parseInt(col)] != "")
		{
			eval("document.images['pos" + row + "-" + col + "'].src = 'images/' + CURRENTTHEME + '/' + getPieceColor(board[row][col]) + '_' + getPieceName(board[row][col]) + '_highlighted.gif'");
		}

		return true;
	}

	function unhighlight(row, col)
	{
		if (DEBUG)
			alert("unhighlight -> row = " + row + ", col = " + col);

		if (board[parseInt(row)][parseInt(col)] != "")
		{
			eval("document.images['pos" + row + "-" + col + "'].src = 'images/' + CURRENTTHEME + '/' + getPieceColor(board[row][col]) + '_' + getPieceName(board[row][col]) + '.gif'");
		}

		return true;
	}

	function getOtherColor(color)
	{
		if (color == "white")
			return "black";
		else
			return "white";
	}

