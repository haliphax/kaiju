function shopkeeping()
{
	if($('#a_auction_list').length > 0)
	{
		if(confirm('Are you sure you wish to close up your shop?'))
			useSkill('shopkeeping');
	}
	else
	{
		if(confirm('Are you sure you wish to start a shop here?'))
			useSkill('shopkeeping');
	}
}

$('#a_shopkeeping').removeClass('a-loading');
