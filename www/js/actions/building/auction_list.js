// load the view
incTrans();
$.ajax({
	url: kaiju_globals.base_url + 'client/actionview/building/auctions/?1&2',
	type: 'GET',
	dataType: 'html',
	async: true,
	success: function(ret)
	{
		$('#hideme')
			.append(ret)
			.find('.dialog')
			.dialog({ maxHeight: 600, autoOpen: false });
		$('.button')
			.addClass('ui-state-default')
			.addClass('ui-corner-all');
		auction_list_hooks();
		$(document.body).append($('#hideme').html());
		$('#hideme').html('');
		$('#a_auction_list').removeClass('a-loading');
	},
	error: ajaxError,
	complete: decTrans
});

// list auctions
function auction_list()
{
	incTrans();
	$.ajax(
	{
		type: 'GET',
		url: kaiju_globals.base_url + 'client/action/building/auction_list/?1&2',
		dataType: 'json',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			html = '';
			$('.auc_remove').remove();
			var odd = 0;
			
			for(r in ret.auctions)
			{
				items = '';
				for(i in ret.auctions[r].items)
					items += (items ? ', ' : '')
						+ '<a href="#" onclick="describeItem(\''
						+ ret.auctions[r].items[i].instance + '\');">'
						+ ret.auctions[r].items[i].iname
						+ (ret.auctions[r].items[i].num > 1 ? ' ['
						+ ret.auctions[r].items[i].num + ']' : '') + '</a>';
				var timeleft = parseInt(ret.auctions[r].timeleft);
				if(timeleft > 24)
					timeleft = parseInt(timeleft / 24) + 'd'
						+ parseInt(timeleft % 24);
				timeleft += 'h';
				html += '<tr class="auc_remove '
					+ (odd ? 'ui-state-highlight' : '') + '">'
					+ '<td>' + items + '</td><td>' + ret.auctions[r].price
					+ '</td>'
					+ '<td style="text-align:right">' + ret.auctions[r].bids
					+ '</td>' + '<td style="text-align:right">' + timeleft
					+ '</td><td>' + (ret.auctions[r].mybid == 1 ? ''
					: '<button class="button ui-state-default ui-corner-all" onclick="auction_bid(this, '
					+ ret.auctions[r].auction + ');">Bid</button>')
					+ '</td></tr>';
				odd = (odd == 1 ? 0 : 1);
			}
			
			$('#auction_list_enc').after(html);
			if(ret.hidemyauctionsbutton)
				$('#my_auctions_button').hide();
			else
				$('#my_auctions_button').show();
			$('#auction_list').dialog('option', 'width', 700).dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
}

// build bid for auction
function auction_bid(td, aucnum)
{
	incTrans();
	$.ajax(
	{
		type: 'GET',
		url: kaiju_globals.base_url + 'client/actparams/building/auction_bid/?1',
		async: true,
		dataType: 'json',
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			$('#auction_bid').data('aucnum', aucnum);
			$('#auction_bid_auction')
				.html($(td).parent().parent().find('td:first').html());
			var html = '';
			for(r in ret.params) {
				var sel = '<select>';
				
				if(ret.params[r].num > 1) {
					for(a = 1; a <= ret.params[r].num; a++)
						sel += '<option' + (a < ret.params[r].num ? ''
							: ' selected="selected"') + '>' + a + '</option>';
				}
				
				sel += '</select>';			
				html += '<div class="nowrap" instance="'
					+ ret.params[r].instance
					+ '"><a href="#" onclick="return describeItem('
					+ ret.params[r].instance + ');" title="View item info">'
					+ ret.params[r].iname + '</a>'
					+ (ret.params[r].num > 1 ? ' ' + sel : '')
					+ '<small> <a href="#" onclick="return auction_bid_add(this);" title="Bid this item">[+]</a><a href="#" onclick="return auction_bid_del(this);" title="Remove from bid" class="hidden">[-]</a></small>&nbsp;&nbsp;</div>';
			}
			
			$('#auction_bid_inventory').html(html);
			$('#auction_bid_bid').html('');
			$('#auction_bid').dialog('open');
		},
		error: ajaxError,
		complete: decTrans	
	});
}

// add item to bid
function auction_bid_add(anchor)
{
	var p = $(anchor).parent().parent();
	p.remove().appendTo('#auction_bid_bid');
	p.find('a:nth(1)').addClass('hidden');
	p.find('a:last').removeClass('hidden');
	p.find('select').attr('disabled', 'disabled');
}

// remove item from bid
function auction_bid_del(anchor)
{
	var p = $(anchor).parent().parent();
	p.remove().appendTo('#auction_bid_inventory');
	p.find('a:last').addClass('hidden');
	p.find('a:nth(1)').removeClass('hidden');
	p.find('select').attr('disabled', null);
}

// add item to auction
function auction_auction_add(anchor)
{
	var p = $(anchor).parent().parent();
	p.remove().appendTo('#auction_auction_auction');
	p.find('a:nth(1)').addClass('hidden');
	p.find('a:last').removeClass('hidden');
	p.find('select').attr('disabled', 'disabled');
}

// remove item from auction
function auction_auction_del(anchor)
{
	var p = $(anchor).parent().parent();
	p.remove().appendTo('#auction_auction_inventory');
	p.find('a:last').addClass('hidden');
	p.find('a:nth(1)').removeClass('hidden');
	p.find('select').attr('disabled', null);
}

// cancel a bid
function auction_bid_cancel(aucnum)
{
	if(! confirm("Are you sure you wish to withdraw your bid?")) return;
	incTrans();
	$.ajax(
	{
		url: kaiju_globals.base_url + 'client/action/building/auction_bid_cancel/' + aucnum + '/?1',
		dataType: 'json',
		type: 'GET',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			$('#my_bids_button').click();
		},
		error: ajaxError,
		complete: decTrans
	});
}

// cancel an auction
function auction_auction_cancel(aucnum)
{
	if(! confirm("Are you sure you wish to withdraw your auction?")) return;
	incTrans();
	$.ajax(
	{
		url: kaiju_globals.base_url + 'client/action/building/auction_auction_cancel/' + aucnum + '/?1',
		dataType: 'json',
		type: 'GET',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			$('#my_auctions_button').click();
		},
		error: ajaxError,
		complete: decTrans
	});
}

// view auction's bids
function auction_auction_bids(aucnum)
{
	incTrans();
	$.ajax(
	{
		url: kaiju_globals.base_url + 'client/action/building/auction_auction_bids/' + aucnum + '/?1',
		dataType: 'json',
		type: 'GET',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			$('.offr_remove').remove();
			var html = '';
			var odd = 0;
			
			for(r in ret.bids) {
				var items = '';
				for(i in ret.bids[r])
					items += (items ? ', ' : '')
						+ '<a href="#" onclick="describeItem(\''
						+ ret.bids[r][i].instance + '\');">'
						+ ret.bids[r][i].iname +
						(ret.bids[r][i].num > 1 ? ' [' + ret.bids[r][i].num
						+ ']' : '') + '</a>';
				html += '<tr class="offr_remove '
					+ (odd ? 'ui-state-highlight' : '') + '">'
					+ '<td>' + items + '</td>'
					+ '<td><button class="button ui-button ui-state-default ui-corner-all" onclick="auction_auction_accept('
					+ aucnum + ',' + r + ');">Accept</button></td></tr>';
				odd = (odd == 1 ? 0 : 1);			
			}
			
			$('#offers_list_enc').after(html);
			$('#offers_list').dialog('option', 'width', 600).dialog('open');
		},
		error: ajaxError,
		complete: decTrans
	});
}

// accept a bid
function auction_auction_accept(aucnum, bidnum)
{
	if(! confirm("Are you sure you wish to accept this bid?")) return;
	incTrans();
	$.ajax(
	{
		url: kaiju_globals.base_url + 'client/action/building/auction_auction_accept/' + aucnum + '/'
			+ bidnum + '/?1',
		dataType: 'json',
		type: 'GET',
		async: true,
		success: function(ret)
		{
			if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
			$('#offers_list').dialog('close');
			$('#my_auctions_button').click();
		},
		error: ajaxError,
		complete: decTrans
	});
}

// hooks
function auction_list_hooks()
{
	// submit bid
	$('#auction_bid_submit').click(function()
	{
		var items = '';
		$('#auction_bid_bid div').each(function()
		{
			var num = $(this).find('select').val();
			items += $(this).attr('instance') + (num ? '_' + num : '') + '/';
		});
		
		incTrans();
		$.ajax(
		{
			url: kaiju_globals.base_url + 'client/action/building/auction_bid/'
				+ $('#auction_bid').data('aucnum') + '/' + items + '?1',
			dataType: 'json',
			type: 'GET',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				$('#auction_bid').dialog('close');
				auction_list();
			},
			error: ajaxError,
			complete: decTrans
		});
	});
	
	// show my bids
	$('#my_bids_button').click(function()
	{
		incTrans();
		$.ajax(
		{
			url: kaiju_globals.base_url + 'client/action/building/auction_my_bids/?1',
			dataType: 'json',
			type: 'GET',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				html = '';
				$('.mybids_remove').remove();
				var odd = 0;
				
				for(r in ret.bids)
				{
					items = '';
					for(i in ret.bids[r].items)
						items += (items ? ', ' : '')
							+ '<a href="#" onclick="describeItem(\''
							+ ret.bids[r].items[i].instance + '\');">'
							+ ret.bids[r].items[i].iname +
							(ret.bids[r].items[i].num > 1 ? ' ['
							+ ret.bids[r].items[i].num + ']' : '') + '</a>';
					bid = '';
					for(i in ret.bids[r].mybid)
						bid += (bid ? ', ' : '')
							+ '<a href="#" onclick="describeItem(\''
							+ ret.bids[r].mybid[i].instance + '\');">'
							+ ret.bids[r].mybid[i].iname +
							(ret.bids[r].mybid[i].num > 1 ? ' ['
							+ ret.bids[r].mybid[i].num + ']' : '') + '</a>';
					var timeleft = parseInt(ret.bids[r].timeleft);
					if(timeleft > 24)
						timeleft = parseInt(timeleft / 24) + 'd' + parseInt(timeleft % 24);
					timeleft += 'h';
					html += '<tr class="mybids_remove '
						+ (odd ? 'ui-state-highlight' : '') + '">'
						+ '<td>' + items + '</td><td>' + bid + '</td>'
						+ '<td style="text-align:right">' + ret.bids[r].bids
						+ '</td><td style="text-align:right">' + timeleft
						+ '</td><td><button class="button ui-state-default ui-corner-all" onclick="auction_bid_cancel('
						+ ret.bids[r].auction + ');">Cancel</a></td></tr>';
					odd = (odd == 1 ? 0 : 1);
				}
				
				$('#mybids_list_enc').after(html);
				$('#mybids_list').dialog('option', 'width', 600).dialog('open');
			},
			error: ajaxError,
			complete: decTrans
		});
	});
	
	// show my auctions
	$('#my_auctions_button').click(function()
	{
		incTrans();
		$.ajax(
		{
			url: kaiju_globals.base_url + 'client/action/building/auction_my_auctions/?1',
			dataType: 'json',
			type: 'GET',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				html = '';
				$('.myauctions_remove').remove();
				var odd = 0;
				
				for(r in ret.auctions)
				{
					items = '';
					for(i in ret.auctions[r].items)
						items += (items ? ', ' : '')
							+ '<a href="#" onclick="describeItem(\''
							+ ret.auctions[r].items[i].instance + '\');">'
							+ ret.auctions[r].items[i].iname
							+ (ret.auctions[r].items[i].num > 1 ? ' ['
							+ ret.auctions[r].items[i].num + ']' : '') + '</a>';
					var timeleft = parseInt(ret.auctions[r].timeleft);
					if(timeleft > 24)
						timeleft = parseInt(timeleft / 24) + 'd' + parseInt(timeleft % 24);
					timeleft += 'h';
					html += '<tr class="myauctions_remove '
						+ (odd ? 'ui-state-highlight' : '') + '">'
						+ '<td>' + items + '</td><td>' + ret.auctions[r].price
						+ '</td><td style="text-align:right">'
						+ ret.auctions[r].bids
						+ '</td><td style="text-align:right">' + timeleft
						+ '</td><td class="nowrap">' + (ret.auctions[r].bids > 0
						? '<button class="button ui-state-default ui-corner-all" onclick="auction_auction_bids('
						+ r + ');">View bids</button>' : '') + '</td><td>'
						+ '<button class="button ui-state-default ui-corner-all" onclick="auction_auction_cancel('
						+ r + ');">Cancel</a></td></tr>';
					odd = (odd == 1 ? 0 : 1);
				}
				
				$('#myauctions_list_enc').after(html);
				$('#myauctions_list').dialog('option', 'width', 700).dialog('open');
			},
			error: ajaxError,
			complete: decTrans
		});	
	});
	
	// create an auction
	$('#create_auction_button').click(function()
	{
		incTrans();
		$.ajax(
		{
			type: 'GET',
			url: kaiju_globals.base_url + 'client/actparams/building/auction_auction/?1',
			async: true,
			dataType: 'json',
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);
				var html = '';
				
				for(r in ret.params) {
					var sel = '<select>';
					
					if(ret.params[r].num > 1) {
						for(a = 1; a <= ret.params[r].num; a++)
							sel += '<option'
								+ (a < ret.params[r].num ? ''
								: ' selected="selected"')
								+ '>' + a + '</option>';
					}
					
					sel += '</select>';			
					html += '<div class="nowrap" instance="'
						+ ret.params[r].instance
						+ '"><a href="#" onclick="return describeItem('
						+ ret.params[r].instance + ');" title="View item info">'
						+ ret.params[r].iname + '</a>'
						+ (ret.params[r].num > 1 ? ' ' + sel : '')
						+ '<small> <a href="#" onclick="return auction_auction_add(this);" title="Auction this item">[+]</a><a href="#" onclick="return auction_auction_del(this);" title="Remove from auction" class="hidden">[-]</a></small>&nbsp;&nbsp;</div>';
				}
				
				$('#auction_auction_inventory').html(html);
				$('#auction_auction_auction').html('');
				$('#auction_auction').dialog('open');
			},
			error: ajaxError,
			complete: decTrans	
		});
	});

	// submit an auction
	$('#auction_auction_submit').click(function()
	{
		var items = '';
		
		$('#auction_auction_auction div').each(function()
		{
			var num = $(this).find('select').val();
			items += $(this).attr('instance') + (num ? '_' + num : '') + '/';
		});
		
		incTrans();
		$.ajax(
		{
			type: 'POST',
			url: kaiju_globals.base_url + 'client/action/building/auction_auction/' + items + '?1&2',
			data: { price: $('#auction_auction_price').val() },
			dataType: 'json',
			async: true,
			success: function(ret)
			{
				if(ret.msg) for(r in ret.msg) addToLog(ret.msg[r]);			
				$('#auction_auction').dialog('close');
				$('#my_auctions_button').click();
			},
			error: ajaxError,
			complete: decTrans
		});
	});
}
