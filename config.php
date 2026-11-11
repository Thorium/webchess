<?php
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

	$_CONFIG = true;

    /* database settings
     *
     * Credentials are read from environment variables so that secrets stay out
     * of version control. For local development you may instead create a
     * config.local.php (ignored by git) that sets the $CFG_* variables; see
     * config.local.sample.php for a template.
     */
	if (is_file(__DIR__ . '/config.local.php')) {
		require __DIR__ . '/config.local.php';
	}

	$CFG_SERVER   = $CFG_SERVER   ?? getenv('WEBCHESS_DB_HOST') ?: 'localhost';
	$CFG_USER     = $CFG_USER     ?? getenv('WEBCHESS_DB_USER') ?: 'WebChessUser';
	$CFG_PASSWORD = $CFG_PASSWORD ?? (getenv('WEBCHESS_DB_PASSWORD') !== false ? getenv('WEBCHESS_DB_PASSWORD') : '');
	$CFG_DATABASE = $CFG_DATABASE ?? getenv('WEBCHESS_DB_NAME') ?: 'WebChess_DB';


	/* server settings */
	$CFG_SESSIONTIMEOUT = 900;		/* session times out if user doesn't interact after 900 secs (15 mins) */
	$CFG_EXPIREGAME = 90;			/* number of days before untouched games expire */
	$CFG_MINAUTORELOAD = 10;			/* min number of secs between automatic reloads reloads */
						/* email notification requires PHP to be properly configured for */
	/* NOTE: in chessutils.php a line is commented containing:
	$headers .= "To: ".$msgTo."\r\n";
	Some MTAs may require for you to uncomment such line. Do so if mail notification doesn't work */

	/* SMTP operations.  This flag allows you to easily activate or deactivate this feature.  It is highly recommended you test it before putting it into production */
	$CFG_USEEMAILNOTIFICATION = $CFG_USEEMAILNOTIFICATION ?? false;
	/* email address people see when receiving WebChess generated mail */
	$CFG_MAILADDRESS = $CFG_MAILADDRESS ?? "WebChess@webchess.org";
	/* This URL is displayed in the email notices */
	$CFG_MAINPAGE = $CFG_MAINPAGE ?? "https://github.com/thorium/webchess";

	$CFG_MAXUSERS = 5000;
	$CFG_MAXACTIVEGAMES = 10000;
	$CFG_NICKCHANGEALLOWED = false;		/* whether a user can change their nick from the main menu */

	$CFG_NEW_USERS_ALLOWED = true;

	/* Application constants */
	define('APP_NAME', 'WebChess'); // The name of the app that is shown in the title
	define('APP_VERSION', '1.0.4'); // The version of the app

	/* I18N constants */
	define('I18N_GETTEXT_SUPPORT', false); // enable gettext for fetching translations
	define('I18N_LOCALE', 'de_DE'); // locale to use (requires the webchess.mo file for the locale)

	/* mysql table names */
	define('communication', 'communication');
	define('history', 'history');
	define('games', 'games');
	define('messages', 'messages');
	define('pieces', 'pieces');
	define('preferences', 'preferences');
	define('players', 'players');

	/* mysql table names
	   Change these if your database needs different table names */
	// These definitions are a quick fix for the CFG_TABLE constants


	$CFG_TABLE[communication] = "communication";
	$CFG_TABLE[games] = "games";
	$CFG_TABLE[history] = "history";
	$CFG_TABLE[messages] = "messages";
	$CFG_TABLE[pieces] = "pieces";
	$CFG_TABLE[players] = "players";
	$CFG_TABLE[preferences] = "preferences";

	/* theme settings */
	$CFG_BOARDSQUARESIZE = 50; /* May be used to resize board size */
	$CFG_IMAGE_EXT = "png";

	/* shared security helpers (escaping, CSRF, hardened sessions, password
	   hashing). Loaded here so they are available on every page. */
	require_once __DIR__ . '/security.php';
