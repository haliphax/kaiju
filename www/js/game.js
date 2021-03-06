/* jshint laxbreak: true, laxcomma: true */
// game
require(
	[
		'lib/jquery'
		, 'lib/jquery-ui'
		, 'lib/knockout'
		, 'global'
		, 'viewModels/game'
		, 'bindingHandlers/jqAccordion'
		, 'bindingHandlers/jqButton'
		, 'bindingHandlers/jqDialog'
		, 'bindingHandlers/jqProgressBar'
		, 'bindingHandlers/gameMapTile'
		, 'extenders/scrollFollow'
	]
	, function ($, $ui, ko, global, GameViewModel) {
		var
			// "this" context == Window; let's make an object, instead
			game = {}
			// naked model for ajax data
			, obj = {
				actions: []
				, cells: []
				, corpses: 0
				, effects: []
				, elev: 0
				, info: {}
				, msg: []
				, occ: []
				, skills: []
				, stat: {
					actor: 0
					, aname: ''
					, clan: 0
					, clan_name: ''
					, evtm: 0
					, evts: 0
					, faction: 0
					, faction_name: ''
					, indoors: null
					, last: 0
					, map: 0
					, stat_ap: 0
					, stat_apmax: 0
					, stat_hp: 0
					, stat_hpmax: 9
					, stat_mp: 0
					, stat_mpmax: 0
					, stat_xp: 0
					, stat_xplevel: 0
					, stat_xpspent: 0
					, user: 0
					, x: 0
					, y: 0
				}
				, surr: ''
			}
		;

		//// properties
		game.ajaxCounter = 0;
		game.client = {};
		game.loadedViews = [];
		game.messageLimit = 1000;

		//// methods

		// end AJAX transmission (counter for status icon)
		game.transx_dec = function () {
			game.ajaxCounter = Math.max(--game.ajaxCounter, 0);
			if (game.ajaxCounter === 0) $('#trans_icon').removeClass('ui-state-error');

			// restart the auto-update timer
			if (game.client.status_timer !== false) {
				game.client.status_timer = setTimeout(
					game.client.status_update
					, game.client.status_delay
				);
			}
		};

		// begin AJAX transmission (counter for status icon)
		game.transx_inc = function () {
			// stop the auto-update timer
			clearTimeout(game.client.status_timer);
			game.ajaxCounter++;
			$('#trans_icon').addClass('ui-state-error');
		};

		//// client properties
		game.client.status_delay = 8000;

		//// client methods

		// load action view
		game.client.action_loadview = function (reqs, atype, aname, firstcall, callback) {
			var
				which = atype + '/' + aname
				, view = 'actions/' + which
				, viewHtml
			;

			// only load if we already haven't
			if (game.loadedViews.indexOf(view) != -1) {
				return (typeof callback == 'function'
					? callback(game.client)
					: null
				);
			}

			// remember loading the view
			game.loadedViews.push(view);

			// pull down the view's HTML
			game.client.ajax({
				dataType: 'HTML'
				, url: 'client/actionview/' + which
				, success: function (data) {
					viewHtml = data;

					// load the view's requirements and fire the callback
					require(reqs, function () {
						$('<div>').html(viewHtml).detach().appendTo('#loadingzone');
						if (typeof firstcall == 'function') firstcall(game.client);
						if (typeof callback == 'function') callback(game.client);
					});
				}
			});
		};

		// ajax request (with defaults)
		game.client.ajax = function (params) {
			var opts, defaults = {
				beforeSend: game.transx_inc
				, cache: false
				, error: game.ajaxError
				, complete: game.transx_dec
				, type: 'GET'
				, dataType: 'JSON'
			};

			opts = $.extend({}, defaults, params);

			if(opts.url[opts.url.length - 1] != '/')
				opts.url = opts.url + '/';

			opts.url = global.site_url + opts.url + (opts.url.indexOf('?') > -1 ? '&' : '?') + 'csrf=' + global.csrf;
			$.ajax(opts);
		};

		// submit chat text/command
		game.client.chat = function () {
			var text = $('#chat_input').val();

			if(text.replace(/ /ig, '').length == 0) return false;

			$('#chat_input').val('');

			game.client.ajax({
				url: 'client/chat'
				, type: 'post'
				, data: { text: text }
				, dataType: 'json'
				, success: function (ret) {
					game.client.status_bind(ret);
				}
			});

			return false;
		};

		// describe clan
		game.client.clan_describe = function () {}; // @TODO

		// describe effect
		game.client.effect_describe = function (effect) {
			game.client.ajax({
				url: 'client/describe/effect/' + effect.effect
				, success: function (data) {
					ko.applyBindings(data.eff, $('#effect_desc')[ 0 ]);
					$('#effect_desc').dialog('open');
				}
			});
		};

		// describe faction
		game.client.faction_describe = function () {}; // @TODO

		// sort items
		game.client.item_sort = function (a, b) {
			var sa = a.iname.toLowerCase(), sb = b.iname.toLowerCase();
			return (sa == sb ? 0 : (sa < sb ? -1 : 1));
		};

		// skill: load view
		game.client.skill_loadview = function (reqs, abbrev, firstcall, callback) {
			var
				view = 'skills/' + abbrev
				, viewHtml
			;

			// only load if we already haven't
			if (game.loadedViews.indexOf(view) != -1) {
				return (typeof callback == 'function'
					? callback(game.client)
					: null
				);
			}

			// remember loading the view
			game.loadedViews.push(view);

			// pull down the view's HTML
			game.client.ajax({
				type: 'get'
				, url: 'client/skillview/' + abbrev
				, success: function (data) {
					viewHtml = data;

					// load the view's requirements and fire the callback
					require(reqs, function () {
						$('<div>').html(viewHtml).detach().appendTo('#loadingzone');
						if (typeof firstcall == 'function') firstcall(game.client);
						if (typeof callback == 'function') callback(game.client);
					});
				}
			});
		};

		// bind stat results
		game.client.status_bind = function (data) {
			var msgs = 0, actions = null;

			for(var i in data) {
				if(! data.hasOwnProperty(i)) continue;

				switch (i) {
					case 'actb':
					case 'actc':
					case 'actd':
					case 'actg':
						if (actions === null) actions = [];
						if (data[ i ] !== '') actions = actions.concat(data[i]);
						break;
					case 'stat':
						for (var s in data.stat) {
							if (s == 'stat_ap' && data.stat[s] <= 0) {
								game.viewModel.msg.push(['...', 'You are exhausted.']);
								clearTimeout(game.client.status_timer);
								game.client.status_timer = false;
								$('#coord_coords').attr('data-bind', null).html('&hellip;');
							}

							game.viewModel.stat[s](data.stat[s]);
						}

						break;
					case 'msg':
						for(var m in data.msg) {
							var lastmsg = game.viewModel.msg()[game.viewModel.msg().length - 1];

							if(typeof lastmsg != 'undefined' && data.msg[m][1] == lastmsg[1]) {
								var $el = $('#log_text span:last');
								$el.html($el.html() + ' <small>(' + data.msg[m][0] + ')</small>');
								continue;
							}

							game.viewModel.msg.push(data.msg[m]);
						}

						// trim the chat log
						msgs = game.viewModel.msg().length;

						if (msgs > game.messageLimit) {
							game.viewModel.msg.splice(game.messageLimit, msgs - game.messageLimit);
						}

						break;
					default:
						if (game.viewModel.hasOwnProperty(i)) {
							game.viewModel[i](data[i]);
						}

						break;
				}
			}

			if (actions !== null) {
				game.viewModel.actions(actions);
			}

			if (! data.hasOwnProperty('effects')) {
				game.viewModel.effects([]);
			}
		};

		// update status
		game.client.status_update = function (map, force, applyBindings) {
			game.client.ajax({
				url: 'client/status/' + (map ? '1' : '0') + '/' + (force ? '1' : '0')
				, success: function (data) {
					game.client.status_bind.call(this, data);

					if (typeof applyBindings != 'undefined') {
						// remove loading graphic
						$('#wrapper').show();
						$('#loading_bg').hide('fade', function () { $(this).remove(); });
						ko.applyBindings(game.viewModel, $('#ui_body')[0]);
					}
				}
			});
		};

		//// init

		$(function () {
			// preload images
			[
				'img/ui-spritesheet.png'
				, 'img/walls-spritesheet.png'
				, 'img/tiles-spritesheet.png'
				, 'img/ui/bamboo.jpg'
				, 'img/ui/gradient.gif'
				, 'img/pawns/person.png'
				, 'img/pawns/other.png'
			]
				.forEach(function (val, idx, arr) {
					$('<img>').attr('src', global.base_url + val).appendTo('#loadingzone').bind('load', function(){ $(this).remove(); });
				})
			;

			// prep viewmodel
			game.viewModel = new GameViewModel(obj, game.client);

			// clear chat hint on first click
			$('#chat_input').bind('focus', function () {
				$(this).val('').unbind('focus').css('color', 'black');
			});

			game.client.status_update(1, 1, 1);
		});
	}
);
