/**
 * @namespace WPGMZA
 * @module StoreLocator
 * @requires WPGMZA.EventDispatcher
 */
jQuery(function($) {
	
	WPGMZA.StoreLocator = function(map, element)
	{
		var self = this;
		
		WPGMZA.EventDispatcher.call(this);
		
		this._center = null;
		
		this.map = map;
		this.element = element;
		this.state = WPGMZA.StoreLocator.STATE_INITIAL;

		this.distanceUnits = this.map.settings.store_locator_distance;
		
		this.addressInput = WPGMZA.AddressInput.createInstance(this.addressElement, this.map);
		
		$(element).find(".wpgmza-not-found-msg").hide();
		
		// Default radius
		if(this.radiusElement && this.map.settings.wpgmza_store_locator_default_radius){
			if(this.radiusElement.find("option[value='" + this.map.settings.wpgmza_store_locator_default_radius + "']").length > 0){
				this.radiusElement.val(this.map.settings.wpgmza_store_locator_default_radius);
			}
		}
		
		// TODO: This will be moved into this module instead of listening to the map event
		this.map.on("storelocatorgeocodecomplete", function(event) {
			self.onGeocodeComplete(event);
		});
		
		this.map.on("init", function(event) {
			
			self.map.markerFilter.on("filteringcomplete", function(event) {
				self.onFilteringComplete(event);
			});
			
			// Workaround for improper inheritance. Because ModernStoreLocator was written in v7, before this StoreLocator module, the ModernStoreLocator effectively re-arranges the store locators HTML. At some point, ModernStoreLocator should properly inherit from StoreLocator. For now, we'll just initialise this here to get the right look and feel. This is not ideal but it will work.
			if(typeof self.map.settings.store_locator_style === 'undefined' || self.map.settings.store_locator_style == "modern" || WPGMZA.settings.user_interface_style === 'modern'){
				if(WPGMZA.settings.user_interface_style === 'default' || WPGMZA.settings.user_interface_style == 'modern' || WPGMZA.settings.user_interface_style == 'legacy'){
					self.legacyModernAdapter = WPGMZA.ModernStoreLocator.createInstance(map.id);
				}
			}
			
		});

		// Legacy store locator buttons
		$(document.body).on("click", ".wpgmza_sl_search_button_" + map.id + ", [data-map-id='" + map.id + "'] .wpgmza_sl_search_button", function(event) {
			self.onSearch(event);
		});
		
		$(document.body).on("click", ".wpgmza_sl_reset_button_" + map.id + ", [data-map-id='" + map.id + "'] .wpgmza_sl_reset_button_div", function(event) {
			self.onReset(event);
		});
		
		// Enter listener
		$(this.addressElement).on("keypress", function(event) {
			
			if(event.which == 13)
				self.onSearch(event);
			
		});
	}
	
	WPGMZA.StoreLocator.prototype = Object.create(WPGMZA.EventDispatcher.prototype);
	WPGMZA.StoreLocator.prototype.constructor = WPGMZA.StoreLocator;
	
	WPGMZA.StoreLocator.STATE_INITIAL		= "initial";
	WPGMZA.StoreLocator.STATE_APPLIED		= "applied";
	
	WPGMZA.StoreLocator.createInstance = function(map, element)
	{
		return new WPGMZA.StoreLocator(map, element);
	}
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "address", {
		"get": function() {
			return $(this.addressElement).val();
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "addressElement", {
		"get": function() {
			
			if(this.legacyModernAdapter)
				return $(this.legacyModernAdapter.element).find("input.wpgmza-address")[0];
			
			return $(this.element).find("input.wpgmza-address")[0];
			
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "countryRestriction", {
		"get": function() {
			return this.map.settings.wpgmza_store_locator_restrict;
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "radiusElement", {
		"get": function() {
			return $("#radiusSelect, #radiusSelect_" + this.map.id);
		}
	});		
		
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "radius", {
		"get": function() {
			return parseFloat(this.radiusElement.val());
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "center", {
		"get": function() {
			return this._center;
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "bounds", {
		"get": function() {
			return this._bounds;
		}
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "marker", {
		
		"get": function() {
			


			if(this.map.settings.store_locator_bounce != 1)
				return null;
			
			if(this._marker)
				return this._marker;
			
			var options = {
				visible: false
			};
			
			this._marker = WPGMZA.Marker.createInstance(options);
			this._marker.disableInfoWindow = true;
			this._marker.isFilterable = false;
			
			this._marker.setAnimation(WPGMZA.Marker.ANIMATION_BOUNCE);
			
			return this._marker;
			
		}
		
	});
	
	Object.defineProperty(WPGMZA.StoreLocator.prototype, "circle", {
		
		"get": function() {
			
			if(this._circle)
				return this._circle;
			
			if(this.map.settings.wpgmza_store_locator_radius_style == "modern" && !WPGMZA.isDeviceiOS())
			{
				this._circle = WPGMZA.ModernStoreLocatorCircle.createInstance(this.map.id);
				this._circle.settings.color = this.circleStrokeColor;
			}
			else
			{
				this._circle = WPGMZA.Circle.createInstance({
					strokeColor:	"#ff0000",
					strokeOpacity:	"0.25",
					strokeWeight:	2,
					fillColor:		"#ff0000",
					fillOpacity:	"0.15",
					visible:		false,
					clickable:      false,
					center: new WPGMZA.LatLng()
				});
			}
			
			return this._circle;
			
		}
		
	});
	
	WPGMZA.StoreLocator.prototype.onGeocodeComplete = function(event)
	{
		if(!event.results || !event.results.length)
		{
			this._center = null;
			this._bounds = null;

			return;
		}
		else
		{
			this._center = new WPGMZA.LatLng( event.results[0].latLng );
			this._bounds = new WPGMZA.LatLngBounds( event.results[0].bounds );
		}
		
		this.map.markerFilter.update({}, this);
	}
	
	WPGMZA.StoreLocator.prototype.onSearch = function(event)
	{
		var self = this;
		
		this.state = WPGMZA.StoreLocator.STATE_APPLIED;
		
		// NB: Moved in from legacy searchLocations
		if(!this.address || !this.address.length)
		{
			this.addressElement.focus();
			return false;
		}
		
		if((typeof this.map.settings.store_locator_style !== 'undefined' && this.map.settings.store_locator_style !== "modern") && WPGMZA.settings.user_interface_style !== 'modern' && WPGMZA.settings.user_interface_style === 'default'){
			WPGMZA.animateScroll(this.map.element);
		}

		$(this.element).find(".wpgmza-not-found-msg").hide();

		function callback(results, status)
		{
			self.map.trigger({
				type:		"storelocatorgeocodecomplete",
				results:	results,
				status:		status
			});
		}
		
		if(!WPGMZA.LatLng.isLatLngString(this.address))
		{
			var geocoder = WPGMZA.Geocoder.createInstance();
			var options = {
				address: this.address
			};
			
			if(this.countryRestriction)
				options.country = this.countryRestriction;
			
			geocoder.geocode(options, function(results, status) {
				
				if(status == WPGMZA.Geocoder.SUCCESS)
					callback(results, status);
				else{
					
					alert(WPGMZA.localized_strings.address_not_found);
				}
				 
			});
		}
		else
			callback([WPGMZA.LatLng.fromString(this.address)], WPGMZA.Geocoder.SUCCESS);
		
		return true;
	}
	
	WPGMZA.StoreLocator.prototype.onReset = function(event)
	{
		this.state = WPGMZA.StoreLocator.STATE_INITIAL;
		
		this._center = null;
		this._bounds = null;
		
		// NB: Moved in from legacy resetLocations
		this.map.setZoom(this.map.settings.map_start_zoom);

		$(this.element).find(".wpgmza-not-found-msg").hide();
		
		if(this.circle)
			this.circle.setVisible(false);
		
		if(this.marker && this.marker.map)
			this.map.removeMarker(this.marker);
		
		this.map.markerFilter.update({}, this);
	}
	
	WPGMZA.StoreLocator.prototype.getFilteringParameters = function()
	{
		if(!this.center)
			return {};
		
		return {
			center: this.center,
			radius: this.radius
		};
	}
	
	WPGMZA.StoreLocator.prototype.getZoomFromRadius = function(radius){
		if(this.distanceUnits == WPGMZA.Distance.MILES)
			radius *= WPGMZA.Distance.KILOMETERS_PER_MILE;
		
		return Math.round(14 - Math.log(radius) / Math.LN2);
	}
	
	WPGMZA.StoreLocator.prototype.onFilteringComplete = function(event)
	{
		var params = event.filteringParams;
		var marker = this.marker;

		if(marker)
			marker.setVisible(false);
		

		// Center point marker
		if(params.center)
		{
			this.map.setCenter(params.center);
			
			if(marker)
			{
				marker.setPosition(params.center);
				marker.setVisible(true);
				
				if(marker.map != this.map)
					this.map.addMarker(marker);
			}
		}
		
		// Set zoom level
		if(params.radius){
			this.map.setZoom(this.getZoomFromRadius(params.radius));
		}
		
		// Display circle
		var circle = this.circle;
		
		if(circle)
		{
			circle.setVisible(false);

			var factor = (this.distanceUnits == WPGMZA.Distance.MILES ? WPGMZA.Distance.KILOMETERS_PER_MILE : 1.0);
			
			if(params.center && params.radius)
			{
				circle.setRadius(params.radius * factor);
				circle.setCenter(params.center);
				circle.setVisible(true);
				
				if(!(circle instanceof WPGMZA.ModernStoreLocatorCircle) && circle.map != this.map)
					this.map.addCircle(circle);
			}
			
			if(circle instanceof WPGMZA.ModernStoreLocatorCircle)
				circle.settings.radiusString = this.radius;
		}
		
		if(event.filteredMarkers.length == 0){
			if($(this.element).find('.wpgmza-no-results').length > 0 && WPGMZA.settings.user_interface_style === 'legacy'){
				$(this.element).find('.wpgmza-no-results').show();
			} else {
				alert(this.map.settings.store_locator_not_found_message ? this.map.settings.store_locator_not_found_message : WPGMZA.localized_strings.zero_results);
			}
		}
	}
	
});