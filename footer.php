<?php
/**
 * Included at the bottom of every page
 */
?>
	<div class="footer" align="center">
		<p><?php
			$url = '<a href="http://webchess.sourceforge.net/">' . APP_NAME . ' ' . gettext('Version') . ' ' . APP_VERSION .'</a>'; 
			printf(gettext('%s is Free Software released under the GNU General Public License (GPL).'), $url); 
		?></p>
	</div>
