$(function()
{
	try {
		$('#my_invites tr td:nth-child(1)').css({
			width: 'auto',
			whiteSpace: 'nowrap'
		});
		$('#my_invites tr td:nth-child(3)').each(function()
		{
			var clan = $(this).parent().parent().find('td:first span').html();
			$(this).html(
				'<button type="button" class="button" onclick="accept_invitation('
				+ clan
				+ ');">Accept</button> <button type="button" class="button" onclick="deny_invitation('
				+ clan + ');">Deny</button>');
		});
		$('#my_invites .button')
			.addClass('ui-state-default ui-corner-all')
			.hover(
				function()
				{
					$(this).addClass('ui-state-focus');
				},
				function()
				{
					$(this).removeClass('ui-state-focus');
				}
			);
	} catch(ex) {}
	
	$('#application_dialog').dialog('option', 'width', 400);
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/list_open',
		dataType: 'html',
		success: function(ret)
		{
			$('#open_clans_list').html(ret);
			$('#open_clans_list table tbody tr td:nth-child(3)')
				.css('text-align', 'right');
			$('#open_clans_list table tbody tr td:nth-child(1) a').click(
			function()
			{
				var id = $(this).parent().find('span').html();
				clan_info_dialog(id);
			});
			$('#open_clans_list .button')
				.addClass('ui-state-default ui-corner-all')
				.hover(
					function()
					{
						$(this).addClass('ui-state-focus');
					},
					function()
					{
						$(this).removeClass('ui-state-focus');
					}
				)
				.click(function()
				{
					var clandescr = $(this).parent().parent().find('td:first a')
						.html();
					var clanid = $(this).parent().parent().find('td:first span')
						.html();
					$('#application_dialog')
						.data('clan', clanid)
						.dialog('option', 'title', 'Application for '
							+ clandescr)
						.dialog('open');
				});
		}
	});
	
	$('#clan_application').submit(function()
	{
		$.ajax({
			type: 'POST',
			url: kaiju_globals.base_url + 'clans/apply',
			data: {
				clan: $('#application_dialog').data('clan'),
				msg: $('#clan_application_msg').val()
			},
			dataType: 'json',
			success: function(ret)
			{
				if(ret == null || ! ret.success)
					alert("Error submitting application.");
				else
					alert("Your application has been submitted.");
			},
			error: function(ret)
			{
				alert("Error submitting application.");
			},
			complete: function()
			{
				$('#clan_application_msg').val('');
				$('#application_dialog').dialog('close');
			}
		});
		return false;
	});
	
	$('#closed_clans_tab').bind('click', function()
	{
		if($(this).data('clicked') == 1) return;
		$.ajax({
			type: 'GET',
			url: kaiju_globals.base_url + 'clans/list_closed',
			dataType: 'html',
			success: function(ret)
			{
				$('#closed_clans_list').html(ret);
				$('#closed_clans_list table tbody tr td:nth-child(3)')
					.css('text-align', 'right');
				$('#closed_clans_list table tbody tr td:nth-child(1) a')
					.click(function()
					{
						var id = $(this).parent().find('span').html();
						clan_info_dialog(id);
					});
				$('#closed_clans_list .button')
					.addClass('ui-state-default ui-corner-all')
					.hover(
						function()
						{
							$(this).addClass('ui-state-focus');
						},
						function()
						{
							$(this).removeClass('ui-state-focus');
						}
					);
				$(this).data('clicked', 1);
			}
		});
	});
});

function accept_invitation(clan)
{
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: kaiju_globals.base_url + 'clans/accept_invitation/' + clan,
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error accepting invitation.');
			else
			{
				alert('Invitation accepted.');
				window.location.reload();
			}
		},
		error: function()
		{
			alert('Error accepting invitation.');
		}
	});
}

function deny_invitation(clan)
{
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: kaiju_globals.base_url + 'clans/deny_invitation/' + clan,
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error denying invitation.');
			else
			{
				alert('Invitation denied.');
				window.location.reload();
			}
		},
		error: function()
		{
			alert('Error denying invitation.');
		}
	});
}