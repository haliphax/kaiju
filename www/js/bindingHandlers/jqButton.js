// bindingHandlers/jqButton
define([ 'lib/jquery', 'lib/jquery-ui', 'lib/knockout', 'global' ], function ($, $ui, ko, global) {
	ko.bindingHandlers.jqButton = {
		init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Initializing button ' + $(element).html());

			var
				opts = { state: 'default', focus: 'focus' } 
				, val = valueAccessor()
			;

			if (typeof val == 'object') $.extend(opts, val);

			$(element)
                .addClass('button')
				.addClass('ui-state-' + opts.state)
				.addClass('ui-corner-all')
				.hover(
					function() { $(this).addClass('ui-state-' + opts.focus); }
					, function() { $(this).removeClass('ui-state-' + opts.focus); }
				)
			;
		}
	};
});
