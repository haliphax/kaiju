var dragcoords = false;
var dragstop = false;
var navint = 3;

// build map from json data
function buildMap(cells)
{
	var html = '';
	
	for(a = 0; a < cells.length; a++)
	{
		var b = cells[a].b;
		if(typeof b == 'undefined') b = 0;
		var t = cells[a].t;
		var im = '';
		if(typeof tiles[t] !== 'undefined') im = tiles[t].img;
		var d = cells[a].d;
		var descr = d;
		
		if(! d)
		{
			if(typeof tiles[cells[a].t] !== 'undefined')
				descr = tiles[cells[a].t].descr;
		}
		
		html +=
			'<div id="cell_' + cells[a].x + '_' + cells[a].y
			+ '" class="map_cell" title="' + descr + ' [' + cells[a].x + ','
			+ cells[a].y + ']' + (b > 0 ? ' (' + b + ')' : '')
			+ '" style="background-image:url(' + kaiju_globals.base_url + 'images/tiles/' + im
			+ ');" tile="' + t + '" bldg="' + b + '" descr="' + d + '">';
		if(cells[a].x > 0 && cells[a].y > 0)
			html +=
				'<img src="' + kaiju_globals.base_url + 'images/'
				+ (cells[a].w != '' ? 'walls/' + cells[a].w + '.png'
					: 'tiles/box.gif')
				+ '" />';
		html += '</div>';
	}
	
	return html;
}

// grab chunk of map
function getChunk(x, y)
{
	var rval = false;
	$.ajax({
		url: kaiju_globals.site_url + 'mapedit/chunk/' + x + '/' + y + '/',
		type: 'GET',
		dataType: 'json',
		async: false,
		success: function(ret)
		{
			$('#map').html(buildMap(ret));
			$('.map_cell').each(function()
			{
				$(this).data('img', $(this).find('img').attr('src'));
			});
			rval = true;
		}
	});	
	return rval;
}

// navigate
function nav(xdif, ydif)
{
	var x = Math.round($('body').data('x'));
	var y = Math.round($('body').data('y'));
	var newx = x + Math.round(xdif);
	var newy = y + Math.round(ydif);
	if(newx < 1) newx = 1;
	if(newy < 1) newy = 1;
	if(getChunk(newx, newy))
		$('body')
			.data('x', newx)
			.data('y', newy);
}

// change building #
function setBldg()
{
	var bldg = prompt('Enter the building number (or nothing to clear):');
	if(bldg === null) return;
	if(! bldg) bldg = 0;
	var sub = '';
	
	for(a = dragcoords[0]; a <= dragstop[0]; a++)
		for(b = dragcoords[1]; b <= dragstop[1]; b++)
		{
			if(a < 1 || b < 1) continue;
			var cur = $('#cell_' + a + '_' + b);
			var t = $(cur).attr('tile');
			var d = $(cur).attr('descr');
			sub += a + '_' + b + '_' + t + '_' + bldg
				+ (d ? '_' + d : '') + ',';
			$(cur)
				.attr('bldg', bldg)
				.attr('title', $(cur).attr('descr') + ' [' + a + ',' + b + ']');
			if(bldg != 0)
				$(cur).attr('title', $(cur).attr('title') + ' (' + bldg + ')');
		}
	
	modcells(sub);
	return false;
}

// edit associated building
function editBldg()
{
	var $b = $('body');
	var x, y;
	
	if(dragcoords.length == 2)
	{
		$b.data('cx', dragcoords[0]);
		$b.data('cy', dragcoords[1]);
	}

	x = $b.data('cx');
	y = $b.data('cy');
	
	$.ajax({
		type: 'GET',
		url: kaiju_globals.site_url + 'mapedit/get_building/' + x + '/' + y + '/',
		dataType: 'json',
		async: true,
		success: function(ret)
		{
			if(ret.error)
				return alert('There is no building here.');
			$('#building_classes').html('');			
			for(var i in ret.classes)
				$('#building_classes').append('<span>' + ret.classes[i].abbrev + ' <small><a href="#" onclick="return delete_class(' + ret.classes[i].bclass + ');">[del]</a></small>&nbsp;&nbsp;</span>');
			$('#building_dialog').dialog('open');
		},
		error: function(ret)
		{
			if(ret.length > 0)
				alert(ret);
			else
				alert('There was an error gathering building information.');
		}
	});
	return false;
}

function add_class()
{
	var $b = $('body');
	var bclass = $('#class').val();
	var x = $b.data('cx');
	var y = $b.data('cy');
	$.ajax({
		type: 'GET',
		url: kaiju_globals.site_url + 'mapedit/add_class/' + x + '/' + y + '/' + bclass + '/',
		dataType: 'json',
		async: true,
		success: editBldg,
		error: function(ret)
		{
			if(ret.length > 0)
				alert(ret);
			else
				alert('There was an error adding the building class.');
		}
	});
	return false;
}

function delete_class(bclass)
{
	var $b = $('body');
	var x = $b.data('cx');
	var y = $b.data('cy');
	$.ajax({
		type: 'GET',
		url: kaiju_globals.site_url + 'mapedit/remove_class/' + x + '/' + y + '/' + bclass + '/',
		dataType: 'json',
		async: true,
		success: editBldg,
		error: function(ret)
		{
			if(ret.length > 0)
				alert(ret);
			else
				alert('There was an error deleting the building class.');
		}
	});
	return false;
}

// change tile
function setTile(t)
{
	var bg = '';
	if(t > 0)
		bg = 'url(' + kaiju_globals.base_url + 'images/tiles/' + tiles[t].img + ')';
	var sub = '';
		
	for(a = dragcoords[0]; a <= dragstop[0]; a++)
		for(b = dragcoords[1]; b <= dragstop[1]; b++)
		{
			if(a < 1 || b < 1) continue;
			var bldg = $(cur).attr('bldg');
			if(typeof bldg == 'undefined') bldg = 0;
			var d = $(cur).attr('descr');
			sub += a + '_' + b + '_' + t + '_' + bldg
				+ (d ? '_' + d : '') + ',';
			var cur = $('#cell_' + a + '_' + b);
			$('#cell_' + a + '_' + b).attr('tile', t)
			$(cur)
				.attr('title', $(cur).attr('descr') + ' [' + a + ',' + b + ']')
				.css('background', bg);
			if(bldg != 0)
				$(cur).attr('title', $(cur).attr('title') + ' (' + bldg + ')');
		}
	
	modcells(sub);
	return false;
}

// modify cell(s)
function modcells(sub)
{
	$('body').css('cursor', 'hourglass');
	$.ajax({
		type: 'POST',
		data: { cells: sub },
		dataType: 'text',
		async: false,
		url: kaiju_globals.site_url + 'mapedit/modcells/',
		success: function(ret)
		{
			if(ret.length > 0) alert(ret);
		},
		error: function(ret)
		{
			if(ret.length > 0)
				alert(ret);
			else
				alert('There was an error submitting your changes.');
		}
	});
	$('body').css('cursor', 'default');
}

// document.ready
$(function()
{
	navint = $('#nav_int').val();
	var html = '<a href="#" onclick="return setBldg();">Set Building</a><span id="edit_bldg"><br /><a href="#" onclick="return editBldg();">Edit associated building</a></span><p />';
	for(t in tiles)
		html +=
			'<a href="#" onclick="return setTile(' + t + ');">' + tiles[t].descr
				+ '</a><br />';
	$('#chooser')
		.html(html)
		.click(function()
		{
			for(a = dragcoords[0]; a <= dragstop[0]; a++)
				for(b = dragcoords[1]; b <= dragstop[1]; b++)
					$('#cell_' + a + '_' + b).find('img')
						.attr('src', $('#cell_' + a + '_' + b).data('img'));
			$(this).hide();
			dragcoords = false;
			dragstop = false;
		});
	$('.map_cell')
		.live('click', function()
		{
			$('#edit_bldg').show();
			$(this)
				.find('img')
				.attr('src', $(this).data('img'));
			$('#chooser')
				.css('top', $(this).position().top)
				.css('left', $(this).position().left)
				.show();
		})
		.live('mousedown', function(e)
		{
			e.preventDefault();
			$(this)
				.find('img')
				.attr('src', kaiju_globals.base_url + 'images/tiles/box-hi.gif');
			$('#chooser').hide();
			dragcoords = $(this).attr('id').split('_');
			dragcoords = [dragcoords[1], dragcoords[2]];
			dragstop = dragcoords;
			$('#chooser').data('drag', true);
		})
		.live('mouseup', function()
		{
			$('#chooser').data('drag', false);
			
			if(dragcoords != dragstop)
			{
				$('#edit_bldg').hide();
				$('#chooser')
					.css('top', $(this).position().top)
					.css('left', $(this).position().left)
					.show();
			}
		})
		.live('mouseover', function()
		{
	 		if($('#chooser').data('drag') == true)
	 		{
				var newdrag = $(this).attr('id').split('_');
				newdrag = [newdrag[1], newdrag[2]];
				if(newdrag[0] < dragstop[0])
					for(a = dragcoords[1]; a <= dragstop[1]; a++)
						$('#cell_' + dragstop[0] + '_' + a).find('img')
							.attr('src', $('#cell_' + dragstop[0] + '_' + a)
								.data('img'));
				else if(newdrag[1] < dragstop[1])
					for(a = dragcoords[0]; a <= dragstop[0]; a++)
						$('#cell_' + a + '_' + dragstop[1]).find('img')
							.attr('src', $('#cell_' + dragstop[0] + '_' + a)
								.data('img'));
				else
					for(a = dragcoords[0]; a <= newdrag[0]; a++)
						for(b = dragcoords[1]; b <= newdrag[1]; b++)
							$('#cell_' + a + '_' + b)
								.find('img')
								.attr('src', kaiju_globals.base_url + 'images/tiles/box-hi.gif');
				dragstop = $(this).attr('id').split('_');
				dragstop = [dragstop[1], dragstop[2]];
	 		}
		});
	$('#minimap')
		.dialog('option', 'width', w * pxsize + 24)
		.dialog('option', 'position', 'center');
	$('#nav_int').change(function() { navint = $(this).val(); });
	$('#nav_nw').click(function() { nav(-1 * navint, -1 * navint); });
	$('#nav_n').click(function() { nav(-1 * navint, 0); });
	$('#nav_ne').click(function() { nav(-1 * navint, 1 * navint); });
	$('#nav_w').click(function() { nav(0, -1 * navint); });
	$('#nav_e').click(function() { nav(0, 1 * navint); });
	$('#nav_sw').click(function() { nav(1 * navint, -1 * navint); });
	$('#nav_s').click(function() { nav(1 * navint, 0); });
	$('#nav_se').click(function() { nav(1 * navint, 1 * navint); });
	$('#btn_minimap').click(function()
	{
		var x = $('body').data('x');
		var y = $('body').data('y');
		$('#minimap_img').attr('src', kaiju_globals.site_url + 'mapedit/mini/' + x + '/' + y + '/'
			+ new Date().getTime() + '/');
		$('#minimap').dialog('open');
	});
	$('#minimap_img').live('click', function(e)
	{
		var offset = $(e.target).offset(false);
        var newy = parseInt((e.pageX - offset.left + 1) / pxsize) - 4;
        var newx = parseInt((e.pageY - offset.top + 1) / pxsize) - 4;
        var x = $('body').data('x');
        var y = $('body').data('y');
        nav(parseInt(newx - x), parseInt(newy - y));
        $('#btn_minimap').click();
	});
	$('body').data('x', 1).data('y', 1);
	$('#minimap_img').attr('src', kaiju_globals.site_url + 'mapedit/mini/1/1/');
	getChunk(1, 1);	
});
