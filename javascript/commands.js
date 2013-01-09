// these functions interact with the server

	function undo()
	{
		document.gamedata.requestUndo.value = "yes";
		if (DEBUG)
			alert("gamedata.requestUndo = " + document.gamedata.requestUndo.value);

		document.gamedata.submit();
	}

	function draw()
	{
		document.gamedata.requestDraw.value = "yes";
		if (DEBUG)
			alert("gamedata.requestDraw = " + document.gamedata.requestDraw.value);

		document.gamedata.submit();
	}

	function resigngame()
	{
		document.gamedata.resign.value = "yes";
		if (DEBUG)
			alert("gamedata.resign = " + document.gamedata.resign.value);

		document.gamedata.submit();
	}

	function logout()
	{
		document.gamedata.action = "mainmenu.php";
		document.gamedata.submit();
	}

	function promotepawn()
	{
		var blackPawnFound = false;
		var whitePawnFound = false;
		var i = -1;
		while (!blackPawnFound && !whitePawnFound && i < 8)
		{
			i++;
			
			/* check for black pawn being promoted */
			if (board[0][i] == (BLACK | PAWN))
				blackPawnFound = true;
			
			/* check for white pawn being promoted */
			if (board[7][i] == (WHITE | PAWN))
				whitePawnFound = true;
		}

		/* to which piece is the pawn being promoted to? */
		var promotedTo = 0;
		for (var j = 0; j <= 3; j++)
		{
			if (document.gamedata.promotion[j].checked)
				promotedTo = parseInt(document.gamedata.promotion[j].value);
		}

		/* change pawn to promoted piece */
		var ennemyColor = "black";
		if (blackPawnFound)
		{
			ennemyColor = "white";
			board[0][i] = (BLACK | promotedTo);
			
			if (DEBUG)
				alert("Promoting to: (black) " + board[0][i]);

		}
		else if (whitePawnFound)
		{
			board[7][i] = (WHITE | promotedTo);
			
			if (DEBUG)
				alert("Promoting to: (white) " + board[7][i]);
		}
		else
			alert("WARNING!: cannot find pawn being promoted!");
			
		/* verify check and checkmate status */
		if (isInCheck(ennemyColor))
		{
			if (DEBUG)
				alert("Promotion results in check!");

			document.gamedata.isInCheck.value = "true";
			document.gamedata.isCheckMate.value = isCheckMate(ennemyColor);
		}
		else
			document.gamedata.isInCheck.value = "false";

		/* update board and database */
		document.gamedata.submit();
	}
