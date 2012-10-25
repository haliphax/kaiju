$(function()
{
	$('#clan_opts_form').submit(function()
	{
		$.ajax({
			type: 'POST',
			url: kaiju_globals.base_url + 'clans/options',
			data: { policy: $('#opt_policy').val() },
			dataType: 'json',
			success: function(ret)
			{
				if(ret == null || ! ret.success)
					alert("Error updating clan options.");
				else
					alert("Clan options updated successfully.");
			},
			error: function(ret)
			{
				alert("Error updating clan options.");
			}
		});
		return false;
	});
	
	$('#clan_stepdown_form').submit(function()
	{
		if($('#clan_stepdown_successor').val() == 0)
		{
			if(prompt('If you are ABSOLUTELY sure you want to disband the clan, type "DELETE" into the box below (in all caps)')
				!= 'DELETE')
			{
				return false;
			}
		}
		else
			if(! confirm('Are you absolutely CERTAIN you want to resign from clan leadership?'))
				return false;
				
		$.ajax({
			type: 'POST',
			url: kaiju_globals.base_url + 'clans/stepdown',
			data: { successor: $('#clan_stepdown_successor').val() },
			dataType: 'json',
			success: function(ret)
			{
				if(ret == null || ! ret.success)
					alert("Error resigning from clan leadership.");
				else
					window.location.reload();
			},
			error: function(ret)
			{
				alert("Error resigning from clan leadership.");
			}
		});
		return false;
	});
	
	$('#clan_applications_tab').click(function()
	{
		if($(this).data('clicked') == 1) return;
		$(this).data('clicked', 1);
		list_applications();
	});
	
	$('#clan_invitations_tab').click(function()
	{
		if($(this).data('clicked') == 1) return;
		$(this).data('clicked', 1);
		list_invitations();
	});
	
	$('#send_invitation_link a').click(function()
	{
		$('#invitation_dialog')
			.dialog('option', 'width', 400)
			.dialog('open');
		return false;
	});
	
	$('#send_invitation').submit(function()
	{
		$.ajax({
			type: 'POST',
			url: kaiju_globals.base_url + 'clans/send_invitation',
			data: {
				actor: $('#invitation_recipient').val(),
				msg: $('#invitation_msg').val()
			},
			dataType: 'json',
			success: function(ret)
			{
				if(ret == null || ! ret.success)
					alert('Error sending invitation.');
				else
				{
					alert('Invitation sent.');
					$('#invitation_dialog').dialog('close');
					list_invitations();
				}
			},
			error: function(ret)
			{
				alert('Error sending invitation.');
			},
			complete: function()
			{
				$('#invitation_recipient').val('');
				$('#invitation_msg').val('');
			}
		});
		return false;
	});
});

function list_applications()
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/list_applications',
		dataType: 'html',
		success: function(ret)
		{
			$('#clan_applications_list').html(ret);
			$('#clan_applications_list table tbody tr td:nth-child(1), '
				+ '#clan_applications_list table tbody tr td:nth-child:(3)')
				.css('whiteSpace', 'nowrap');
			$('#clan_applications_list table tbody tr td:nth-child(2)')
				.css('width', '100%');
			$('#clan_applications_list table tbody tr td:nth-child(3)')
				.each(function()
				{
					var who = $(this).parent().parent().find('td:first span')
						.html();
					$(this).html(
						'<button type="button" class="button" onclick="accept_application('
						+ who
						+ ');">Accept</button> <button type="button" class="button" onclick="deny_application('
						+ who + ');">Deny</button>');
				});
			$('#clan_applications_list .button')
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
		}
	});
}

function list_invitations()
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/list_invitations',
		dataType: 'html',
		success: function(ret)
		{
			$('#clan_invitations_list').html(ret);
			$('#clan_invitations_list table tbody tr td:nth-child(1), '
				+ '#clan_invitations_list table body tr td:nth-child(3)')
				.css('whiteSpace', 'nowrap');
			$('#clan_invitations_list table tbody tr td:nth-child(2)')
				.css('width', '100%');
			$('#clan_invitations_list table tbody tr td:nth-child(3)')
				.each(function()
				{
					$(this).html(
						'<button type="button" class="button" onclick="cancel_invitation('
						+ $(this).parent().parent()
							.find('td:first span').html()
						+ ');">Cancel</button>');
				});
			$('#clan_invitations_list .button')
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
		}
	});
}

function cancel_invitation(who)
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/cancel_invitation/' + who,
		dataType: 'json',
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error canceling invitation.');
			else
			{
				alert('Invitation canceled.');
				list_invitations();
			}
		},
		error: function(ret)
		{
			alert('Error canceling invitation.');
		}
	});
}

function accept_application(who)
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/accept_application/' + who,
		dataType: 'json',
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error accepting application.');
			else
			{
				alert('Application accepted.');
				list_applications();
			}
		},
		error: function(ret)
		{
			alert('Error accepting application.');
		}
	});
}

function deny_application(who)
{
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/deny_application/' + who,
		dataType: 'json',
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error denying application.');
			else
			{
				alert('Application denied.');
				list_applications();
			}
		},
		error: function(ret)
		{
			alert('Error denying application.');
		}
	});
}

function roster_buttons()
{
	$('#clan_roster_list table tbody tr td:nth-child(2)')
		.each(function()
		{
			var td = $(this).parent().find('td')[0];
			if($(td).find('small').length == 0)
				$(this).html(
					'<button type="button" class="button" onclick="remove_member('
					+ $(td).find('span').html() + ');">Remove</button>');
		});
	$('#clan_roster_list .button')
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
}

function remove_member(who)
{
	if(! confirm('Are you sure?')) return;
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/remove_member/' + who,
		dataType: 'json',
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert('Error removing member.');
			else
			{
				alert('Member removed.');
				list_roster();
			}
		},
		error: function(ret)
		{
			alert('Error removing member.');
		}
	});
}

function remove_relation(which)
{
	if(! confirm('Are you sure?')) return;
	$.ajax({
		type: 'GET',
		url: kaiju_globals.base_url + 'clans/remove_relation/' + which,
		dataType: 'json',
		success: function(ret)
		{
			if(ret == null || ! ret.success)
				alert("Error removing relation.");
			else
			{
				alert("Relation removed.");
				list_relations();
			}
		},
		error: function(ret)
		{
			alert('Error removing relation.');
		}
	});
}

function relations_buttons()
{
	$('#clan_relations_list table tbody tr td:nth-child(3)')
		.each(function()
		{
			var which = $(this).parent().parent().find('td:first span')
				.html();
			$(this).html(
				'<button type="button" class="button" onclick="remove_relation('
				+ which
				+ ');">Remove</button>');
		});
	$('#clan_relations_list .button')
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
}