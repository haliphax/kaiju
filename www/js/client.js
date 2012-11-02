// constants
var kaijuTimerDelay = 8000;
// globals
var kaijuTimer;
var transCount = 0;
var fetching = false;
var scriptsToLoad = [];
var loadedScripts = [];

function ajaxResponse(ret)
{
	clearTimeout(kaijuTimer);
	kaijuTimer = setTimeout(getStatus, kaijuTimerDelay);
	
	if(ret == null)
		return;
	if(ret.msg)
		for(var i in ret.msg)
			addToLog(ret.msg[i]);

	if(ret.maint)
	{
		window.location = kaiju_globals.base_url + 'login';
		return;
	}			

	if(ret.cells) $('#map').html(buildMap(ret.cells, 1));
	var blankme = false;
	if(typeof ret.stat == 'undefined') return;
	var stat = ret.stat;
	var effHtml = '';
	var occHtml = '';
	var surrHtml = '';
	var actHtml = '';
	var skillsHtml = '';
	
	if(stat.stat_hp)
	{
		if(stat.stat_hp <= 0)
		{
			$('#map').html('');
			$('#occupants .block').html('');
			$('#surroundings .block').html('');
			$('#effects').html('');
			$('.dialog').dialog('close');
			
			if(ret.actd)
			{
				if(actHtml) actHtml += ', ';
				actHtml += actionHelper('dead', ret.actd);
				$('#actions .block').html(actHtml);
			}
			
			addToLog([false, "You are <b>dead</b>."]);
			clearTimeout(kaijuTimer);
			blankme = true;
		}
		
		$('#bar_hp span.prog')
			.html(stat.stat_hp + '/' + stat.stat_hpmax);
		$('#bar_hp').progressbar('option', 'value',
			stat.stat_hp / stat.stat_hpmax * 100);
	}
	
	if(stat.stat_xp)
	{
		$('#bar_xp span.prog')
			.html(stat.stat_xp + '/' + stat.stat_xplevel);
		$('#bar_xp').progressbar('option', 'value',
			stat.stat_xp / stat.stat_xplevel * 100);
	}
	
	if(stat.stat_ap)
	{
		if(stat.stat_ap <= 0)
		{
			$('#map').html('');
			$('#occupants div').html('');
			$('#surroundings .block').html('');
			$('#actions .block').html('');
			$('.dialog').dialog('close');
			if(! blankme)
				addToLog([false, "You are <b>exhausted</b>."]);
			clearTimeout(kaijuTimer);
			blankme = true;
		}
		
		$('#bar_ap span.prog')
			.html(stat.stat_ap + '/' + stat.stat_apmax);
		$('#bar_ap').progressbar('option', 'value',
			stat.stat_ap / stat.stat_apmax * 100);
	}
	
	if(stat.stat_mp)
	{
		$('#bar_mp span.prog')
			.html(stat.stat_mp + '/' + stat.stat_mpmax);
		$('#bar_mp').progressbar('option', 'value',
			stat.stat_mp / stat.stat_mpmax * 100);
	}
	
	for(a in ret.effects)
	{
		if(a > 0) effHtml += ', ';
		effHtml += '<a href="#" class="nowrap" onclick="return describeEffect(\''
			+ ret.effects[a].effect + '\',\'' + ret.effects[a].ename
			+ '\')">' + ret.effects[a].ename
			+ '</a>';
	}
	
	if(blankme) return;
	var dojs = false;
	
	if(ret.actg)
	{
		dojs = true;
		if(actHtml) actHtml += ', ';
		actHtml += actionHelper('global', ret.actg);
	}
	
	if(ret.actb)
	{
		dojs = true;
		if(actHtml) actHtml += ', ';
		actHtml += actionHelper('building', ret.actb);
	}
	
	if(ret.actc)
	{
		dojs = true;
		if(actHtml) actHtml += ', ';
		actHtml += actionHelper('cell', ret.actc);
	}
	
	if(ret.skills)
	{
		dojs = true;
		
		for(r in ret.skills)
		{
			var cost = false;
			if(ret.skills[r].cost_ap > 1
				|| ret.skills[r].cost_mp > 1)
				cost = true;
			var both = false;
			if(ret.skills[r].cost_ap > 1
				&& ret.skills[r].cost_mp > 1)
				both = true;
			if(skillsHtml) skillsHtml += ', ';
			skillsHtml += '<div class="inline nowrap"><a href="#" id="a_' + ret.skills[r].abbrev + '" onclick="';

			if(ret.skills[r].js == 1) 
			{
				skillsHtml += ret.skills[r].abbrev + '();" ';

				if(! loadedScripts[ret.skills[r].abbrev])
				{
					skillsHtml += 'class="a-loading" ';
					scriptsToLoad[ret.skills[r].abbrev] = 'skills/' + ret.skills[r].abbrev;
				}
			}
			else
			{
				if(ret.skills[r].params != 0)
					skillsHtml += 'getSkillParams('
						+ (ret.skills[r].rpt == 1 ? 'true' : 'false')
						+ ',';
				else
					skillsHtml += 'useSkill(';
				skillsHtml += ret.skills[r].skill + ',false,'
					+ (ret.skills[r].params == 0 ? 'false' : 'true')
					+ ');"';
			}
			
			skillsHtml += '>' + ret.skills[r].sname + '</a>';
			if (ret.skills[r].rpt == 1 && ret.skills[r].params != 1)
				skillsHtml += ' <a href="#" onclick="repeatSkill(\''
					+ ret.skills[r].skill + '\');">x5</a>';
			skillsHtml += (cost ? ' <span class="cost">('
					+ (ret.skills[r].cost_ap > 1
					? ret.skills[r].cost_ap + (both ? '/' : '') : '')
					+ (ret.skills[r].cost_mp > 1
					? ret.skills[r].cost_mp + 'm' : '') + ')</span>'
					: '')
					+ '</div>';
		}
	}

	if(ret.info)
	{
		$('#coord_x').html(ret.info.x);
		$('#coord_y').html(ret.info.y);
		$('#coord_desc').html(ret.info.descr);
		if(ret.info.town)
			$('#coord_desc').append('<br /><i>Town: ' + ret.info.town
				+ '</i>');
	}
	else
	{
		$('#coord_x, #coord_y').html('0');
		$('#coord_desc').html('Unknown');
	}
	
	if(ret.occ)
	{
		var occ = ret.occ;
		var first = [true, true];
		var b = occ.length;
		var och = ['', ''];
		
		for(a = 0; a < b; a++)
		{
			if(occ[a].actor != stat.actor)
			{
				if(! first[occ[a].elev])
					och[occ[a].elev] += ', ';
				else
					och[occ[a].elev] = (occ[a].elev != ret.elev
						? '<b>' + (ret.elev ? 'Below' : 'Above')
						+ ': </b>' : '');
				och[occ[a].elev] +=
					'<a href="#" onclick="actorMenu(' + occ[a].actor
					+ ',1);"' + (occ[a].ally ? 'class="allyname"'
					: (occ[a].enemy ? 'class="enemyname"' :
					(occ[a].faction != ret.stat.faction
					? 'class="badguy"' : 'class="goodguy"')))
					+ '>' + occ[a].aname + '</a>';
				first[occ[a].elev] = false;
			}
		}
		
		var other = Math.abs(ret.elev - 1);
		occHtml = och[ret.elev] + (och[other] != '' ? '<p />' : '')
			+ och[other];
	}
	
	if(ret.corpses)
		if(ret.corpses == 1)
			surrHtml +=
				'There is a <b>corpse</b> here.&nbsp;';
		else if(ret.corpses > 1)
			surrHtml += 'There are <b>' + ret.corpses
				+ ' corpses</b> on the ground.&nbsp;';
	if(ret.surr) surrHtml += ret.surr;
	if(! effHtml) effHtml = '<i>None</i>';
	$('#effects').html('<b>Effects:</b>&nbsp;' + effHtml);
	$('#occupants .block').html(occHtml);
	$('#occupants').accordion('resize')
	$('#surroundings .block').html(surrHtml);
	$('#surroundings').accordion('resize');
	$('#actions .block').html(actHtml);
	$('#actions').accordion('resize');
	$('#skills .block').html(skillsHtml);
	$('#skills').accordion('resize');
	
	if(dojs)
	{
		for(a in scriptsToLoad)
		{
			if(loadedScripts[a]) continue;
			var newscr = document.createElement('script');
			newscr.type = 'text/javascript';
			newscr.async = true;
			newscr.src = kaiju_globals.base_url_path + 'js/' + scriptsToLoad[a] + '.js';
			$('body').append(newscr);
			loadedScripts[a] = 1;
		}
	}
}

// action helper
function actionHelper(atype, acts)
{
	var html = '';
	
	for(r in acts)
	{
		// show exit/enter actions on map, as well
		if(acts[r].descr === 'Exit' || acts[r].descr === 'Enter')
		{
			$('#current_cell')
				.css('cursor', 'pointer')
				.attr('title', acts[r].descr)
				.data('e', acts[r].abbrev)
				.hover(showDoor, hideDoor)
				.unbind('click')
				.click(function(e)
				{
					useAction('global', $(this).data('e'));
					e.preventDefault();
					e.stopPropagation();
					return false;
				});
		}
		
		if(html) html += ', ';
		html += '<div class="inline nowrap">';
		
		if(acts[r].js == 1)
		{
			if(! loadedScripts[acts[r].abbrev])
				scriptsToLoad[acts[r].abbrev] = 'actions/' + acts[r].atype + '/'
					+ acts[r].abbrev;
			html += '<a href="#" onclick="'
				+ acts[r].abbrev + '();" id="a_' + acts[r].abbrev + '"'
				+ (loadedScripts[acts[r].abbrev] ? '' : ' class="a-loading"')
				+ '>';
		}
		else
		{
			if(acts[r].params == 1)
				html +=
					'<a href="#" onclick="getActionParams('
					+ (acts[r].rpt == 1 ? 'true' : 'false') + ',\'' + atype
					+ '\',\'';
			else
				html +=
					'<a href="#" onclick="useAction(\'' + atype + '\',\'';
			html += acts[r].abbrev + '\');">';
		}
		
		html += acts[r].descr + '</a>';
		
		if(acts[r].rpt == 1 && acts[r].params != 1)
			html += ' <a href="#" onclick="repeatAction(\'' + atype + '\',\''
				+ acts[r].abbrev + '\');">x5</a>';
		html += (acts[r].cost > 1 ? ' <span class="cost">(' + acts[r].cost
			+ ')</span>' : '') + '</div>';
	}

	return html;
}

// increment transmit counter
function incTrans()
{
	if(++transCount > 0) $('#trans_icon').addClass('ui-state-error');
}

// decrement transmit counter
function decTrans()
{
	if(--transCount <= 0) $('#trans_icon').removeClass('ui-state-error');
}

// ajax error
function ajaxError(e)
{
	if(e.status != 200 && e.status > 0)
	{
		switch(e.status)
		{
			case 404:
				alert('Error [404]: Page not found');
				break;
			default:
				alert('Error [' + e.status + ']:\n\n' + e.responseText);
				break;
		}
	}
}

// submit chat text/command
function chatSubmit(whisper)
{
	incTrans();
	var chatText = (typeof whisper == 'undefined' ?
		$('#chat_input').val()
		: '/w ' + $('#whisper_opts').data('a') + ' ' + $('#whisper_txt').val());
	$('#chat_input').val('');
	if(chatText.replace(' ', '').length == 0) return false;

	$.ajax(
	{
		type: 'POST',
		url: kaiju_globals.base_url + 'client/chat/?1&2',
		data: { text: chatText },
		dataType: 'json',
		async: true,		
		success: function(ret)
		{
			ajaxResponse(ret);
		},
		error: ajaxError,
		complete: decTrans
	});

	return false;
}

function minimap()
{
	$('#minimap_img').attr('src', kaiju_globals.base_url + 'client/minimap/?1&2&'
		+ (new Date()).getTime());
	$('#minimap').dialog('option', 'width', 17 * 12 + 24).dialog('open');
}

// build map from json data
function buildMap(cells, main)
{
	if(typeof main == 'undefined') main = false;
	var html = '';
	
	for(a = 0; a < cells.length; a++)
	{
		var ptrid = false;
			
		if(main && cells[a].x != 0)
			if(a == 6) ptrid = 'nw';
			else if(a == 7) ptrid = 'n';
			else if(a == 8) ptrid = 'ne';
			else if(a == 11) ptrid = 'w';
			else if(a == 13) ptrid = 'e';
			else if(a == 16) ptrid = 'sw';
			else if(a == 17) ptrid = 's';
			else if(a == 18) ptrid = 'se';
		
		html += '<div class="map_cell tile';
		
		if(cells[a].img)
		{
			html += ' tile-' + cells[a].img.replace(/\..+$/, '');
		}
		else
		{
			html += ' tile-blank';
		}
		
		html += '" title="' + cells[a].descr + '">';
		if(cells[a].x != 0)
			html +=
				'<div class="map_cell '
				+ (cells[a].w
					? 'walls walls-' + cells[a].w
					: 'tile tile-box')
				+ '"'
				+ (ptrid
					? ' onmouseover="showArrow(this)"'
						+ ' onmouseout="hideArrow(this)"'
						+ ' onclick="moveto(' + cells[a].x + ','
						+ cells[a].y + ')"'
					: (main && a == 12 ? ' id="current_cell"' : ''))
				+ ' style="' + (ptrid ? 'cursor:pointer;' : '') + '">'
				+ (cells[a].clan ? '<div class="sh">' : '');
		if((ptrid || a == 12) && main && cells[a].x != 0)
			html += '<div class="mvarrow" navdir="' + ptrid + '">';
		occ = cells[a].occ;
		
		if((main && a == 12) || (! main && a == 40))
		{
			occ--;
			html +=
				'<img src="' + kaiju_globals.base_url_path + 'images/pawns/person.png" style="position:relative;top:15px;" />';
		}
		
		if(occ > 0)
		{
			html +=
				'<img src="' + kaiju_globals.base_url_path + 'images/pawns/other.png" style="position:relative;top:15px;" />';
			if(occ > 1)
				html += '<span class="occ">&nbsp;' + occ + '&nbsp;</span>';
		}
		
		if(cells[a].x != 0)
		{
			if((ptrid || a == 12) && main) html += '</div>';
			if(cells[a].clan) html += '</div>';
			html += '</div>';
		}			
		
		html += '</div>';
	}
	
	return html;
}

// get actor profile
function actorMenu(actor, descr)
{
	// TODO make context sensitive to barriers
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/actor/' + actor + (descr == true ? '/1' : '') + '/?1',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			$('#profile').data('actor', actor);
			actHtml = '';
			var fac = 'none';
			if(ret.faction)
				fac = '<a href="#">' + ret.faction_name + '</a>';
			$('#profile_faction').html(fac);
			var clan = 'none';
			if(ret.clan)
				clan = '<a href="#" onclick="clan_info_dialog(' + ret.clan
					+ ')">' + ret.clan_name + '</a>'
					+ (ret.rel ? ' <small><i>' + ret.rel + '</i></small>' : '');
			$('#profile_clan').html(clan);
			
			if(ret.attack)
			{
				actHtml +=
					'<div class="skillbutton"><button class="button ui-state-error ui-corner-all" onclick="attack('
						+ actor + ');">Attack <small>' + ret.cth
						+ '%</small></button></div>';
				if(actor > 0)
					if(ret.dist == 0)
						$('#profile_distance').html('Melee');
					else
						$('#profile_distance').html('Ranged');
			}
			
			if(ret.skills && ret.skills.length > 0)
			{
				for(r in ret.skills)
				{
					var cost = false;
					if(ret.skills[r].cost_ap > 1
						|| ret.skills[r].cost_mp > 1)
						cost = true;
					var both = false;
					if(ret.skills[r].cost_ap > 1
						&& ret.skills[r].cost_mp > 1)
						both = true;
					actHtml +=
						'<div class="skillbutton inline nowrap"><button class="button ui-state-default ui-corner-all" onclick="useSkill(\''
						+ ret.skills[r].abbrev + '\',' + actor + ','
						+ (ret.skills[r].params == 0 ? 'false' : 'true')
						+ ');">' + ret.skills[r].sname + (cost ? ' <small>('
						+ (ret.skills[r].cost_ap > 1 ? ret.skills[r].cost_ap
						: '') + (both ? '/' : '')
						+ (ret.skills[r].cost_mp > 1 ? ret.skills[r].cost_mp
						+ 'm' : '') + ')</small>' : '') + '</button>';
						
					if(ret.skills[r].params != 0)
					{
						actHtml += '&nbsp;<select id="' + ret.skills[r].abbrev
						+ '_params">';
						for(p in ret.skills[r].params)
							actHtml += '<option'
								+ ($('#profile').data(ret.skills[r].abbrev)
									== ret.skills[r].params[p][0]
									? ' selected="selected"' : '')
								+ ' value="' + ret.skills[r].params[p][0] + '">'
								+ ret.skills[r].params[p][1] + '</option>';
						actHtml += '</select>&nbsp;';
					}
					
					actHtml += '</div>';
				}
			}
			
			if(ret.acta && ret.acta.length > 0)
			{
				for(r in ret.acta)
				{
					if(ret.acta[r].js == 1
						&& ! loadedScripts[ret.acta[r].abbrev])
					{
						scriptsToLoad[ret.acta[r].abbrev] = 'actions/'
							+ ret.acta[r].abbrev;
					}
					
					actHtml +=
						'<div class="skillbutton inline nowrap"><button class="button ui-state-default ui-corner-all" onclick="'
						+ (ret.acta[r].js == 1
							? ret.acta[r].abbrev + '(' + actor
							: 'useAction(\'actor\',\'' + ret.acta[r].abbrev
								+ '\',' + actor + ','
								+ (ret.acta[r].params != 0 ? 'true' : ''))
						+ ');">' + ret.acta[r].descr
						+ (ret.acta[r].cost > 1 ?
							' <small>(' + ret.acta[r].cost + ')</small>'
							: '') + '</button>';
						
					if(ret.acta[r].params != 0)
					{
						actHtml += '&nbsp;<select id="' + ret.acta[r].abbrev
							+ '_params">';
						for(p in ret.acta[r].params)
							actHtml += '<option'
								+ ($('#profile').data(ret.acta[r].abbrev)
									== ret.acta[r].params[p][0]
									? ' selected="selected"' : '')
								+ ' value="' + ret.acta[r].params[p][0] + '">'
								+ ret.acta[r].params[p][1] + '</option>';
						actHtml += '</select>&nbsp;';
					}
					
					actHtml += '</div> ';
				}
			}
			
			if(actor > 0)
			{
				$('#profile .stat, #profile_descr, #profile_spacer').show();
				if(! ret.status) return;
				
				switch(ret.status)
				{
					case 2:
					{
						ret.status = '<span style="color:yellow;">Online</span>';
						break;
					}
					
					case 1:
					{
						ret.status = 'Offline';
						break;
					}
				}
				
				$('#profile_status').html(ret.status);
				
				if(! ret.npc)
					actHtml +=
						' <div class="skillbutton inline nowrap"><button class="button ui-state-default ui-corner-all" onclick=\'whisper("'
						+ ret.aname + '")\'>Whisper</button></div>';			
				$('#profile_health').html(ret.health);
				
				if(descr == true)
					$.ajax({
						type: 'GET',
						url: kaiju_globals.base_url + 'client/describe/actor/' + actor + '/?1',
						dataType: 'json',
						async: true,
						
						success: function(rret)
						{
							ajaxResponse(rret);
							var str = '<b>Equipped:</b> ';
							
							for(r in rret.descr)
							{
								if(r > 0) str += ', ';
								str += rret.descr[r].iname;
							}
							
							if(rret.descr.length == 0) str += 'Nothing.';
							$('#profile_descr').html(str);
							$('#profile')
								.dialog('destroy')
								.dialog({ title: rret.aname, width: 400 });
						}
					});
			}
			else
			{
				$('#profile .stat, #profile_descr, #profile_spacer').hide();
				if(descr == true)
					$('#profile')
						.dialog('destroy')
						.dialog({ title: 'Attack', width: 400 });
			}
			
			$('#profile_actions').html(actHtml);
		},
		error: ajaxError,
		complete: decTrans
	});
}

// whisper to somebody
function whisper(actor)
{
	if(typeof actor != 'undefined')
	{
		$('#whisper_opts')
			.data('a', '"' + actor + '"')
			.attr('title', 'Whisper to ' + actor)
			.dialog('open');
		$('#whisper_txt').focus();
		return;
	}

	chatSubmit(true);
	$('#whisper_txt').val('').focus();
}

// attack somebody
function attack(actor)
{
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/attack/' + actor + '/?1',
		dataType: 'json',
		async: false,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			actorMenu(actor);
		},
		error: ajaxError,
		complete: decTrans
	});
}

// perform inventory action
function invAction(action, items)
{
	if((action == 'drop' || action == 'dropstack')
		&& ! confirm('Are you sure?'))
	{
		$('#inv_multi > option:first').attr('selected', true);
		return false;
	}
	
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/' + action + '/' + items + '/?1',
		dataType: 'json',
		async: false,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			$($('#inv_multi > option')[0]).attr('selected', true);
			
			try {
				if(action == 'item' && items.indexOf('/') > -1)
					actorMenu($('#profile').data('actor'));
				else
					getInventory();
			} catch(e) {
				getInventory();
			}
		},
		error: ajaxError,
		complete: decTrans
	});
}

// move to given cell
function moveto(x, y)
{
	incTrans();
	$.ajax(
	{
		type: 'GET',
		url: kaiju_globals.base_url + 'client/move/' + x + '/' + y + '/?1',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			$('#profile').dialog('close');
		},
		error: ajaxError,
		complete: decTrans
	});
}

// check for status/map update
function getStatus(force, forcemap)
{
	if(force != true && fetching == true) return;
	clearTimeout(kaijuTimer);
	fetching = true;
	var url = kaiju_globals.base_url + 'client/status/' + (force === true ? '1' : '0') + '/'
		+ (forcemap === true ? '1' : '0') + '/?1';
	incTrans();
	$.ajax(
	{
		type: 'GET',
		url: url,
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
		},
		error: ajaxError,
		complete: function()
		{
			decTrans();
			fetching = false;
		}
	});
}

// add text to log panel
function addToLog(txt)
{
	var logPanel = $('#log_text')[0];
	var logHeight = $(logPanel).height();
	var scrollLock = false;
	var oldTop = logPanel.scrollTop;
	if(oldTop < logPanel.scrollHeight - logHeight - 12)
	    scrollLock = true;
	
	if(txt[1] == $('#log_text').data('last'))
	{
		$('#log_text')
			.data('count', $('#log_text').data('count') + 1)
			.find('li:last')
			.append(' (' + txt[0] + ') ...and again.');
	}
	else
		$('#log_text').data('count', 0).append('<li>'
			+ (txt[0] === false ? '<p>' : '<span class="stamp">['
				+ txt[0] + ']</span>')
			+ ' ' + txt[1] + '</li>');

	$('#log_text').data('last', txt[1]);
	// auto scroll if not locked
	if(! scrollLock)
	    logPanel.scrollTop = logPanel.scrollHeight - logHeight;
	else
	    logPanel.scrollTop = oldTop;
}

// use a skill
function useSkill(skill, actor, params)
{
	var p = false;
	
	if(typeof actor == 'undefined' || actor == false)
	{
		actor = false;
		
		if(params) {
			p = $('#skillparams select').val();
			if(p) $('#skillparams').data('last', p);
		}
	}
	else if(params)
	{
		p = $('#' + skill + '_params').val();
		if(p) $('#profile').data(skill, p);
	}
	
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/skill/' + skill + (actor ? '/' + actor : '')
			+ (p == false ? '' : '/' + p) + '/?1',
		dataType: 'json',
		
		async: true,
		success: function(ret)
		{
			ajaxResponse(ret);
			if(actor)
				actorMenu(actor);
		},
		error: ajaxError,
		complete: decTrans		
	});
}

// get actor's inventory
function getInventory()
{
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/inventory/?1',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			var checkAmmo = [];
			$('.inv_remove').remove();
			var html = '';
			var odd = 0;
			$('#inventory_enc').html(ret.enc);
			
			for(r in ret.inv)
			{
				html += '<tr class="inv_remove '
					+ (odd ? 'ui-state-highlight' : '')
					+ '"><td style="width:1%"><input type="checkbox" value="'
					+ ret.inv[r].instance + '" class="inv_chk" /></td><td>'
					+ '<a href="#" onclick="return describeItem('
					+ ret.inv[r].instance + ');">' + ret.inv[r].iname + '</a>';
				if(ret.inv[r].eq_slot != null)
					html += ' <b>(' + ret.inv[r].eq_slot + ')</b>';
				else if(ret.inv[r].eq_type != null)
					html += ' <small>(' + ret.inv[r].eq_type + ')</small>';
				if(ret.inv[r].num > 1)
					html += ' [' + ret.inv[r].num + '] ';
				
				if(ret.inv[r].ammo)
				{
					var split = ret.inv[r].ammo.split('&');
					var i = split[0];
					var ammo = split[1];
					html += ' <select onchange="changeAmmo('
						+ ret.inv[r].instance + ');" id="ammo_'
						+ ret.inv[r].instance + '"><option value="'
						+ (i ? i : '') + '">'
						+ (ammo ? ammo : 'Choose ammo:')
						+ '</option></select>';
					checkAmmo.push(ret.inv[r].inum);
				}
				
				html += '</td><td>' + (ret.inv[r].weight * ret.inv[r].num)
					+ '</td><td style="text-align:right;white-space:nowrap;">';
				buttons = '';
				if(ret.inv[r].eq_type != null)
					if(ret.inv[r].eq_slot != null)
						buttons += '<button onclick="invAction(\'remove\', '
							+ ret.inv[r].instance
							+ ');" class="button ui-state-default ui-corner-all">Remove</button>';
					else
						buttons += '<button onclick="invAction(\'equip\', '
							+ ret.inv[r].instance
							+ ');" class="button ui-state-default ui-corner-all">Equip</button>';
				if(ret.inv[r].target !== null && ret.inv[r].target < 2)
					buttons += (buttons != '' ? '&nbsp;' : '')
						+ '<button onclick="invAction(\'item\', '
						+ ret.inv[r].instance
						+ ');" class="button ui-state-default ui-corner-all">Use</button>';
				if(ret.inv[r].eq_slot == null)
					buttons += (buttons != '' ? '&nbsp;' : '')
						+ '<button onclick="invAction(\'drop\','
						+ ret.inv[r].instance
						+ ');" class="button ui-state-default ui-corner-all">Drop</button>';
				if(buttons == '')
					html += '&nbsp;';
				else
					html += buttons;
				html += '</td></tr>';
				odd = (odd == 1 ? 0 : 1);
			}
			
			$('#inv_header').after(html);
			if(! $('#inventory').data('open'))
				$('#inventory').dialog('open');
			
			// get ammo options
			if(checkAmmo.length > 0)
			{
				incTrans();
				$.ajax({
					type: 'GET',
					url: kaiju_globals.base_url + 'client/ammo/' + checkAmmo.join(',') + '/?1',
					dataType: 'json',
					async: true,
					
					success: function(rret)
					{
						ajaxResponse(rret);
						
						for(a in rret.ammo)
						{
							var html = $('#ammo_' + rret.ammo[a].instance).html();
							var cur = $('#ammo_' + rret.ammo[a].instance).val();
							for(b = 0, bx = rret.ammo[a].opts.length; b < bx; b++)
								if(rret.ammo[a].opts[b].inum != cur)
									html += '<option value="'
											+ rret.ammo[a].opts[b].inum + '">'
											+ rret.ammo[a].opts[b].iname
											+ '</option>';
							$('#ammo_' + rret.ammo[a].instance).html(html);
						}
					},
					complete: decTrans
				});
			}
		},
		error: ajaxError,
		complete: decTrans
	});
}

// change ammo for weapon
function changeAmmo(weapon)
{
	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/loadweapon/' + weapon + '/'
			+ $('#ammo_' + weapon + ' option:selected').val() + '/?1',
		type: 'GET',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			if(ret.success)
				$('#inventory').profile();
		},
		complete: decTrans
	});
}

// describe an effect
function describeEffect(effect)
{
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/describe/effect/' + effect + '/?1',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			$('#effect_desc').html(ret.eff.descr);
			$('#effect_desc')
				.dialog('option', 'title', ret.eff.ename)
				.dialog('open');
		},
		complete: decTrans
	});
}

// describe an item
function describeItem(item)
{
	incTrans();
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'client/describe/item/' + item + '/?1',
		dataType: 'json',
		async: true,
		
		success: function(ret)
		{
			ajaxResponse(ret);
			// item description
			$('#item_desc_main').html(
				(ret.descr.img != '' ?
				'<img style="float:left;margin:8px;" src="' + kaiju_globals.base_url_path + 'images/items/'
					+ ret.descr.img + '" />'
					: '')
				+ ret.descr.txt + '<p />');
			$('#item_desc_armor, #item_desc_weapon, #item_desc_ammo').html('');
			var classes = '';
			for(c in ret.descr.iclass)
				classes += (c > 0 ? ', ' : '') + ret.descr.iclass[c].descr;
			if(! classes) classes = 'None.';
			$('#item_desc_classes').html('<b>Item Class:</b> ' + classes);
			var metrics = (ret.descr.durability ?
				'<tr><td class="tright"><b>Durability:</b></td><td>'
				+ ret.descr.durability + (ret.descr.durmax > 0
				? '/' + ret.descr.durmax : '') + '</td></tr>'
				: '');
			
			// weapon
			if(ret.descr.weapon)
			{
				var html =
					'<table><tr><td class="tright"><b>Weapon Type:</b></td><td>'
					+ ret.descr.eq_type + '</td></tr>'
					+ '<tr><td class="tright"><b>Weapon Distance:</b></td><td>'
					+ ret.descr.weapon.distance + '</td></tr>'
					+ '<tr><td class="tright"><b>Damage Type:</b></td><td>'
					+ ret.descr.weapon.dmg_type + '</td></tr>'
					+ '<tr><td class="tright"><b>Min. Damage:</b></td><td>'
					+ ret.descr.weapon.dmg_min + '</td></tr>'
					+ '<tr><td class="tright"><b>Max. Damage:</b></td><td>'
					+ ret.descr.weapon.dmg_max + '</td></tr>'
					+ '<tr><td class="tright"><b>Crit. Bonus:</b></td><td>'
					+ ret.descr.weapon.dmg_bonus + '</td></tr>'
					+ metrics + '</table>';
				metrics = '';
				$('#item_desc_weapon').html(html);
			}
			
			// armor
			if(ret.descr.armor)
			{
				var html =
					'<table><tr><td class="tright"><b>Armor Slot:</b></td><td>'
					+ ret.descr.eq_type + '</td></tr>'
					+ '<tr><td class="tright"><b>Armor Class:</b></td><td>'
					+ ret.descr.armor.iclass + '</td></tr>'
					+ '<tr><td class="tright"><b>Def. vs Slashing:</b></td><td>'
					+ ret.descr.armor.slashing + '</td></tr>'
					+ '<tr><td class="tright"><b>Def. vs. Piercing:</b></td><td>'
					+ ret.descr.armor.piercing + '</td></tr>'
					+ '<tr><td class="tright"><b>Def. vs Blunt:</b></td><td>'
					+ ret.descr.armor.blunt + '</td></tr>'
					+ metrics + '</table>';
				metrics = '';
				$('#item_desc_armor').html(html);
			}
			
			// ammo
			if(ret.descr.ammo)
			{
				var html =
					'<table><tr><td class="tright"><b>Ammo Dmg Bonus:</b></td><td>'
					+ ret.descr.ammo.dmg + '</td></tr>' + metrics + '</table>';
				metrics = '';
				$('#item_desc_ammo').html(html);
			}
			
			$('#item_desc')
				.dialog('destroy')
				.dialog({
					title: ret.descr.iname,
					position: 'center',
					width: 500
				});
		},
		error: ajaxError,
		complete: decTrans
	});
}

// get an active, non-targeted skill's parameters
function getSkillParams(rpt, skill)
{
	incTrans();
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: kaiju_globals.base_url + 'client/skillparams/' + skill + '/?1',
		async: true,		
		success: function(ret)
		{
			ajaxResponse(ret);
			var html = '';
			for(r in ret.params)
				html += '<option value="' + ret.params[r][0] + '"'
					+ (ret.params[r][0] == $('#skillparams').data('last')
						? ' selected="selected"' : '')
					+ '>' + ret.params[r][1] + '</option>';
			$('#skillparams select').html(html);
			if(rpt)
				$('#btn_skillparam_rpt').show();
			else
				$('#btn_skillparam_rpt').hide();
			$('#skillparams')
				.data('skill', skill)
				.data('rpt', rpt)
				.dialog('option', 'title', ret.sname)
				.dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
}

// get an action's parameters
function getActionParams(rpt, atype, action)
{
	incTrans();
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: kaiju_globals.base_url + 'client/actparams/' + atype + '/' + action + '/?1',
		async: true,
		success: function(ret)
		{
			ajaxResponse(ret);
			var html = '';
			for(r in ret.params)
				html += '<option value="' + ret.params[r][0] + '"'
					+ (ret.params[r][0] == $('#actparams').data('last')
						? ' selected="selected"' : '')
					+ '>' + ret.params[r][1] + '</option>';
			$('#actparams select').html(html);
			if(rpt)
				$('#btn_actparam_rpt').show();
			else
				$('#btn_actparam_rpt').hide();
			$('#actparams')
				.data('atype', atype)
				.data('act', action)
				.data('rpt', rpt)
				.dialog('option', 'title', ret.actname)
				.dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
}

function showDoor()
{
	$('#current_cell').find('.mvarrow')
		.addClass('ui-sprite')
		.addClass('door-icon');
}

function hideDoor(el)
{
	$('#current_cell .mvarrow')
		.removeClass('ui-sprite')
		.removeClass('door-icon');
}
		
function showArrow(el)
{
	var arrow = $(el).find('.mvarrow');
	var img = $(arrow).attr('navdir');
	$(arrow).addClass('ui-sprite').addClass('move-' + img);
}

function hideArrow(el)
{
	var arrow = $(el).find('.mvarrow');
	var img = $(arrow).attr('navdir');
	$(el).find('.mvarrow').removeClass('ui-sprite').removeClass('move-' + img);
}

function useAction(atype, action, actor, params)
{
	var p = false;
	
	if(typeof actor == 'undefined' || actor == false)
	{
		actor = false;
		
		if(params)
		{
			p = $('#actparams select').val();
			if(p) $('#actparams').data('last', p);
		}
	}
	else if(params)
	{
		p = $('#' + action + '_params').val();
		if(p) $('#profile').data(action, p);
	}

	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/action/' + atype + '/' + action  
			+ (actor ? '/' + actor : '') + (p ? '/' + p : '') + '/?1',
		async: true,		
		type: 'GET',
		dataType: 'json',
		success: function(ret)
		{
			ajaxResponse(ret);
			if(actor && p) actorMenu(actor);
			else if(params)
				getActionParams($('#actparams').data('rpt'), atype, action);
		},
		error: ajaxError,
		complete: decTrans
	});
}

function repeatAction(atype, action)
{
	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/repeat/action/' + atype + '/' + action + '/?1',
		async: true,
		type: 'GET',
		dataType: 'json',
		success: function(ret)
		{
			ajaxResponse(ret);
		},
		error: ajaxError,
		complete: decTrans
	});
}

function repeatSkill(skill)
{
	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/repeat/skill/' + skill + '/?1',
		async: true,
		type: 'GET',
		dataType: 'json',
		success: function(ret)
		{
			ajaxResponse(ret);
		},
		error: ajaxError,
		complete: decTrans
	});
}

// doc.ready
$(function()
{
	$.ajaxSetup({ cache: false });
	// prevent highlighting from dragging mouse
	$('.map_cell').live('mousedown', function(e) { e.preventDefault(); });
	// stop mouseover propagation
	$('.map_cell').live('mouseenter', function(e) { e.stopPropagation(); });
	
	// inventory multi-select
	$('#inv_multi').change(function()
	{
		if($(this).val() == '') return;
		var items = [];
		
		$('.inv_chk').each(function()
		{
			if($(this).attr('checked')) items.push($(this).val());
		});
		
		items = items.join('-');
		invAction($(this).val(), items);
	});
	
	$('#item_desc').dialog('option', 'width', 400);
	$('#inventory').dialog('option', 'width', 600);
	// load inventory on click
	$('#btn_inventory').click(getInventory);
	
	// wire up skill parameter button
	$('#btn_skillparam').click(function()
	{
		var skill = $('#skillparams').data('skill');
		var param = $('#skillparams select option:selected').val();
		
		incTrans();		
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'client/skill/' + skill + '/' + param + '/?1',
			dataType: 'json',
			async: true,			
			success: function(ret)
			{
				ajaxResponse(ret);
				$('#skillparams').data('last', param);
				getSkillParams($('#skillparams').data('rpt'), skill);
			},
			error: ajaxError,
			complete: decTrans
		});		
	});
	$('#btn_skillparam_rpt').click(function()
	{
		var skill = $('#skillparams').data('skill');
		var param = $('#skillparams select option:selected').val();
		
		incTrans();
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'client/repeat/skill/' + skill + '/' + param + '/?1',
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				ajaxResponse(ret);
				$('#skillparams').data('last', param);
				getSkillParams(true, skill);
			},
			error: ajaxError,
			complete: decTrans
		});
	});
	
	// wire up action parameter button
	$('#btn_actparam').click(function()
	{
		var act = $('#actparams').data('act');
		var atype = $('#actparams').data('atype');
		useAction(atype, act, false, true);
	});
	
	// keep text as "Type chat text here" if blank
	$('#chat_input')
		//.hide()
		.data('clear', true)
		.change(function()
		{
			if($('#chat_input').val().length > 0)
				$('#chat_input').data('clear', false);
		})
		.focus(function()
		{
			if($('#chat_input').data('clear'))
			{
				$('#chat_input')
					.val('')
					.css('color', 'black')
					.data('clear', false);
			}
		})
		.blur(function()
		{
			if($('#chat_input').data('clear'))
			{
				$('#chat_input')
					.val('Type chat text or commands here')
					.css('color', 'gray');
			}
		});	

	// resize accordions when necessary
	$('.accordion').bind('accordionchange', function()
	{
		if($(this).find('.block').height() == 0) return;
		var div = $(this).find('div:first');
		var block = $(this).find('div.block');
		var h = $(div).height();
		if(! h) h = 0;
		var diff = $(block).height() - h;
		$(div).height(h + diff + 10);
	});
	// reset action params on close
	$('#actparams').bind('dialogclose', function()
	{
		$('#actparams select').html('');
	});
		
	// fix chat log unfolding bug
	$('#log').data('h', $('#log').height());
	$('#log_accordion').bind('accordionchange', function()
	{
		if($('#log_accordion').height() > 31)
			$('#log').animate({ height: $('#log').data('h') });
	});	
	
	// have dialogs flag themselves as open/closed
	$('.dialog')
		.bind('open', function()
		{
			$(this).data('open', true);
		})
		.bind('close', function()
		{
			$(this).data('open', false);
		});
	
	// do it!
	getStatus(true, true);
});
