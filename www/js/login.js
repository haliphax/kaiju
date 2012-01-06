$(function()
{
	$('.accordion-fixed').accordion({ collapsible: false });	
	$('#btn_login').click(function()
	{
		$('#frm_login').submit(); 
		return false;
	});
	$('#user').focus();
});