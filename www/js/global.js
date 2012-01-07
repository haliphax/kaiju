var kaiju_globals = [];

function touchHandler(event)
{
    var touches = event.changedTouches,
        first = touches[0],
        type = "";
    switch(event.type)
    {
        case "touchstart": type = "mousedown"; break;
        case "touchmove":  type="mousemove"; break;        
        case "touchend":   type="mouseup"; break;
        default: return;
    }

    var simulatedEvent = document.createEvent("MouseEvent");
    simulatedEvent.initMouseEvent(type, true, true, window, 1, 
                              first.screenX, first.screenY, 
                              first.clientX, first.clientY, false, 
                              false, false, false, 0/*left*/, null);

    if(! ($(first.target).hasClass('ui-dialog-titlebar')
        || $(first.target).hasClass('ui-dialog-title')))
    {
        return;
    }

    first.target.dispatchEvent(simulatedEvent);
    event.preventDefault();
}

function touchHandler_init() 
{
	if(document.addEventListener)
	{
		document.addEventListener("touchstart", touchHandler, true);
		document.addEventListener("touchmove", touchHandler, true);
		document.addEventListener("touchend", touchHandler, true);
		document.addEventListener("touchcancel", touchHandler, true);    
	}
	else
	{
		document.attachEvent("touchstart", touchHandler);
		document.attachEvent("touchmove", touchHandler);
		document.attachEvent("touchend", touchHandler);
		document.attachEvent("touchcancel", touchHandler);
	}
}

$(function()
{
	kaiju_globals.base_url = document.getElementById('base_url').innerHTML;
	if(! /\/$/.exec(kaiju_globals.base_url))
		kaiju_globals.base_url += '/';
	kaiju_globals.base_url_path = kaiju_globals.base_url.replace(/\/index\.php/i, '');
	touchHandler_init();
	$('.button')
		.addClass('ui-state-default')
		.addClass('ui-corner-all')
		.live('mouseover', function()
		{
			$(this)
				.addClass('ui-state-hover')
				.addClass('ui-state-focus');
		}).live('mouseout', function()
		{
			$(this)
				.removeClass('ui-state-hover')
				.removeClass('ui-state-focus');
		});	
	$('.button').live('click', function()
	{
		$('#' + $(this).attr('dialog')).dialog('open');
	});
	$('#btn_logout').click(function() { window.location = document.getElementById('base_url').innerHTML + 'login';	});
	$('#btn_characters').click(function() { window.location = document.getElementById('base_url').innerHTML + 'characters'; });
	$('#btn_account').click(function() { window.location = document.getElementById('base_url').innerHTML + 'account'; });
	$('#btn_return').click(function() { window.location = document.getElementById('base_url').innerHTML + 'game'; });
	$('.accordion').accordion({ autoHeight: false, clearType: true, collapsible: true, animated: false });
	$('.dialog').dialog({ maxHeight: 600 }).dialog('close');
	$('.tabs').tabs();
	$('.progbar').progressbar();
});
