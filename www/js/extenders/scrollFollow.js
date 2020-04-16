// extenders/scrollFollow
define([ 'lib/jquery', 'lib/knockout', 'global' ], function ($, ko, global) {
	ko.extenders.scrollFollow = function (target, option) {
		if (global.debug) console.log('DEBUG: Scrollfollow activated for #' + $(option)[0].getAttribute('id'));

		target.subscribe(function (newval) {
			var el = $(option)[ 0 ];
			
			if (el.scrollTop == el.scrollHeight - el.clientHeight) {
				setTimeout(
					function () {
						if (global.debug) console.log('DEBUG: Scrolling down');

						el.scrollTop = el.scrollHeight - el.clientHeight;
					}
					, 0
				);
			}
		});
		
		return target;
	};	
});
