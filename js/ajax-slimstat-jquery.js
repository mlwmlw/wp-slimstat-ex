/* Ajax-SlimStat */

var SlimStat = {
	// menu loading
	loading: jQuery('<span id="slim_loading"><img src="'+SlimStatL10n.url+'/css/spinner.gif" alt="loading" /></span>'),
	// panel, module loading
	now_loading: jQuery('<div class="slim_nowloading" style="z-index:100;"><br /></div>'),
	// init. AJAX links
	initialize:function(target){
		if (!target)
			target = 'wp_slimstat';
		jQuery('#'+target+' a.ajax-request-link').click(function(e){
			var params = SlimStat.get_par(this.href);
			if ('request_module' == params['action']) {
				params['update'] = jQuery(this).parents('div.module:first').attr('id');
				if (!params['update'] || !params['moid']) return false;
			}
			SlimStat.ajax(jQuery.param(params));
			return false;
		});
		if (target == 'slim_main' || target == 'wp_slimstat') {
			if (jQuery('#slim_main input#fd_str').length) {
				var rangeInput = jQuery('#slim_main input#fd_str');
				var slim_main = jQuery('#slim_main');
				jQuery('#slim_main input#fd_str').daterangepicker({
//					arrows:true,
//					dateFormat:'m/d/yy',
					appendTo:'#slim_main',
					posX: rangeInput.offset().left - slim_main.offset().left, // x position
					posY: rangeInput.offset().top + rangeInput.outerHeight() - slim_main.offset().top, // y position
//					datepickerOptions: {maxDate: '0D', altField: '#fd_rng', altFormat: '@'},
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
//					onChange: function(){ var val = rangeInput.val().replace(' ', ''); rangeInput.val(val);},
//					rangeSplitter:'|',
//					presetRanges: [],
//					presets:{dateRange: 'Date Range'}
				});
			}
			jQuery('#slimstat_filter').submit(function() {// filter form
				SlimStat.filter(this);
				return false;
			});
		}
		if(typeof document.body.style.maxHeight === "undefined") {// lte IE6
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
		}
//		jQuery('#'+target+' .module')/*.sortable().resizable()*/.draggable();
	},
	// get parameters from URL
	get_par:function(url){
		url = url.split('?', 2);
		if (url.length < 2)
			return '';
		var qv = url[1];
		var vars = {update: 'slim_main', panel: '1', action: 'request_panel'}; 
		qv = jQuery.extend(vars, this.toQueryParams(qv));
		delete qv['page'];
		if (!qv['_wpnonce'])// append '_wpnonce' if not set.
			qv['_wpnonce'] = SlimStatL10n._wpnonce;
		return qv;
	},
	// form
	filter:function(form){
		if (jQuery(form).find("#fi").val() == '' && jQuery(form).find("#fd").val() == '')
			alert('No Filter Resource');
		else {
			var par = jQuery(form).formSerialize();
			this.ajax('update=slim_main&' + par);
		}
	},
	toQueryParams: function( s ) {
		var r = {}; if ( !s ) { return r; }
		s = s.replace(/^\?/,'').replace(/[;&]jQuery/,''); // remove any leading ?, remove any trailing & || ;
		var pp = s.split('&');
		for ( var i=0; i<pp.length; i++ ) {
			var p = pp[i].split('=');
			r[p[0]] = p[1];
		}
		return r;
	},
	// apply current menu class
	current:function(tid, qv, aborted){
		if (aborted || tid == 'slim_main') {
			jQuery('#slim_menu .slm_current').removeClass('slm_current');
			jQuery('#slim_menu #slm'+qv['panel']).addClass('slm_current').parent('li').addClass('slm_current');
		}
	},
	ajax:function(hash){
		if ( hash == '' ) return;
    if (typeof jQuery.historyAddHistory == 'function') {
			jQuery.historyLoad(decodeURIComponent(hash));
		} else 
			SlimLoading.start(hash);
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

var SlimLoading = {
	start:function(hash) {
		if ( hash == '' ) return;
		SlimLoading.aborted = SlimLoading.loadmodule = false;
		if (SlimLoading.doing_ajax)
			SlimLoading.abort();
		SlimLoading.qv = SlimStat.toQueryParams(hash);
		if (!SlimLoading.qv['update'] || !SlimLoading.qv['panel'] || !SlimLoading.qv['action']) {
			return;
		}
		SlimLoading.par = hash;
		SlimLoading.tid = SlimLoading.qv['update'];
		if (!jQuery('#'+SlimLoading.tid).length) {
			return SlimLoading.fix_par();
		}
		SlimLoading.request();
	},
	empty:function() {
		SlimStat.loading.remove();
		SlimStat.now_loading.remove();
		SlimLoading.tid = SlimLoading.par = SlimLoading.qv = null;
	},
	abort:function() {
		SlimLoading.aborted = true;
		SlimLoading.doing_ajax.abort();
		SlimLoading.empty();
	},
	fix_par: function(){
		jQuery.extend(SlimLoading.qv, {update: 'slim_main', action: 'request_panel'});
		return SlimStat.ajax(jQuery.param(SlimLoading.qv));
	},
	request:function() {
		var self = this;
		SlimLoading.doing_ajax = jQuery.ajax({
			async : true,
			type : 'GET',
			cache: true,
			dataType: 'html',
			scriptCharset : SlimStatL10n.charset,
			processData: false,
			url: SlimStatL10n.url + '/lib/slimstat-ajax.php',
			data: self.par,
			beforeSend: function(request){
				SlimStat.loading.appendTo('#slim_menu #slm'+self.qv['panel']).css('display', 'inline');
				if (self.tid == 'slim_main' && jQuery('#slim_main .module').length)
					jQuery('#slim_main .module').prepend(SlimStat.now_loading);
				else
					jQuery('#'+self.tid).prepend(SlimStat.now_loading);
			},
			error: function(request, status, error){
				jQuery('.ui-daterangepickercontain').remove();
				jQuery('#'+self.tid).html('ERROR');
			},
			success: function(data, status){
				jQuery('#'+self.tid).html(data);
				SlimStat.current(self.tid, self.qv, self.aborted);
				SlimStat.initialize(self.tid);
				// init sweetTitles
				if (typeof sweetTitles != 'undefined') {
					sweetTitles.tip.remove();
					sweetTitles.init();
				}
			},
			complete : function(request, status){
				self.doing_ajax = null;
				self.empty();
			}
		});
	}
};

jQuery(document).ready(function() {
	SlimStat.initialize();
	var SlimStatLoader = false;
	var current_hash_string = '';
	if (typeof jQuery.historyAddHistory == 'function') {
		var historyHandler = jQuery.historyInit(SlimLoading.start);
		current_hash_string = jQuery.historyCurrentHash.replace(/^#/, '');
	}
	if (current_hash_string == '') {
		current_hash_string = 'update=slim_main&panel=1&action=request_panel&_wpnonce='+SlimStatL10n._wpnonce;
		SlimStatLoader = SlimStat.ajax(current_hash_string);
	}
});

function slimstat_chart_onclick(index, url) {
	if (url) {
		var params = SlimStat.get_par(url);
		SlimStat.ajax(jQuery.param(params));
	}
}
