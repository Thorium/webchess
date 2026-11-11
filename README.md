# WebChess

A PHP web application you can install on your own web server. It lets you play
chess with other users across the internet or sitting at the same screen. It
only permits valid moves and automatically detects check and checkmate.

This repository: https://github.com/thorium/webchess
Originally created by Jonathan Evraire and Rodrigo Flores — see **Credits** below.

---

## 2026 Security Modernization

This release rewrites the data and authentication layers to run on modern PHP
and to meet current security expectations. Functionality is unchanged; the way
it talks to the database and handles credentials is not.

**Highlights**

- **PHP 8 / PDO.** The removed `mysql_*` extension is gone. All database access
  goes through PDO (`db.php`) using **prepared statements everywhere**, which
  closes the SQL‑injection holes that were present throughout the old code.
- **Modern password hashing.** Passwords are stored with
  `password_hash()` / bcrypt instead of unsalted MD5. Existing MD5 accounts keep
  working and are **transparently re‑hashed to bcrypt on the user's next
  successful login** — no password resets required.
- **CSRF protection** on every state‑changing form (login, registration,
  preferences, invitations, messages, and in‑game move/undo/draw/resign/logout).
- **Access control / authorization.** Authentication is enforced before any
  action runs, and game/message operations verify that the logged‑in user is a
  participant/recipient — closing IDOR holes (loading/moving in or deleting
  other players' games, reading others' private messages, spoofing a message
  sender, and using the shared‑PC prompt as a password oracle).
- **Mail header‑injection** protection on outbound notification emails.
- **Server‑side castling validation.** Move legality is otherwise checked in the
  browser; a forged "castle" request was uniquely able to corrupt the board, so
  castling (rights, a real rook present, empty squares) is now validated on the
  server too.

## Local 2-player (no account, no database)

`local.php` offers a free, login-free, database-free hot-seat game: two people
play on one screen. It reuses the existing client-side engine (move validation,
check/checkmate detection, board rendering) and keeps the whole game in the
browser — nothing is sent to the server. It's linked from the login page
("2-player local game").

Local play adds, reusing the shared engine where possible:

- **Move history** — rendered with the same `displayMoves()`/`moves` the online
  game uses; only the notation is generated client-side (the online game makes
  the same notation server-side).
- **PGN export / import** — export the game, or paste a PGN to replay it
  (WebChess long-algebraic, e.g. `1. e2-e4 e7-e5 2. Ng1-f3`); illegal moves are
  rejected by the normal validator.
- **Piece-set picker** — switch chess set live (saved in localStorage). Online
  players choose their set in account preferences.

## Board appearance

- **Default piece set is GNU Chess Simple.**
- **Board colour schemes** (grey, brown, green, blue, wood) are selectable on
  both the online and local boards — pure client-side CSS, saved in localStorage.
- **Drag-and-drop**: pieces can be dragged as well as clicked, on both boards.
  Drag drives the same validated move flow as clicking; tap-to-move still works
  on touch devices.

## Mobile support

The UI is now responsive: every page carries a `width=device-width` viewport,
a shared `responsive.css` reflows the fixed‑width 2010 layouts into a single
fluid column on small screens, and the chess board scales to fit the viewport.
Desktop appearance is unchanged.
- **Hardened sessions** — `HttpOnly` + `SameSite=Lax` cookies, `Secure` when
  served over HTTPS, and the session id is regenerated on login.
- **Output escaping** of user/DB‑derived values to prevent XSS.
- **Secrets out of version control** — database credentials are no longer stored
  in `config.php`.

## Requirements

- PHP **8.1+** with the **PDO MySQL** driver (`pdo_mysql`)
- MySQL / MariaDB

## Configuration

Database credentials are provided **outside** of `config.php` (which is
committed) in one of two ways:

1. **Environment variables** (recommended for production):

   | Variable | Default |
   | --- | --- |
   | `WEBCHESS_DB_HOST` | `localhost` |
   | `WEBCHESS_DB_USER` | `WebChessUser` |
   | `WEBCHESS_DB_PASSWORD` | *(empty)* |
   | `WEBCHESS_DB_NAME` | `WebChess_DB` |

2. **A local override file.** Copy `config.local.sample.php` to
   `config.local.php` (git‑ignored) and set the `$CFG_*` values there.

See `docs/INSTALL-MySQL.txt` for full installation instructions.

## ⚠️ Breaking changes (upgrading from an older WebChess)

1. **Run these schema migrations before deploying** against an existing database
   (fresh installs already include them):

   ```sql
   -- Bcrypt hashes are up to 60 chars; the legacy column was char(32) (MD5 width).
   -- Without this, the first successful login truncates the new hash and locks the user out.
   ALTER TABLE players MODIFY password VARCHAR(255) NOT NULL;

   -- The app stores textual skill labels like '(Novice)' in userlevel, but the
   -- legacy column was tinyint(1), which silently discarded them.
   ALTER TABLE players MODIFY userlevel VARCHAR(20) NOT NULL DEFAULT '1';
   ```

2. **Move your database credentials** out of `config.php` into environment
   variables or `config.local.php` (see *Configuration* above). After upgrading,
   `config.php` no longer contains them.

3. **PHP 8.2+ with `pdo_mysql` is now required.** The old `mysql_*` extension is
   no longer used and PHP 5/7‑only deployments are no longer supported.
   Run at least 8.2.35, 8.3.36, 8.4.27 or 8.5.12: older builds have the
   unauthenticated RCE flaws CVE-2026-109618 and CVE-2026-109619 ("PHP2Shell"),
   and PHP 8.1 gets no fix. WebChess takes no file uploads, so also set these in
   `php.ini` or the FPM pool (not `.user.ini` or `ini_set()`):

   ```ini
   session.upload_progress.enabled = 0
   post_max_size = 8M
   ```

4. **Remove the installer scripts after install.** `install.php` and
   `makeConfig.php` are now disabled by default and only run when the
   environment variable `WEBCHESS_ENABLE_INSTALLER=1` is set. Set it while
   installing, then delete both files (or leave the variable unset) on a
   live site.

---

## Credits

WebChess was **originally created by Jonathan Evraire and Rodrigo Flores** and
hosted on SourceForge:

- Original project: http://sourceforge.net/projects/webchess/
- Original site: http://webchess.sourceforge.net/

This repository (https://github.com/thorium/webchess) is a modernized fork; the
original authors' copyright and the GNU GPL are retained throughout. Thanks also
to the other contributors credited in the source headers and `docs/CHANGELOG.txt`
(including Dadi Jonsson and Michael Evraire).

## Changelog

WebChess Unofficial 1.0.4 (2026-06-20)
 + Security modernization: PHP 8 / PDO with prepared statements (SQL-injection fixes),
   bcrypt password hashing with transparent md5 upgrade, CSRF protection, hardened
   sessions, output escaping, access-control/IDOR fixes, and server-side validation
   of castling, promotion and en-passant.
 + Mobile-friendly responsive layout; the board scales to the viewport.
 + Login-free, database-free 2-player local (hot-seat) game (local.php) with
   move history, PGN export/import and a piece-set picker.
 + Board colour schemes (grey/brown/green) and drag-and-drop on both boards.

WebChess Unofficial 1.0.0rc3 (2013-12-08)
 + changes:
   - Updated the chessdb.php::saveGame() to be more efficient so moves are processed quicker.
   - Added Deutsch translations for gettext. This is disabled in lang.php by default.
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
   - The replay button titles and text can now be translated
   - Email messages can now be translated
   - Fixed issue where emails weren't being sent (From email address had mistype in variable name)
   - Moved footer html in to footer.php
   - Changes the charset of main pages to UTF-8
   - Replaces the hardcoded app name 'WebChess' with a constant in config.php

WebChess 1.0.0rc2 (2010-08-14)
 + minor changes:
   - user interface appearance changes
   - watch also other players' games
   - md5 hash passwords in database
