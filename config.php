<?
	$_CONFIG = true;

	/* database settings */
	$CFG_SERVER = "localhost";
	$CFG_USER = "WebChessUser";
	$CFG_PASSWORD = "WebChessPassword";
	$CFG_DATABASE = "WebChess_0_8_2";

	/* server settings */
	$CFG_SESSIONTIMEOUT = 900;		/* session times out if user doesn't interact after 600 secs (10 mins) */
	$CFG_EXPIREGAME = 14;			/* number of days before untouched games expire */
	$CFG_MINAUTORELOAD = 5;			/* min number of secs between automatic reloads reloads */
						/* email notification requires PHP to be properly configured for */
	$CFG_USEEMAILNOTIFICATION = false;	/* SMTP operations.  This flag allows you to easily activate
						   or deactivate this feature.  It is highly recommended you test
						   it before putting it into production */
						/* email address people see when receiving WebChess generated mail */
	$CFG_MAILADDRESS = "WebChess@webchess.org";

	$CFG_MAXUSERS = 50;
	$CFG_MAXACTIVEGAMES = 50;
	$CFG_NICKCHANGEALLOWED = false;		/* whether a user can change their nick from the main menu */
?>
