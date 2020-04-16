function describeSkill(skill)
{
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: kaiju_globals.base_url + 'skilltree/describe/' + skill,
		async: true,
		success: function(ret)
		{
			$('#skilldesc')
				.dialog('option', 'title', ret.sname)
				.html(ret.descr)
				.dialog('open');
		}
	});
}

$(function()
{
	$('.purchase').click(function()
	{
		if(! confirm(
			'Purchase ' + $(this).parent().parent().find('a:first').html()
			+ '?'))
		{
			return false;
		}
		
		window.location = kaiju_globals.base_url + 'skilltree/purchase/' + $(this).attr('abbrev') + '/'
			+ $(this).attr('aclass') + '/' + $(this).attr('skill');
	});
});