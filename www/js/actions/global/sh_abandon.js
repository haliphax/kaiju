$('#a_sh_abandon').removeClass('a-loading');

function sh_abandon()
{
	if(! confirm('Are you sure?')) return false;
	useAction('global', 'sh_abandon');
	return false;
}