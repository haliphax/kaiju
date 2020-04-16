$(function()
{
	$('div.progbar').each(function()
	{
		$(this).progressbar('option', 'value', $(this).attr('progress'));
	});
	
	$('.btn_connect').click(function()
	{
		window.location = kaiju_globals.site_url + 'characters/connect/' + $(this).attr('character') + '/';
	});
	
	var cur = $('#cur_char').html();
	if(cur != '') $('#characters').tabs('option', 'selected', Math.round($('#cur_char').html()));
});
