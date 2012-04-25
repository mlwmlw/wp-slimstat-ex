/*
Sweet Titles (c) Creative Commons 2005
http://creativecommons.org/licenses/by-sa/2.5/
Author: Dustin Diaz | http://www.dustindiaz.com
*/
var sweetTitles = { 
	xCord : 0,				// @Number: x pixel value of current cursor position
	yCord : 0,				// @Number: y pixel value of current cursor position
	tipElements : ['a','abbr','acronym', 'span.slim_button'],	// @Array: Allowable elements that can have the toolTip
	obj : Object,			// @Element: That of which you're hovering over
	tip : Object,			// @Element: The actual toolTip itself
	active : 0,				// @Number: 0: Not Active || 1: Active
	delay : 350,			// @Number: delay value in ms
	init : function() {
		this.tip = jQuery('<div id="toolTip"></div>').css({opacity: '0.8'}).appendTo(document.body).hide();
		jQuery(this.tipElements.join(', ')).each(function(e){
			var el = jQuery(this);
			if (!((el.attr('title') == '') || (typeof(el.attr('title')) == 'undefined'))) {
				el.attr('tip', el.attr('title')).removeAttr('title').hover(sweetTitles.tipOver, sweetTitles.tipOut);
			}
		});
	},
	updateXY : function(e) {
		if ( document.captureEvents ) {
			document.captureEvents(Event.MOUSEMOVE);
			sweetTitles.xCord = e.pageX;
			sweetTitles.yCord = e.pageY;
		} else if ( window.event.clientX ) {
			sweetTitles.xCord = window.event.clientX+document.documentElement.scrollLeft;
			sweetTitles.yCord = window.event.clientY+document.documentElement.scrollTop;
		}
	},
	tipOut: function() {
		if ( window.tID ) {
			clearTimeout(tID);
		}
		sweetTitles.tip.hide();
	},
	tipOver : function() {
		sweetTitles.obj = this;
		jQuery(document.body).bind('mousemove', sweetTitles.updateXY);
		tID = window.setTimeout("sweetTitles.tipShow()",250)
	},
	tipShow : function() {		
		var tp = parseInt(Number(this.yCord)+15);
		var lt = parseInt(Number(this.xCord)+10);
		var anch = this.obj;//checkNode();
		var addy = '';
		var access = '';
		if (anch.href)
			addy = (anch.href.length > 62 ? anch.href.toString().substring(0,62)+"..." : anch.href);
		var em = (addy == '' && access == '') ? '' : "<em>"+access+addy+"</em>";
//		access = ( anch.accessKey ? ' <span>[alt+'+anch.accessKey+']</span> ' : '' );
		this.tip.html("<p>"+anch.getAttribute('tip')+em+"</p>");
		if ( parseInt(document.documentElement.clientWidth+document.documentElement.scrollLeft) < parseInt(this.tip.outerWidth()+lt) ) {
			this.tip.css({left : parseInt(lt-(this.tip.outerWidth()+10))+'px'});
		} else {
			this.tip.css({left : lt+'px'});
		}
		if ( parseInt(document.documentElement.clientHeight+document.documentElement.scrollTop) < parseInt(this.tip.outerHeight()+tp) ) {
			this.tip.css({top : parseInt(tp-(this.tip.outerHeight()+10))+'px'});
		} else {
			this.tip.css({top : tp+'px'});
		}
		this.tip.fadeIn('normal');
		jQuery(document.body).unbind('mousemove', sweetTitles.updateXY);
	}
};

jQuery(document).ready(function(){sweetTitles.init()});
