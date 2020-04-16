$(function()
{
	$('div.progbar').each(function()
	{
		$(this).progressbar('option', 'value', $(this).attr('progress'));
	});
});