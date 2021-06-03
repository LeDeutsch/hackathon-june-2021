/**
 * @namespace WPGMZA
 * @module AdminFeatureDataTable
 * @requires WPGMZA.DataTable
 */
jQuery(function($) {
	
	WPGMZA.AdminFeatureDataTable = function(element)
	{
		var self = this;

		this.allSelected = false;
		
		WPGMZA.DataTable.call(this, element);
		
		$(element).on("click", ".wpgmza.bulk_delete", function(event) {
			self.onBulkDelete(event);
		});

		$(element).on("click", ".wpgmza.select_all_markers", function(event) {
			self.onSelectAll(event);
		});
		
		// TODO: Move to dedicated marker class, or center feature ID instead
		$(element).on("click", "[data-center-marker-id]",
		function(event) {
			self.onCenterMarker(event);
		});
	}
	
	WPGMZA.extend(WPGMZA.AdminFeatureDataTable, WPGMZA.DataTable);
	
	Object.defineProperty(WPGMZA.AdminFeatureDataTable.prototype, "featureType", {
		
		"get": function() {
			return $(this.element).attr("data-wpgmza-feature-type");
		}
		
	});
	
	Object.defineProperty(WPGMZA.AdminFeatureDataTable.prototype, "featurePanel", {
		
		"get": function() {
			return WPGMZA.mapEditPage[this.featureType + "Panel"];
		}
		
	});
	
	WPGMZA.AdminFeatureDataTable.prototype.getDataTableSettings = function()
	{
		var self = this;
		var options = WPGMZA.DataTable.prototype.getDataTableSettings.call(this);
		
		options.createdRow = function(row, data, index)
		{
			var meta = self.lastResponse.meta[index];
			row.wpgmzaFeatureData = meta;
		}
		
		return options;
	}
	
	WPGMZA.AdminFeatureDataTable.prototype.onBulkDelete = function(event)
	{
		var self = this;
		var ids = [];
		var map = WPGMZA.maps[0];
		var plural = this.featureType + "s";
		
		$(this.element).find("input[name='mark']:checked").each(function(index, el) {
			var row = $(el).closest("tr")[0];
			ids.push(row.wpgmzaFeatureData.id);
		});
		
		ids.forEach(function(marker_id) {
			var marker = map.getMarkerByID(marker_id);
			
			if(marker)
				map.removeMarker(marker);
		});
		
		WPGMZA.restAPI.call("/" + plural + "/", {
			method: "DELETE",
			data: {
				ids: ids
			},
			complete: function() {
				self.reload();
			}
		});
	}

	WPGMZA.AdminFeatureDataTable.prototype.onSelectAll = function(event){
		this.allSelected = !this.allSelected;

		var self = this;

		$(this.element).find("input[name='mark']").each(function(){
			if(self.allSelected){
				$(this).prop("checked", true);
			} else {
				$(this).prop("checked", false);
			}
		});
	}
	
	// TODO: Move to dedicated marker class, or center feature ID instead
	WPGMZA.AdminFeatureDataTable.prototype.onCenterMarker = function(event)
	{
		var id;

		//Check if we have selected the center on marker button or called this function elsewhere 
		if(event.currentTarget == undefined)
		{
			id = event;
		}
		else{
			id = $(event.currentTarget).attr("data-center-marker-id");
		}

		var marker = WPGMZA.mapEditPage.map.getMarkerByID(id);
		
		if(marker){
			var latLng = new WPGMZA.LatLng({
				lat: marker.lat,
				lng: marker.lng
			});
			
			//Set a static zoom level
			var zoom_value = 6;
			WPGMZA.mapEditPage.map.setCenter(latLng);
			//WPGMZA.mapEditPage.map.setZoom(zoom_value);
			WPGMZA.animateScroll("#wpgmaps_tabs_markers");
		}


	}
	
});