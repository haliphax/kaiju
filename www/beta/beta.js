require(
	[
		'https://ajax.aspnetcdn.com/ajax/knockout/knockout-3.1.0.js'
		, 'https://code.jquery.com/jquery-1.10.2.min.js'
		, 'https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js'
	]
	, function(ko, jquery, _) {
		function resizeHandler()
		{
			var
				$log = $('.yk-log-block')
				, $stat = $('.yk-status-block')
			;

			if($(window).width() > 630) {
				$stat.insertBefore('.ui-content > .ui-grid-a > .ui-block-a:first');
				$log.insertBefore('.ui-content > .ui-grid-a > .ui-block-a:first');
			} else {
				$log.insertAfter('.ui-content > .ui-grid-a > .ui-block-a:last');
				$stat.insertAfter('.ui-content > .ui-grid-a > .ui-block-a:first');
			}
		}

		var viewModel = {
			character: {
				name: 'haliphax'
				, effects: [
					{ name: 'Some effect', id: 0 }
					, { name: 'Some other effect', id: 1 }
				]
				, stats: {
					hp: [65, 100]
					, mp: [75, 100]
					, ap: [75, 100]
					, xp: [75, 100]
				}
			}
		};

		var t = null;

		ko.applyBindings(viewModel);
		$.mobile.defaultPageTransition = 'none';
		$('#loading-screen').remove();

		$(window).on('resize', function(){
			clearTimeout(t);
			t = setTimeout(resizeHandler, 100);
		}).resize();
	}
);
