$('#hideme').append('<div id="scan" class="dialog" title="Scan"><div id="scan_map"></div></div>');
$('#hideme').find('.dialog').dialog({ maxHeight: 600, autoOpen: false });
$(document.body).append($('#hideme').html());
$('#hideme').html('');
$('#a_scanhrzn').removeClass('a-loading');

// scan horizon
function scanhrzn()
{
	incTrans();
	$.ajax(
	{
		type: 'GET',
		url: kaiju_globals.base_url + 'client/action/global/scanhrzn/?1',
		dataType: 'json',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			var html = '';
			if(ret.cells) html = buildMap(ret.cells);
			$('#scan_map').html(html);
			$('#scan').dialog('option', 'width', 600).dialog('open');
			getStatus(true);
		},
		error: ajaxError,
		complete: decTrans
	});
	
	getStatus(true);
}