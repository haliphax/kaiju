// bindingHandlers/jqDialog
define([ 'lib/jquery', 'lib/jquery-ui', 'lib/knockout', 'global' ], function ($, $ui, ko, global) {
	ko.bindingHandlers.jqDialog = {
		init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Building dialog #' + $(element)[0].getAttribute('id'));
			var
				opts = { maxHeight: 600, autoOpen: false }
				, val = valueAccessor()
			;
			
			if (val !== null) $.extend(opts, val);
			opts.title = $('<div/>').html(opts.title).text();
			$(element).dialog(opts);
		}
		, update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			return;
			if (global.debug) console.log('DEBUG: Updating dialog #' + $(element)[0].getAttribute('id'));

			var val = valueAccessor();			
			if (val !== null) $(element).dialog('option', val);
		}
	};
});
