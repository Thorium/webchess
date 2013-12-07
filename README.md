Known issues;

   - If a player reloads the board while a turn is being processed then the board may be incomplete.

WebChess 1.0.0rc3 (2013-12-07)

 + minor fixes:
   - Fixed issue where you couldn't create a game
   - Fixed issue where you couldn't accept a game
   - Fixed issue where the board wouldn't load
   - Removed PHP short tags
   - Added missing '' ENUM value `games` table columns
   - Added missing `players``userlevel` column
   - Updated `players` table definition to hold the md5 password and change the CHARs to VARCHARs
   - Removed some PHP warnings and notices
   - These fixes were minimal just to get a game up and running and see if it's worth playing
   - Changed the window.onload to document.onready (using domready.js)
   - Fixed issue with columns collapsing if they contained no pieces
   - Fixed issue with slow moves (changed single SQL inserts in to a batch insert)
 
WebChess 1.0.0rc2 (2010-08-14)
 + minor changes:
   - user interface appearance changes
   - watch also other players' games
   - md5 hash passwords in database

A PHP Web Application that you can install on your own web server. It allows you to play chess with other users across the internet or sitting at the same screen. It only permits valid moves and can automatically detect check and checkmate status.

Original website: http://sourceforge.net/projects/webchess/
