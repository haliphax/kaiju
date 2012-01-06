<?php if(! defined('BASEPATH')) exit(); ?>
<div id="auction_list" class="dialog" title="Auctions">
	<p>
		<button class="button" id="my_bids_button">My Bids</button>&nbsp;
		<button class="button" id="my_auctions_button">My Auctions</button>
	</p>
	<table cellspacing="0" cellpadding="4" id="auction_list_table">
		<tr class="ui-state-focus" id="auction_list_enc">
			<td style="width:50%"><b>Item(s)</b></td>
			<td style="width:50%"><b>Price</b></td>
			<td><b>Bids</b></td>
			<td class="nowrap"><b>Time left</b></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="myauctions_list" class="dialog" title="My Auctions">
	<p>
		<button class="button" id="create_auction_button">Create auction</button>
	</p>
	<table cellspacing="0" cellpadding="4" id="myauctions_list_table">
		<tr class="ui-state-focus" id="myauctions_list_enc">
			<td style="width:50%"><b>Auctioned items</b></td>
			<td style="width:50%"><b>Price</b></td>
			<td class="nowrap"><b>Bids</b></td>
			<td class="nowrap"><b>Time left</b></td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="mybids_list" class="dialog" title="My Bids">
	<table cellspacing="0" cellpadding="4" id="mybids_list_table">
		<tr class="ui-state-focus" id="mybids_list_enc">
			<td style="width:50%"><b>Auctioned item(s)</b></td>
			<td style="width:50%"><b>My bid</b></td>
			<td><b>Bids</b></td>
			<td class="nowrap"><b>Time left</b></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="offers_list" class="dialog" title="Offers">
	<table cellspacing="0" cellpadding="4" id="offers_list_table">
		<tr class="ui-state-focus" id="offers_list_enc">
			<td style="width:100%"><b>Offered item(s)</b></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="auction_auction" class="dialog" title="Create auction">
	<p>
		Price: <input type="text" id="auction_auction_price" />
	</p>
	<h2>Inventory</h2>
	<div id="auction_auction_inventory" class="wrapdivs"></div>
	<div class="wrapdivs" style="clear:left">&nbsp;</div>
	<h2>Auction</h2>
	<div id="auction_auction_auction" class="wrapdivs"></div>
	<div class="wrapdivs" style="clear:left">&nbsp;</div>
	<p style="text-align:center">
		<button class="button" id="auction_auction_submit">Create</button>
	</p>
</div>
<div id="auction_bid" class="dialog" title="Bid">
	<h3>Bidding on:</h3>
	<div id="auction_bid_auction" class="wrapdivs"></div>
	<div class="wrapdivs" style="clear:left">&nbsp;</div>
	<h2>Inventory</h2>
	<div id="auction_bid_inventory" class="wrapdivs"></div>
	<div class="wrapdivs" style="clear:left">&nbsp;</div>
	<h2>Bid</h2>
	<div id="auction_bid_bid" class="wrapdivs"></div>
	<div class="wrapdivs" style="clear:left">&nbsp;</div>
	<p style="text-align:center">
		<button class="button" id="auction_bid_submit">Submit</button>
	</p>
</div>
