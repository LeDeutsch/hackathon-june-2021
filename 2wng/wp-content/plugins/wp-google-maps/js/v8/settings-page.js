/**
 * @namespace WPGMZA
 * @module SettingsPage
 * @requires WPGMZA
 */

var $_GET = {};
if(document.location.toString().indexOf('?') !== -1) {
    var query = document.location
                   .toString()
                   // get the query string
                   .replace(/^.*?\?/, '')
                   // and remove any existing hash string (thanks, @vrijdenker)
                   .replace(/#.*$/, '')
                   .split('&');

    for(var wpgmza_i=0, wpgmza_l=query.length; wpgmza_i<wpgmza_l; wpgmza_i++) {
       var aux = decodeURIComponent(query[wpgmza_i]).split('=');
       $_GET[aux[0]] = aux[1];
    }
}
//get the 'index' query parameter



jQuery(function($) {
	
	WPGMZA.SettingsPage = function()
	{
		var self = this;
		
		this._keypressHistory = [];
		
		this.updateEngineSpecificControls();
		this.updateStorageControls();
		this.updateGDPRControls();
		
		//$("#wpgmza-developer-mode").hide();
		$(window).on("keypress", function(event) {
			self.onKeyPress(event);
		});


		

		
		jQuery('body').on('click',".wpgmza_destroy_data", function(e) {
			e.preventDefault();
			var ttype = jQuery(this).attr('danger');
			var warning = 'Are you sure?';
			if (ttype == 'wpgmza_destroy_all_data') { warning = 'Are you sure? This will delete ALL data and settings for WP Google Maps!'; }
			if (window.confirm(warning)) {
	            
				jQuery.ajax(WPGMZA.ajaxurl, {
		    		method: 'POST',
		    		data: {
		    			action: 'wpgmza_maps_settings_danger_zone_delete_data',
		    			type: ttype,
		    			nonce: wpgmza_dz_nonce
		    		},
		    		success: function(response, status, xhr) {
		    			if (ttype == 'wpgmza_destroy_all_data') {
		    				window.location.replace('admin.php?page=wp-google-maps-menu&action=welcome_page');
		    			} else if (ttype == 'wpgmza_reset_all_settings') {
		    				window.location.reload();
		    			}  else {
		    				alert('Complete.');
		    			}
		    			
	    			}
		    	});
	        }

			

		});

		
		$("select[name='wpgmza_maps_engine']").on("change", function(event) {
			self.updateEngineSpecificControls();
		});
		
		$('[name="wpgmza_settings_marker_pull"]').on('click', function(event) {
			self.updateStorageControls();
		});
		
		$("input[name='wpgmza_gdpr_require_consent_before_load'], input[name='wpgmza_gdpr_require_consent_before_vgm_submit'], input[name='wpgmza_gdpr_override_notice']").on("change", function(event) {
			self.updateGDPRControls();
		});

		$('select[name="tile_server_url"]').on('change', function(event){
			if($('select[name="tile_server_url"]').val() === "custom_override"){
				$('.wpgmza_tile_server_override_component').removeClass('wpgmza-hidden');
			} else {
				$('.wpgmza_tile_server_override_component').addClass('wpgmza-hidden');
			}
		});
		$('select[name="tile_server_url"]').trigger('change');
		
		jQuery('#wpgmza_flush_cache_btn').on('click', function(){
			jQuery(this).attr('disabled', 'disabled');
			WPGMZA.settingsPage.flushGeocodeCache();
		});
		
		$("#wpgmza-global-settings").tabs({
	       create: function(event, ui) {
	       		
	       		if (typeof $_GET['highlight'] !== 'undefined') {

					var elmnt = document.getElementById($_GET['highlight']);
					elmnt.classList.add('highlight-item');
					
					setTimeout(function() {
						elmnt.classList.add('highlight-item-step-2');	
					},1000);
					
					var yOffset = -100; 
					var y = elmnt.getBoundingClientRect().top + window.pageYOffset + yOffset;
					window.scrollTo({top: y, behavior: 'smooth'});
				
				}
	       }
	    });

	    $( "#wpgmza-global-setting" ).bind( "create", function(event, ui) {
				alert('now');
		       	
		});
		
		$("#wpgmza-global-settings fieldset").each(function(index, el) {
			
			var children = $(el).children(":not(legend)");
			children.wrapAll("<span class='settings-group'></span>");
			
		});
	}
	
	WPGMZA.SettingsPage.createInstance = function()
	{
		return new WPGMZA.SettingsPage();
	}
	
	/**
	 * Updates engine specific controls, hiding irrelevant controls (eg Google controls when OpenLayers is the selected engine) and showing relevant controls.
	 * @method
	 * @memberof WPGMZA.SettingsPage
	 */
	WPGMZA.SettingsPage.prototype.updateEngineSpecificControls = function()
	{
		var engine = $("select[name='wpgmza_maps_engine']").val();
		
		$("[data-required-maps-engine][data-required-maps-engine!='" + engine + "']").hide();
		$("[data-required-maps-engine='" + engine + "']").show();
	}
	
	WPGMZA.SettingsPage.prototype.updateStorageControls = function()
	{
		if($("input[name='wpgmza_settings_marker_pull'][value='1']").is(":checked"))
			$("#xml-cache-settings").show();
		else
			$("#xml-cache-settings").hide();
	}
	
	/**
	 * Updates the GDPR controls (eg visibility state) based on the selected GDPR settings
	 * @method
	 * @memberof WPGMZA.SettingsPage
	 */
	WPGMZA.SettingsPage.prototype.updateGDPRControls = function()
	{
		var showNoticeControls = $("input[name='wpgmza_gdpr_require_consent_before_load']").prop("checked");
		
		var vgmCheckbox = $("input[name='wpgmza_gdpr_require_consent_before_vgm_submit']");
		
		if(vgmCheckbox.length)
			showNoticeControls = showNoticeControls || vgmCheckbox.prop("checked");
		
		var showOverrideTextarea = showNoticeControls && $("input[name='wpgmza_gdpr_override_notice']").prop("checked");
		
		if(showNoticeControls)
		{
			$("#wpgmza-gdpr-compliance-notice").show("slow");
		}
		else
		{
			$("#wpgmza-gdpr-compliance-notice").hide("slow");
		}
		
		if(showOverrideTextarea)
		{
			$("#wpgmza_gdpr_override_notice_text").show("slow");
		}
		else
		{
			$("#wpgmza_gdpr_override_notice_text").hide("slow");
		}
	}

	/**
	 * Flushes the geocode cache
	 */
	WPGMZA.SettingsPage.prototype.flushGeocodeCache = function()
	{
		var OLGeocoder = new WPGMZA.OLGeocoder();
		OLGeocoder.clearCache(function(response){
			jQuery('#wpgmza_flush_cache_btn').removeAttr('disabled');
		});
	}
	
	WPGMZA.SettingsPage.prototype.onKeyPress = function(event)
	{
		var string;
		
		this._keypressHistory.push(event.key);
		
		if(this._keypressHistory.length > 9)
			this._keypressHistory = this._keypressHistory.slice(this._keypressHistory.length - 9);
		
		string = this._keypressHistory.join("");
		
		if(string == "codecabin" && !this._developerModeRevealed)
		{
			$("fieldset#wpgmza-developer-mode").show();
			this._developerModeRevealed = true;
		}
	}
	
	$(document).ready(function(event) {
		
		if(WPGMZA.getCurrentPage())
			WPGMZA.settingsPage = WPGMZA.SettingsPage.createInstance();
		
	});
	
});