$(function()
{	
	$('#clan_roster_tab').click(function()
	{
		if($(this).data('clicked') == 1) return;
		$(this).data('clicked', 1);
		list_roster();
	});

	$('#clan_relations_tab').click(function()
	{
		if($(this).data('clicked') == 1) return;
		$(this).data('clicked', 1);
		list_relations();
	});

	$('#quit_clan_btn').click(function()
	{
		if(! confirm('Are you sure?')) return;
		window.location = kaiju_globals.base_url + 'clans/leave';
	});
});

function list_roster()
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/list_roster',
		dataType: 'html',
		success: function(ret)
		{
			$('#clan_roster_list').html(ret);
			$('#clan_roster_list table tbody tr td:nth-child(1), '
				+ '#clan_roster_list table body tr td:nth-child(3)')
				.css('whiteSpace', 'nowrap');
			$('#clan_roster_list table tbody tr td:nth-child(1)')
				.css('width', '100%');
			try { roster_buttons(); } catch(ex) {}
		}
	});
}

function list_relations()
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/list_relations',
		dataType: 'html',
		success: function(ret)
		{
			$('#clan_relations_list').html(ret);
			$('#clan_relations_list table tbody tr td:nth-child(1)')
				.css('width', '100%')
				.find('a').click(function()
				{
					clan_info_dialog($(this).parent().find('span').html());
				});
			try { relations_buttons(); } catch(ex) {}
		}
	});
}