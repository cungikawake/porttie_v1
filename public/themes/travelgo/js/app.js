$(document).on('click', '[data-toggle-target]', function (e) {
	e.preventDefault();

	handleToggles($(this).data('toggle-target'));
});

function handleToggles(input) {
	if (!$.isArray(input)) {
		return handleToggles(input.split(' '));
	}

	$.each(input, function (i, item) {
		item = item.split(':');

		if (item.length === 1) {
			item.unshift('toggle');
		}

		toggles[item[0]].call(null, getElement(item[1]));
	});
}

function getElement(toggleName) {
	return $('[data-toggle-name~="' + toggleName + '"]');
}

var toggles = {
	toggle: function ($element) {
		$element.toggle();
	},
	hide: function ($element) {
		$element.hide();
	},
	show: function ($element) {
		$element.show();
	}
};

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
    (function ( $ ) {
    // Pass an object of key/vals to override
    $.fn.awesomeFormSerializer = function(overrides) {
        // Get the parameters as an array
        var newParams = this.serializeArray();

        for(var key in overrides) {
            var newVal = overrides[key]
            // Find and replace `content` if there
            for (index = 0; index < newParams.length; ++index) {
                if (newParams[index].name == key) {
                    newParams[index].value = newVal;
                    break;
                }
            }

            // Add it if it wasn't there
            if (index >= newParams.length) {
                newParams.push({
                    name: key,
                    value: newVal
                });
            }
        }

        // Convert to URL-encoded string
        return $.param(newParams);
    }
}( jQuery ));


$(document).on('turbolinks:render', function(){
    console.log("RENDER");
    main();
    Intercooler.processNodes($('body'));
    if($(document).data('ic-init')) return;
    $(document).data('ic-init', true);
});

$(document).on('turbolinks:click', function(){
    $(document).data('ic-init', null);
});

function main() {
    console.log("main");
    setUpScroll();

    $('.pop').webuiPopover();

    $('#postModal').on('show.bs.modal', function (e) {
        Intercooler.triggerRequest('#post-wrapper');
        Intercooler.processNodes($('#post-wrapper'));
    });

    $(document).off('click','#VirtualCoinPopup');
    $(document).on('click','#VirtualCoinPopup',function(e){
        console.log("----------------");
        e.preventDefault();
        HoldOn.open({
            theme: "sk-fading-circle",
            message: $(this).data('message'),
            backgroundColor: "#000",
            textColor: "white"
        });

        $.ajax({
            url: $(this).data('url') + '?' + $('.checkout-form').serialize(),
            success: function(data) {
                $("#virtualcoins-wrapper").html($(data).find("#virtualcoins-wrapper").html());
                $('#virtualcoinModal').modal('show');
                Intercooler.processNodes($('#virtualcoins-wrapper'));
                HoldOn.close();
            },
            complete: function(data) {
                HoldOn.close();
            }
        });
    });

    $(document).on('click','.InboxDirectMessage',function(e){
        e.preventDefault();
        $('#inboxModal').modal('show');
		var separator = "?";
		if($(this).data('url').indexOf("?") > -1) {
			separator = '&';
		}
		
        $.ajax({
            url: $(this).data('url') + separator + $('.checkout-form').serialize(),
            success: function(data) {
                $("#inbox-wrapper").html($(data).find("#inbox-main-outer").html());
                Intercooler.processNodes($('#inbox-wrapper'));
            },
            complete: function(data) {

            }
        });
    });
    alertify.reset();

    $('[data-toggle="tooltip"]').tooltip()
}

function setUpScroll() {
    if($('.infinite-scroll').length > 0) {
        $('ul.pagination').hide();
        $('.infinite-scroll').jscroll({
            autoTrigger: true,
            loadingHtml: '<img class="d-block mx-auto" src="/images/loader.svg" alt="Loading..." />',
            padding: 0,
            nextSelector: '.pagination li.active + li a',
            contentSelector: 'div.infinite-scroll',
            callback: function () {
                $('ul.pagination').remove();
            }
        });
    }
}

document.addEventListener("turbolinks:load", function() {
    delete lastCheck;
    console.log("turbolinks:load");
    main();

    if (typeof ga !== 'undefined' && jQuery.isFunction(ga)) {
        ga('send', 'pageview', window.location.pathname);
    }

    if($('div#review-rating').length)
        $('div#review-rating').raty({path: '/images/'});
});



(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
mixpanel.init("afe4c7ba8c8a56993b906ece1546bde0");
mixpanel.track('rentals');