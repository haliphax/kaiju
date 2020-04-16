/* jshint laxbreak: true, laxcomma: true */
// viewModels/game
define(
	[ 'lib/jquery', 'lib/knockout', 'global' ]
	, function ($, ko, global) {
		return function (obj, client) {
			// make it easy to track the parent ViewModel in child functions
			var	self = this;

			//// properties
			self.actions = ko.observableArray(obj.actions);
			self.attack = ko.observable(0);
			self.cells = ko.observableArray(obj.cells);
			self.client = client;
			self.corpses = ko.observable(obj.corpses);
			self.effects = ko.observableArray(obj.effects);
			self.elev = ko.observable(obj.elev);
			self.info = ko.observable(obj.info);
			self.msg = ko.observableArray(obj.msg).extend({
				scrollFollow: '#log_text' });
			self.occ = ko.observableArray(obj.occ);
			self.occ_far = ko.computed(function() {
				var elev = self.elev();
				return self.occ().filter(function(o) {
					return o.elev != elev; });
			});
			self.occ_near = ko.computed(function() {
				var elev = self.elev();
				return self.occ().filter(function(o) {
					return o.elev == elev; });
			});
			self.skills = ko.observable(obj.skills);
			self.surr = ko.observable(obj.surr);
			// collection of observables constructed during init (below)
			self.stat = {};
			self.viewModels = {};

			//// methods

			self.fn = {};

			// action: click
			self.fn.action_click = function (target) {
				if (this.js == 1) {
					return self.fn.action_customJS.call(this, target);
				}

				if (this.params !== null && this.params != 0) {
					return self.fn.action_params.call(this, target);
				}

				return self.fn.action_fire.call(this, target);
			};

			// action: run custom JS
			self.fn.action_customJS = function (target) {
				var me = this;

				if (typeof 'aname' == 'function') me = ko.toJS(this);

				require(
					[ 'actions/' + me.atype + '/' + me.abbrev
						+ (global.debug ? '' : '.min') ]
					, function (action) {
						if(! action.hasOwnProperty('initialized')
							&& typeof action.init == 'function')
						{
							action.initialized = true;
							action.init(self, client);
						}

						action.fire(target, self, client);

						if (typeof target != 'undefined') {
							self.fn.actor_menu(target, 1);
						}
					}
				);

				return false;
			};

			// action: fire generic
			self.fn.action_fire = function (target) {
				var me = this, params = null, url;

				if (typeof this.params == 'function') me = ko.toJS(this);

				if (me.params !== null && me.params != 0) {
					params = $('#actparams select').val();
				}

				url = 'client/action/' + me.atype + '/' + me.abbrev;
				if(typeof target !== 'undefined') url += '/' + target;
				if(params !== null) url += '/' + params;
				url += '/';

				client.ajax({
					url: url
					, success: function (data) {
						if (typeof target != 'undefined') {
							self.fn.actor_menu(target, 1);
						}

						client.status_bind(data);

						if (params !== null) {
							self.fn.action_params.call(me, target);
						}
					}
				});

				return false;
			};

			// action or skill: get cost text
			self.fn.action_costText = function () {
				if (typeof this.cost_ap == 'undefined') {
					this.cost_ap = this.cost;
					this.cost_mp = 0;
				}

				return (this.cost_ap > 1 || this.cost_mp > 0
					? '<small>(' + (this.cost_ap> 1 ? this.cost_ap: '')
						+ (this.cost_mp > 1 ? (this.cost_a > 1 ? ',' : '')
						+ this.cost_mp+ 'm' : '') + ')</small>'
					: ''
				);
			};

			// action: show parameters dialog
			self.fn.action_params = function (target) {
				var me = this;

				// pull parameters
				client.ajax({
					url: 'client/actparams/' + me.atype + '/' + me.abbrev
					, success: function (data) {
						me.actname = data.actname;
						me.params = data.params;
						me.target = target;
						me.vm = self;

						for(var i in me.params) {
							me.params[i][1] = $('<div />').html(me.params[i][1])
								.text();
						}

						if (typeof self.viewModels.actionParams == 'undefined') {
							if (global.debug) {
								console.log('DEBUG: Building action viewmodel '
									+ 'for ' + me.actname);
							}

							self.viewModels.actionParams =
								ko.mapping.fromJS(me);
							ko.applyBindings(self.viewModels.actionParams,
								$('#actparams')[ 0 ]);
						}

						self.viewModels.actionParams.params(me.params);

						// show dialog
						var
							$profile = $('#profile')
							, $actparams = $('#actparams')
						;

						if (typeof target != 'undefined') {
							$.mobile.switchPopup($profile, $actparams
								, function(){
									$actparams
										.find('select').selectmenu('refresh')
										.end().popup('open');
								}
							);
						} else {
							$actparams.find('select').selectmenu('refresh')
								.end().popup('open');
						}
					}
				});

				return false;
			};

			// action: repeat
			self.fn.action_repeat = function (target) {
				var me = this, params, url;

				if (typeof this.params == 'function') me = ko.toJS(this);
				params = (this.params == 1
					? $('#actparams select').val() : null);
				url = 'client/repeat/action/' + me.atype + '/' + me.abbrev;
				if (params !== null) url += '/' + params;

				client.ajax({
					url: url
					, success: function (data) {
						if (typeof target != 'undefined') {
							self.fn.actor_menu(target, 1);
						}

						client.status_bind.call(this, data);
					}
				});

				return false;
			};

			// actor: open interaction menu
			self.fn.actor_menu = function (actor, descr) {
				var me = this, params, url;

				url = 'client/actor/' + actor;
				if (typeof descr !== 'undefined') url += '/1';

				// pull actor data
				client.ajax({
					url: url
					, success: function (actorinfo) {
						actorinfo.actor = actor;

						if (typeof descr == 'undefined'
							&& (typeof self.viewModels.actorMenu == 'undefined'
								|| self.viewModels.actorMenu.actor() != actor)
						) {
							actorinfo.descr = [];
						}

						if (! actorinfo.hasOwnProperty('skills')) {
							actorinfo.skills = [];
						}

						if (! actorinfo.hasOwnProperty('acta')) {
							actorinfo.acta = [];
						}

						if (! actorinfo.hasOwnProperty('rel')) {
							actorinfo.rel = null;
						}

						if (typeof self.viewModels.actorMenu == 'undefined') {
							if (global.debug) {
								console.log('DEBUG: Building actor menu');
							}

							self.viewModels.actorMenu =
								ko.mapping.fromJS(actorinfo);
							self.viewModels.actorMenu.skills =
								ko.observableArray([]);
							// link to client
							self.viewModels.actorMenu.client = client;
							// link to self
							self.viewModels.actorMenu.vm = self;

							//// added functionality
							self.viewModels.actorMenu.fn = {
								// actor: attack
								attack: function (actor) {
									client.ajax({
										url: 'client/attack/' + actor
										, success: function (data) {
											if (typeof descr != 'undefined') {
												self.fn.actor_menu(actor);
											} else {
												self.fn.actor_menu(actor, 1);
											}

											client.status_bind(data);
										}
									});
								}
								// actor: whisper
								, whisper: function (actor) {
									$('#chat_input').click()
										.val('/w "' + actor + '" ').focus();
								}
							};

							// bind
							ko.applyBindings(self.viewModels.actorMenu,
									$('#profile')[ 0 ]);
						}

						for(var i in actorinfo.skills) {
							var s = actorinfo.skills[i];

							if(! s.params) continue;

							for(var j in s.params) {
								s.params[j][1] = $('<div />')
									.html(s.params[j][1]).text();
							}
						}

						for(i in actorinfo.acta) {
							var a = actorinfo.acta[i];

							if(! a.params) continue;

							for(var j in a.params) {
								a.params[j][1] = $('<div />')
									.html(a.params[j][1]).text();
							}
						}

						for (i in actorinfo) {
							if (! self.viewModels.actorMenu.hasOwnProperty(i)) {
								continue;
							}

							self.viewModels.actorMenu[i](actorinfo[i]);
						}

						// show the dialog
						$('#profile')
							.find('ul').listview('refresh').end()
							.popup('open');
					}
				});

				return false;
			};

			// describe item
			self.fn.item_describe = function (instance) {
				self.client.ajax({
					url: 'client/describe/item/' + instance
					, success: function (data) {
						var
							div = $('#item_desc')[ 0 ]
							, item = {
								armor: {
									class: null
									, slashing: null
									, piercing: null
									, blunt: null
								}
								, weapon: {
									distance: null
									, dmg_type: null
									, dmg_min: null
									, dmg_max: null
								}
								, ammo: {
									dmg: null
								}
								, dmg_bonus: null
								, iclass: []
								, iname: null
								, img: null
								, txt: null
								, durability: null
								, durmax: null
								, eq_type: null
							}
							, vm
						;

						vm = ko.mapping.fromJS(data.descr, {}, item);
						if(vm.img.length > 0)
							vm.img = global.base_url + 'images/items/' + vm.img;
						div = $('#item_desc')[ 0 ];
						ko.cleanNode(div);
						ko.applyBindings(vm, div);
						$('#item_desc').dialog('open');
					}
				});

				return false;
			};

			// minimap: show
			self.fn.minimap_show = function () {
				var
					$button = $('#minimap-button')
					, text = $button.text()
				;

				$button.text('Loading...');

				// pull the map
				$('#minimap img').attr('src', global.base_url
					+ 'client/minimap?csrf=' + global.csrf + '&_='
					+ new Date().getTime()).bind('load', function() {
						$button.text(text);
						$('#minimap').popup('open');
						$(this).unbind('load');
					});

				return false;
			};

			// move on the map
			self.fn.move = function (x, y) {
				client.ajax({
					url: 'client/move/' + x + '/' + y
					, success: function (data) {
						client.status_bind(data);
					}
				});

				return false;
			};

			// player: view inventory
			self.fn.player_inventory = function () {
				// pull inventory data
				client.ajax({
					url: 'client/inventory'
					, success: function (data) {
						self.viewModels.playerInventory.enc(data.enc);
						self.viewModels.playerInventory.inv(data.inv);
						// show the dialog
						$('#inventory').dialog('open');
					}
				});

				return false;
			};

			// skill: click
			self.fn.skill_click = function (target) {
				if (this.js == 1) {
					return self.fn.skill_customJS.call(this, target);
				}

				if (this.params == 1) {
					return self.fn.skill_params.call(this, target);
				}

				return self.fn.skill_fire.call(this, target);
			};

			// skill: fire custom JS
			self.fn.skill_customJS = function (target) {
				var me = this;

				// flatten observables if necessary
				if (typeof this.abbrev == 'function') me = ko.toJS(this);

				require(
					[ 'skills/' + me.abbrev + (global.debug ? '' : '.min') ]
					, function (skill) {
						if(! skill.hasOwnProperty('initialized')
							&& typeof skill.init == 'function')
						{
							skill.initialized = true;
							skill.init(self, client);
						}

						skill.fire(target, self, client);

						if (typeof target != 'undefined') {
							self.fn.actor_menu(target, 1);
						}
					}
				);

				return false;
			};

			// skill: fire generic
			self.fn.skill_fire = function (target) {
				var me = this, params = null, url;

				// flatten observables if necessary
				if (typeof me.params == 'function') me = ko.toJS(me);

				if (me.params !== null && me.params != 0) {
					if (typeof target != 'undefined') {
						params = $('#skillparams_' + me.abbrev).val();
					} else {
						params = $('#skillparams select').val();
					}
				}

				url = 'client/skill/' + me.skill;
				if (typeof target != 'undefined') url += '/' + target;
				if (params !== null) url += '/' + params;

				client.ajax({
					url: url
					, success: function (data) {
						if (typeof target != 'undefined') {
							self.fn.actor_menu(target, 1);
						}

						client.status_bind(data);

						if (typeof target == 'undefined' && params !== null) {
							self.fn.skill_params.call(me);
						}
					}
				});

				return false;
			};

			// skill: show parameters dialog
			self.fn.skill_params = function () {
				var me = this;

				// pull parameters
				client.ajax({
					url: 'client/skillparams/' + me.skill
					, success: function (data) {
						me.vm = self;
						me.params = data.params;
						me.sname = data.sname;

						for(var i in me.params) {
							me.params[i][1] = $('<div />')
								.html(me.params[i][1]).text();
						}

						if(! self.viewModels.hasOwnProperty('skillParams')) {
							if (global.debug)  {
								console.log('DEBUG: Building skill viewmodel '
									+ 'for ' + data.sname);
							}

							self.viewModels.skillParams = ko.mapping.fromJS(me);
							ko.applyBindings(self.viewModels.skillParams,
								$('#skillparams')[0]);
						}

						self.viewModels.skillParams.params(me.params);
						self.viewModels.skillParams.sname(me.sname);
						self.viewModels.skillParams.skill(me.skill);
						$('#skillparams')
							.dialog('option', 'title', me.sname)
							.dialog('open');
					}
				});

				return false;
			};

			// skill: repeat
			self.fn.skill_repeat = function (target) {
				var me = this, params, url;

				if (typeof me.params == 'function') me = ko.toJS(me);
				params = (me.params !== 0
					? $('#skillparams select').val() : null);

				url = 'client/repeat/skill/' + me.skill;
				if (typeof target != 'undefined') url += '/' + target;
				if (params !== null) url += '/' + params;

				client.ajax({
					url: url
					, success: function (data) {
						if (data.hasOwnProperty('target')) {
							self.fn.actor_menu(data.target, 1);
						}

						client.status_bind.call(this, data);
					}
				});

				return false;
			};

			// clan: describe
			self.fn.describe_clan = function (clan) {
				if(! clan) return;

				var me = this;

				client.ajax({
					url: 'clans/info/' + clan + '/'
					, success: function (data) {
						var attribs = [ 'isleader', 'clan', 'faction', 'descr',
							'policy', 'map', 'building', 'shield', 'x', 'y',
							'members', 'leader', 'leader_name', 'fmatch',
							'faction_name', 'rel' ];

						me.vm = self;

						attribs.forEach(function(v, i, a) {
							me[v] = data.hasOwnProperty(v) ? data[v] : null;
						});

						if (typeof self.viewModels.clanInfo == 'undefined') {
							if (global.debug) {
								console.log('DEBUG: Building clan info '
									+ 'viewmodel');
							}

							self.viewModels.clanInfo = ko.mapping.fromJS(me);

							self.viewModels.clanInfo.fn = {};

							self.viewModels.clanInfo.fn.add_relation =
							function() {
								client.ajax({
									url: 'clans/add_relation/' + me.clan + '/'
									, success: function(data) {
										if(data.success) {
											alert('Clan relation added');

											var
												theirfac = self.viewModels
													.clanInfo.faction()
												, myfac = self.stat.faction()
												, isally = theirfac == myfac
											;

											self.viewModels.clanInfo.rel(
												isally ? 'Ally' : 'Enemy');
										}
									}
								});
							};

							ko.applyBindings(self.viewModels.clanInfo,
								$('#claninfo')[0]);
						}

						attribs.forEach(function(v, i, a) {
							self.viewModels.clanInfo[v](me[v]);
						});

						$('#claninfo').dialog('open');
					}
				});
			};

			//// viewmodels

			// player inventory //

			// base viewmodel properties
			self.viewModels.playerInventory = {
				client: client
				, vm: self
				, enc: ko.observable()
				, inv: ko.observableArray()
			};

			// function node
			self.viewModels.playerInventory.fn = {};

			// inventory action
			self.viewModels.playerInventory.fn.action = function (action, inum) {
				if (action == 'drop' &&
						! confirm('Are you sure you wish to drop this item?'))
				{
					return;
				}

				client.ajax({
					url: 'client/' + action + '/' + inum
					, success: function (data) {
						client.status_bind(data);
						// update the view
						self.fn.player_inventory();
					}
				});
			};

			// inventory bulk action
			self.viewModels.playerInventory.fn.action_bulk = function (action) {
				var
					$items = $('#inventory_tbl input[type="checkbox"]:checked')
					, items = []
				;

				$('#inv_multi').val('');

				if (! confirm('Are you sure you want to perform this bulk '
					+ 'action?'))
				{
					return;
				}

				$items.each(function () {
					items.push($(this).val());
				});

				$items.attr('checked', null);

				client.ajax({
					url: 'client/' + action + '/' + items.join('-')
					, success: function (data) {
						client.status_bind(data);
						self.fn.player_inventory();
					}
				});
			};

			// change ammo
			self.viewModels.playerInventory.fn.change_ammo = function () {
				client.ajax({
					url: 'client/loadweapon/' + this.instance + '/'
						+ $('#ammo-' + this.instance).val()
					, success: function (data) {
						client.status_bind(data);
						self.fn.player_inventory();
					}
				});
			};

			//// init

			// automagically create stat properties for player viewModel
			for (var i in obj.stat) {
				if (! obj.stat.hasOwnProperty(i)) continue;
				self.stat[ i ] = ko.observable(obj.stat[ i ]);
			}
		};
	}
);
