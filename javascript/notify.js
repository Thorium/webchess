/*
    Desktop notifications for WebChess: tell the player when the opponent has
    made a move while the game tab is in the background.

    How it works: while waiting for the opponent, chess.php reloads itself
    (see "autoreload" in board.js). Each page load compares the current number
    of halfmoves with the count this tab saw last time (kept in sessionStorage).
    If the count grew and the newest move was made by the opponent, a
    notification is shown - but only if the player is not already looking at
    the board.

    The feature is opt-in (checkbox below the board) because browsers only
    grant the notification permission in response to a user action. The choice
    is remembered in localStorage; nothing is sent to the server.

    Requires domready.js and the wcNotify config object written by chess.php.
    Include AFTER board.js. Notifications need a secure context (HTTPS or
    localhost) - on plain HTTP the checkbox is shown disabled.
*/

(function () {
	var PREF_KEY = 'wc_notify';

	function lsGet(k)    { try { return localStorage.getItem(k); } catch (e) { return null; } }
	function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) { } }
	function ssGet(k)    { try { return sessionStorage.getItem(k); } catch (e) { return null; } }
	function ssSet(k, v) { try { sessionStorage.setItem(k, v); } catch (e) { } }

	function supported()  { return ('Notification' in window); }
	function permission() { return supported() ? Notification.permission : 'denied'; }
	function enabled()    { return permission() === 'granted' && lsGet(PREF_KEY) === '1'; }

	/* is the player looking at this page right now? */
	function isWatching()
	{
		if (document.hidden)
			return false;
		return (typeof document.hasFocus !== 'function') || document.hasFocus();
	}

	/* the newest move as plain text, e.g. "12. Nf3" or "12... Nf6" ('' if unknown) */
	function lastMoveText()
	{
		if (typeof moves === 'undefined' || moves.length === 0)
			return '';
		var last = moves[moves.length - 1];
		var text = moves.length + '.' + (last[1] != '' ? '.. ' + last[1] : ' ' + last[0]);
		return text.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ');
	}

	function showNotification()
	{
		var body = wcNotify.body;
		var move = lastMoveText();
		if (move !== '')
			body += ' (' + move + ')';
		try
		{
			var n = new Notification(wcNotify.title, {
				body: body,
				icon: 'images/webchess.jpg',
				tag: 'webchess-game-' + gameId	/* a newer move replaces the older notification */
			});
			n.onclick = function () { window.focus(); this.close(); };
		}
		catch (e) { /* e.g. mobile browsers that only allow service-worker notifications */ }
	}

	/* compare with what this tab saw on its previous load of this game */
	function checkForOpponentMove()
	{
		/* a pawn waiting for its promotion piece is not a finished move yet;
		   keep the old count so the notification fires once it is complete
		   (chess.php keeps the autoreload running while the opponent chooses) */
		if (isPromoting == '1')
			return;

		var key = 'wc_seen_moves_' + gameId;
		var seen = ssGet(key);
		ssSet(key, numMoves);

		if (seen === null || numMoves < 0 || numMoves <= parseInt(seen, 10))
			return;		/* first visit, nothing new, or a move was undone */
		if (chessHistory[numMoves][CURCOLOR] == playersColor)
			return;		/* the player's own move */

		if (enabled() && !isWatching())
			showNotification();
	}

	function setHint(text)
	{
		var hint = document.getElementById('notifyHint');
		if (hint)
			hint.innerHTML = '';
		if (hint && text)
			hint.appendChild(document.createTextNode(text));
	}

	function bindToggle()
	{
		var box = document.getElementById('notifyToggle');
		if (!box)
			return;

		if (!supported() || window.isSecureContext === false)
		{
			box.disabled = true;
			setHint(wcNotify.msgUnsupported);
			return;
		}

		box.checked = enabled();
		if (permission() === 'denied')
			setHint(wcNotify.msgBlocked);

		box.onclick = function ()
		{
			if (!box.checked)
			{
				lsSet(PREF_KEY, '0');
				return;
			}

			var done = function (result)
			{
				if (result === 'granted')
				{
					lsSet(PREF_KEY, '1');
					setHint('');
				}
				else
				{
					box.checked = false;
					if (result === 'denied')
						setHint(wcNotify.msgBlocked);
				}
			};

			if (permission() === 'granted')
				done('granted');
			else
			{
				/* older browsers take a callback, newer ones return a promise */
				var p = Notification.requestPermission(done);
				if (p && typeof p.then === 'function')
					p.then(done);
			}
		};
	}

	domready(function () {
		if (typeof wcNotify === 'undefined')
			return;		/* shared PC or not a player: nothing to do */
		bindToggle();
		checkForOpponentMove();
	});
})();
