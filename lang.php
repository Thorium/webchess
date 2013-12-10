<?php 

// $Id: lang.php,v 1.1 2013/12/08 14:00:00 gitjake Exp $

/*
    This file is part of WebChess. http://webchess.sourceforge.net
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

if (!defined('I18N_LOCALE') || !defined('I18N_GETTEXT_SUPPORT')) {
	exit('Your config.php is Missing the I18N_LOCALE and/or I18N_GETTEXT_SUPPORT constant(s).'
		. '<br>You can manually copy these from the makeConfig.php to your config.php file.');
}

if ( ! function_exists('gettext')) {
	function gettext($text) {
		return htmlspecialchars($text);
	}
}

// Must do some testing before releasing it to mainstream
// Note that any changes to a .mo file won't be picked up by Apache until it's
// restarted due to caching. The PHP CLI can be used for testing
if(I18N_GETTEXT_SUPPORT) {
	
	putenv('LANG=' . I18N_LOCALE); 
	putenv('LC_ALL=' . I18N_LOCALE);
	setlocale(LC_ALL, I18N_LOCALE);
	
	// Set the text domain as 'webchess'
	$domain = 'webchess';
	bindtextdomain($domain, "./locale"); 
	textdomain($domain);
}
