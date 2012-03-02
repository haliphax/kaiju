<?php if(! defined('BASEPATH')) exit(); ?>
<div id="building_opts" class="dialog" title="Building options" style="max-width:600px">
	<table cellspacing="0" cellpadding="4" id="building_opts_table" style="width:100%">
		<tr>
			<td class="bold tright">Name:</td>
			<td style="width:100%">
				<input style="width:100%" id="building_opts_name" type="text" maxlength="64" />
			</td>
		</tr>
		<tr>
			<td class="bold tright">Description (indoors):</td>
			<td>
				<textarea style="width:100%" id="building_opts_idescr" type="text" maxlength="512"></textarea>
			</td>
		</tr>
		<tr>
			<td class="bold tright">Description (outdoors):</td>
			<td>
				<textarea style="width:100%" id="building_opts_odescr" type="text" maxlength="512"></textarea>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<button class="button" id="building_opts_submit">Update</button>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="bold tright">Upkeep:</td>
			<td>
				<div style="white-space:nowrap">
					<select id="building_upkeep_params"></select>
					<button class="button" id="building_upkeep_submit">Use</button>
					<button class="button" id="building_upkeep_repeat">x5</button>
				</div>
			</td>
		</tr>
	</table>
</div>
