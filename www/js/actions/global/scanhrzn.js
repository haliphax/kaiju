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
			var html = '';
			if(ret.cells) html = buildMap(ret.cells);
			ret.cells = null;
			ajaxResponse(ret);
			$('#scan_map').html(html);
			$('#scan').dialog('option', 'width', 600).dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
}
