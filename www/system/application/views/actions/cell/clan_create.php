<?php if(! defined('BASEPATH')) exit(); ?>
<div class="dialog" id="clan_create_dialog" title="Form a clan">
<form id="clan_create_form">
	<table style="margin:0 auto">
		<tbody>
			<tr>
				<th class="bold tright">Name:</th>
				<td><input type="text" id="clan_create_name" /></td>
			</tr>
			<tr>
				<th class="bold tright">Policy:</th>
				<td>
					<select id="clan_create_policy">
						<option>open</option>
						<option>closed</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<p style="text-align:center">
		<button type="submit" class="button">Create</button>
	</p>
</form>
</div>