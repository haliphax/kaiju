// load the view
incTrans();
$.ajax({
	url: kaiju_globals.base_url + 'client/actionview/global/bldg_opts/?1&2',
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
		building_opts_hooks();
		$(document.body).append($('#hideme').html());
		$('#hideme').html('');		
		$('#a_bldg_opts').removeClass('a-loading');
	},
	error: ajaxError,
	complete: decTrans
});

// building options
function bldg_opts()
{
	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/actparams/global/bldg_opts/?1&2',
		type: 'GET',
		dataType: 'json',
		async: true,
		success: function(ret)
		{
			if(ret.params == null)
			{
				$('#building_opts table tr:not(tr:last)').hide();
				building_upkeep_params();
				$('#building_opts')
					.dialog('option', 'width', 600 )
					.dialog('open');
				return;
			}
			
			$('#building_opts table tr').show();
			$('#building_opts_name').val(ret.params.name);
			$('#building_opts_idescr').val(ret.params.idescr);
			$('#building_opts_odescr').val(ret.params.odescr);
			building_upkeep_params();
			$('#building_opts')
				.dialog('option', 'width', 600 )
				.dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
	
	return false;
}

// get building upkeep options
function building_upkeep_params() {
	incTrans();
	$.ajax({
		url: kaiju_globals.base_url + 'client/actparams/global/bldg_upkeep/?1&2',
		type: 'GET',
		dataType: 'json',
		async: false,
		success: function(ret)
		{
			$('#building_upkeep_params option').remove();
			for(r in ret.params)
			{
				$('#building_upkeep_params')
					.append('<option value="'
						+ ret.params[r].inum + '">'
						+ ret.params[r].iname +
						(ret.params[r].num ? ' [' + ret.params[r].num + ']'
							: '')
						+ '</option>');
			}
		},
		error: ajaxError,
		complete: decTrans
	});
}

// set building options
function building_opts_hooks() {
	$('#building_opts_submit').click(function()
	{
		incTrans();
		$.ajax(
		{
			type: 'POST',
			url: kaiju_globals.base_url + 'client/action/global/bldg_opts/?1&2',
			data: {
				name: $('#building_opts_name').val(),
				idescr: $('#building_opts_idescr').val(),
				odescr: $('#building_opts_odescr').val()
			},
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				$('#building_opts').dialog('close');
				getStatus(true);
			},
			error: ajaxError,
			complete: decTrans
		});
	});
	// building upkeep
	$('#building_upkeep_submit').click(function()
	{
		incTrans();
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'client/action/global/bldg_upkeep/'
				+ $('#building_upkeep_params').val() + '/?1&2',
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				building_upkeep_params();
				getStatus(true);
			},
			error: ajaxError,
			complete: decTrans
		});
	});
	// building upkeep x5
	$('#building_upkeep_repeat').click(function()
	{
		incTrans();
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'client/repeat/action/global/bldg_upkeep/'
				+ $('#building_upkeep_params').val() + '/?1&2',
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				building_upkeep_params();
				getStatus(true);
			},
			error: ajaxError,
			complete: decTrans
		});
	});
}