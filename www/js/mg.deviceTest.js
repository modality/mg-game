/*
 USE
 if (Modernizr.retina_resolution) {
 // high resolution device - Retina like
 }
 else {
 // low resolution device
 }
 */
var ratio,
    multiply_width,
    device;

Modernizr.addTest('retina_resolution', function() {
    try {
        if (window.devicePixelRatio > 1.25) {
            return true;
        }
    } catch (err) {
        return false;
    }
});

/**
 * Required modernizr lib
 */

if (Modernizr.retina_resolution) {
    ratio = 0.5;
    multiply_width = 2;
} else {
    ratio = 1;
    multiply_width = 1;
}

device_ratio = ratio;

    if ( $.browser.webkit ) {
        var width = window.innerWidth;
        if (width < screen.width) {
            width = screen.width;
        }

        // fix for Iphone app
        var ww = ( $(window).width() < window.screen.width ) ? $(window).width() : window.screen.width; //get proper width
        var mw = parseInt($(window).width(), 10) * parseInt(window.devicePixelRatio, 10); // min width of site

        //var ratio =  1 / parseInt(window.devicePixelRatio, 10); //ww / ww*window.devicePixelRatio; //calculate ratio
        if(device_ratio === 0.5){ //smaller than minimum size
            $('#Viewport').attr('content', 'initial-scale=' + ratio + ', maximum-scale=' + ratio + ', minimum-scale=' + ratio + ', user-scalable=yes, width=' + ww*multiply_width);
            $("body").css('width', width + 'px');
        } else{ //regular size
            $('#Viewport').attr('content', 'initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=no, width=' + screen.width / 2);
            $("body").css('width', screen.width + 'px');
        }
    }

    if(/Android|webOS/i.test(navigator.userAgent) && !/Nexus/i.test(navigator.userAgent) ) {
        var ww = ( $(window).width() < window.screen.width ) ? $(window).width() : window.screen.width; //get proper width
        //var ratio =  1 / parseInt(window.devicePixelRatio, 10);
        $('#Viewport').attr('content', 'initial-scale=' + ratio + ', maximum-scale=' + ratio + ', minimum-scale=1, user-scalable=yes, width=' + ww);
    }

/*if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
 var ww = ( $(window).width() < window.screen.width ) ? $(window).width() : window.screen.width; //get proper width
 var mw = 480; // min width of site
 var ratio =  ww / mw; //calculate ratio
 if( ww < mw){ //smaller than minimum size
 $('#Viewport').attr('content', 'initial-scale=' + ratio + ', maximum-scale=' + ratio + ', minimum-scale=' + ratio + ', user-scalable=yes, width=' + ww);
 }else{ //regular size
 $('#Viewport').attr('content', 'initial-scale=1.0, maximum-scale=2, minimum-scale=1.0, user-scalable=yes, width=' + ww);
 }
 }*/

$( document ).ready(function() {

    Modernizr.addTest('device_test', function() {
        // Target ipads as retina displays
        var screenPPI = $('#ppitest').outerWidth();

        if (BrowserDetect.browser === 'Other') {
            device = "application ";
        } else {
            device = "browser ";
        }
        if (screenPPI === 96 && ((screen.availWidth == '748' && screen.availHeight == '1024') || (screen.availHeight == '748' && screen.availWidth == '1024'))) {
            // Target Ipad applications
            device = device + "ipad";
            return true;
        } else if (screenPPI === 96 && ((screen.availWidth == '1004' && screen.availHeight == '768') || (screen.availHeight == '1004' && screen.availWidth == '768'))) {
            //Ipad 3rd generation && Ipad 4th generation && ipad air
            // target ipad mini 1st generation app
            //2048 × 1536 px color IPS LCD display at (264 ppi)
            device = device + "ipad";
            return true;
        } else if (screenPPI === 96 && ((screen.availWidth == '1024' && screen.availHeight == '768') || (screen.availHeight == '1024' && screen.availWidth == '768'))) {
        //Ipad 3rd generation && Ipad 4th generation && ipad air
        // target ipad mini 1st generation app
        //2048 × 1536 px color IPS LCD display at (264 ppi)
        // Ipad ios7 app
        device = device + "ipad";
        return true;
        }
    });

    if (Modernizr.device_test) {
        // device is recognized and we want to fake as non-retina display
        $("body").addClass(device);
        $('#Viewport').attr('content', 'initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=no, width=' + screen.availWidth);
        $("body").css('width', 'auto');
    }

    if (Modernizr.touch_device) {
        $("body").addClass('touch_device');
        is_touch_device = true;
    } else {
        $("body").addClass('no-touch_device');
        is_touch_device = false;
    }
    if (Modernizr.retina_resolution || $("body").hasClass('ipad')) {
        $("body").addClass('retina');
    } else {
        $("body").addClass('no-retina');
    }

    BrowserDetect.init();
    $("body").addClass(BrowserDetect.browser);

})

/*
 USE
 if (Modernizr.touch_device) {
 // code if this is touch device
 }

 Modernizr.touch is different
 if ( Modernizr.touch ) {
 // click
 } else {
 // mouseover
 }
 */
Modernizr.addTest('touch_device', function() {
    return !!('ontouchstart' in window) // works on most browsers
        || !!('onmsgesturechange' in window); // works on ie10
});

var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "Other";
        this.version = this.searchVersion(navigator.userAgent) ||       this.searchVersion(navigator.appVersion) || "Unknown";
    },

    searchString: function (data) {
        for (var i=0 ; i < data.length ; i++) {
            var dataString = data[i].string;
            this.versionSearchString = data[i].subString;

            if (dataString.indexOf(data[i].subString) != -1) {
                return data[i].identity;
            }
        }
    },

    searchVersion: function (dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },

    dataBrowser:
        [
            { string: navigator.userAgent, subString: "Chrome",  identity: "Chrome" },
            { string: navigator.userAgent, subString: "MSIE",    identity: "IE" },
            { string: navigator.userAgent, subString: "Firefox", identity: "FF" },
            { string: navigator.userAgent, subString: "Safari",  identity: "Safari" },
            { string: navigator.userAgent, subString: "Opera",   identity: "Opera" }
        ]

};

$.fn.centerHorizontal = function () {
    this.css("position", "absolute");
    if (this.outerWidth() < $(window).width()) {
        this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
        return this;
    }
};

$.fn.centerVertival = function () {
    this.css("position", "absolute");
    if (this.outerWidth() < $(window).width()) {
        this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
        return this;
    }
};