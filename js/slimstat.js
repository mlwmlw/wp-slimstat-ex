var SlimStat = {
	initialize:function(target){
		if (!target)
			target = 'wp_slimstat';
		if (target == 'slim_main' || target == 'wp_slimstat') {
			if (jQuery('#slim_main input#fd_str').length) {
				var rangeInput = jQuery('#slim_main input#fd_str');
				var slim_main = jQuery('#slim_main');
				jQuery('#slim_main input#fd_str').daterangepicker({
					appendTo:'#slim_main',
					posX: rangeInput.offset().left - slim_main.offset().left, // x position
					posY: rangeInput.offset().top + rangeInput.outerHeight() - slim_main.offset().top, // y position
					onChange:function(){
						var val = rangeInput.val().split('-');
						if (!val[0] && !val[1])
							return;
						if (!val[0])
							val[0] = Date.parse('-15years');
						val[0] = parseFloat((new Date(val[0])).getTime()/1000);
						if (!val[1])
							val[1] = val[0] + 86400;
						else
							val[1] = parseFloat((new Date(val[1])).getTime()/1000);
						jQuery('#fd').val(val[0] + '|' + val[1]);
					}
				});
			}
		}
		jQuery('#'+target+' ul.module-tabs li.first-item').hoverIntent({
			over:function(e){
				jQuery(this).find('ul:first').css({display:'block'});
			},
			out:function(){
				jQuery(this).find('ul').css('display', 'none');
			},
			timeout: 220,
			sensitivity: 8,
			interval: 100
		});
	},
	toggleAllSubs:function(me){
		var toggler = jQuery(me);
		var expanded = toggler.html() == 'collapse';
		toggler.parents('table:first').find('tr.' + (expanded ? 'subcons' : 'collapsed-subcons'))
			.removeClass(expanded ? 'subcons' : 'collapsed-subcons')
			.addClass(expanded ? 'collapsed-subcons' : 'subcons')
			.prev('tr')[expanded ? 'addClass' : 'removeClass']('collapsed');
		toggler.html(expanded ? 'expand' : 'collapse');
	},
	toggleSub:function(me){
		jQuery(me).toggleClass('collapsed').next().toggleClass('collapsed-subcons');
	}
};

jQuery(document).ready(function() {
	SlimStat.initialize();
});