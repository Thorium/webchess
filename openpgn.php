<?php
// $Id: openpgn.php,v 1.3 2010/08/14 16:57:54 sandking Exp $

/*
    This file is part of WebChess. https://github.com/thorium/webchess
	Copyright 2010 Jonathan Evraire, Rodrigo Flores, Dadi Jonsson

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

	/* load settings (also pulls in the security helpers) */
	if (!isset($_CONFIG))
		require 'config.php';

	secure_session_start();

	/* define constants */
	require 'chessconstants.php';

	/* include outside functions */
#	if (!isset($_CHESSUTILS))
	require 'chessutils.php';
	require 'gui.php';
	require 'chessdb.php';

	/* check session status (must be logged in to export a game) */
	require 'sessioncheck.php';

	/* debug flag */
	define ("DEBUG", 0);

	/* connect to database */
	require 'connectdb.php';

	/* same viewing rule as chess.php */
	if (!isset($_SESSION['gameID']) || !canViewGame($_SESSION['gameID']))
	{
		http_response_code(403);
		die('Not authorized for this game.');
	}

	/* load game */

$output_file = 'game'.$_SESSION['gameID'].'.pgn';

header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP 1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s') . 'GMT');
header('Content-Transfer-Encoding: none');
header('Content-Type: application/x-chess-pgn; name="'. $output_file . '"');
header('Content-Disposition: attachment; filename="' .$output_file . '"');
// header("Content-length: $content_len");

loadHistory();
ReturnGameInfo($_SESSION['gameID']);
writePGN();
?>
