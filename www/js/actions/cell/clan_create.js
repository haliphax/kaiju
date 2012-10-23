// load the view
incTrans();
$.ajax({
	url: kaiju_globals.base_url + 'client/actionview/cell/clan_create/?1&2',
	type: 'GET',
	dataType: 'html',
	async: true,
	success: function(ret)
	{
		$('#hideme')
			.append(ret)
			.find('.dialog')
			.dialog({ maxHeight: 600, autoOpen: false });
		$('.button')
			.addClass('ui-state-default')
			.addClass('ui-corner-all');
		clan_create_hooks();
		$(document.body).append($('#hideme').html());
		$('#hideme').html('');
		$('#a_clan_create').removeClass('a-loading');
	},
	error: ajaxError,
	complete: decTrans
});

function clan_create_hooks()
{
	$('#a_clan_create').click(function()
	{
		$('#clan_create_dialog').dialog('open');
		return false;
	});
	$('#clan_create_form').submit(function()
	{
		var clanname = $('#clan_create_name').val().replace(/^\s*|\s*$/i, '');
		if(! clanname) return false;
		var clanpolicy = $('#clan_create_policy').val();
		
		incTrans();
		$.ajax({
			url: kaiju_globals.base_url + 'client/action/cell/clan_create/?1&2',
			type: 'POST',
			data: { name: clanname, policy: clanpolicy },
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				ajaxResponse(ret);
				$('#clan_create_dialog').dialog('close');
			},
			error: ajaxError,
			complete: decTrans
		});
		return false;
	})
}