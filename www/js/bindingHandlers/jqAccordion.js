// bindingHandlers/jqAccordion
define([ 'lib/jquery', 'lib/jquery-ui', 'lib/knockout', 'global' ], function ($, $ui, ko, global) {
    ko.bindingHandlers.jqAccordion = {
		init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Initializing accordion #' + $(element)[0].getAttribute('id'));

			var
				$el = $(element)
                , opt = { heightStyle: 'content', collapsible: true, animate: false }
				, val = valueAccessor()
			;

			if (typeof val == 'object') $.extend(opt, val);
			$el.addClass('accordion').accordion(opt);
		}
		, update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
			if (global.debug) console.log('DEBUG: Updating accordion #' + $(element)[0].getAttribute('id'));

			var
				$el = $(element)
				, val = valueAccessor()
			;
	
			if (typeof val == 'object') $el.accordion('option', val);
            $el.accordion('refresh');
		}
	};
});
