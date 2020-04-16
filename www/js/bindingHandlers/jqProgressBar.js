// bindingHandlers/jqProgressBar
define([ 'lib/jquery', 'lib/jquery-ui', 'lib/knockout', 'global' ], function ($, $ui, ko, global) {
	ko.bindingHandlers.jqProgressBar = {
		init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Initializing progress bar #' + $(element)[0].getAttribute('id'));

			var
				$el = $(element)
				, val = valueAccessor()
			;
			
			if (typeof val == 'object') {
				$el.progressbar(val);
				return;
			}
			
			$el.progressbar();
		}
		, update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Updating progress bar #' + $(element)[0].getAttribute('id'));

			var
				$el = $(element)
                , nan = false
				, val = valueAccessor()
			;
	
			if (typeof val == 'object') {
				$el.progressbar('option', val);
				return;
			}
			
            nan = isNaN(val);
			$el.progressbar('value', nan ? 100 : val);
			if (nan) $el.progressbar('disable');
		}
	};
});
