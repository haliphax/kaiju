<?php if(! defined('BASEPATH')) exit(); ?>
<div class="dialog" title="Clan Information" id="clan_info_dialog">
	<table border="0" cellpadding="0" cellspacing="4"  style="margin:0 auto">
		<tbody>
			<tr>
				<td class="bold tright">Clan:</td>
				<td class="tleft" id="clan_info_clan"></td>
			</tr>
			<tr>
				<td class="bold tright">Leader:</td>
				<td class="tleft" id="clan_info_leader"></td>
			</tr>
			<tr>
				<td class="bold tright">Faction:</td>
				<td class="tleft" id="clan_info_faction"></td>
			</tr>
			<tr>
				<td class="bold tright">Members:</td>
				<td class="tleft" id="clan_info_members"></td>
			</tr>
		</tbody>
		<p style="text-align:center;display:none" id="clan_info_buttons">
			<button class="button" id="clan_relation_add"></button>
		</p>
	</table>
</div>