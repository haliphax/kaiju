// bindingHandlers/gameMapTile
define([ 'lib/jquery', 'lib/knockout', 'global' ], function ($, ko, global) {
	ko.bindingHandlers.gameMapTile = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug && viewModel.descr != '') console.log('DEBUG: Updating map tile for ' + viewModel.descr);

			var
				$el = $(element)
				, $int = $el.find('.map_cell')
			;
			
			$el
				.attr('title', viewModel.descr)
				.addClass('tile-' + viewModel.img.replace(/\..+/, ''))
			;

			if (viewModel.w != '') $int.addClass('walls').addClass('walls-' + viewModel.w);
		}
	};
});
