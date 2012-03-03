$(function()
{
	$('#clan_relation_add').click(function()
	{
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'clans/add_relation/' + $('#clan_info_dialog').data('c')
				+ '?1&2',
			dataType: 'json',
			success: function(ret)
			{
				if(ret == null || ! ret.success)
				{
					alert("Error adding relation.");
					return;
				}
				
				alert("Relation added successfully.");
				clan_info_dialog($('#clan_info_dialog').data('c'));
				try { list_relations(); } catch(ex) {}
			},
			error: function(ret)
			{
				alert("Error adding relation.");
			}
		});
	});
	
	$('.clan-link').live('click', function() {
		clan_info_dialog($(this).find('i:first').html());
	});
});

function clan_info_dialog(which)
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/info/' + which + '?1&2',
		dataType: 'json',
		success: function(ret)
		{
			if(! ret.clan)
			{
				alert("Error retrieving clan information.");
				return;
			}
			
			$('#clan_info_dialog').data('c', ret.clan);
			$('#clan_info_clan').html(ret.descr
				+ (ret.rel ? ' <small><i>' + ret.rel + '</i></small>' : ''));
			$('#clan_info_leader')
				.html('<a href="#">' + ret.leader_name + '</a>');
			$('#clan_info_faction').html(ret.faction_name);
			$('#clan_info_members').html(ret.members);
			
			if(ret.isleader)
			{
				if(ret.fmatch)
					$('#clan_relation_add').html('Declare alliance');
				else
					$('#clan_relation_add').html('Declare war');
				$('#clan_info_buttons').show();
			}
			else
				$('#clan_info_buttons').hide();
			
			$('#clan_info_dialog').dialog('open');
		},
		error: function(ret)
		{
			alert("Error retrieving clan information.");
		}
	});
	
	return false;
}
