/**
 * @namespace WPGMZA
 * @module FeaturePanel
 * @requires WPGMZA.EventDispatcher
 */
jQuery(function($) {
	
	WPGMZA.FeaturePanel = function(element, mapEditPage)
	{
		var self = this;
		
		WPGMZA.EventDispatcher.apply(this, arguments);
		
		this.map = mapEditPage.map;
		this.drawingManager = mapEditPage.drawingManager;
		
		this.feature = null;
		
		this.element = element;

		this.initDefaults();
		this.setMode(WPGMZA.FeaturePanel.MODE_ADD);
		
		this.drawingInstructionsElement = $(this.element).find(".wpgmza-feature-drawing-instructions");
		this.drawingInstructionsElement.detach();
		
		this.editingInstructionsElement = $(this.element).find(".wpgmza-feature-editing-instructions");
		this.editingInstructionsElement.detach();
		
		$("#wpgmaps_tabs_markers").on("tabsactivate", function(event, ui) {
			if($.contains(ui.newPanel[0], self.element[0]))
				self.onTabActivated(event);
		});
		
		$("#wpgmaps_tabs_markers").on("tabsactivate", function(event, ui) {
			if($.contains(ui.oldPanel[0], self.element[0]))
				self.onTabDeactivated(event);
		});
		
		// NB: Removed to get styling closer
		/*$(element).closest(".wpgmza-accordion").find("h3[data-add-caption]").on("click", function(event) {
			if(self.mode == "add")
				self.onAddFeature(event);
		});*/

		$(document.body).on("click", "[data-edit-" + this.featureType + "-id]", function(event) {
			self.onEditFeature(event);
		});
		
		$(document.body).on("click", "[data-delete-" + this.featureType + "-id]", function(event) {
			self.onDeleteFeature(event);
		});
		
		$(this.element).find(".wpgmza-save-feature").on("click", function(event) {
			self.onSave(event);
		});
		
		this.drawingManager.on(self.drawingManagerCompleteEvent, function(event) {
			self.onDrawingComplete(event);
		});
		
		this.drawingManager.on("drawingmodechanged", function(event) {
			self.onDrawingModeChanged(event);
		});
		
		$(this.element).on("change input", function(event) {
			self.onPropertyChanged(event);
		});
	}
	
	WPGMZA.extend(WPGMZA.FeaturePanel, WPGMZA.EventDispatcher);
	
	WPGMZA.FeaturePanel.MODE_ADD			= "add";
	WPGMZA.FeaturePanel.MODE_EDIT			= "edit";
	
	WPGMZA.FeaturePanel.prevEditableFeature = null;
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "featureType", {
		
		"get": function() {
			return $(this.element).attr("data-wpgmza-feature-type");
		}
		
	});
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "drawingManagerCompleteEvent", {
		
		"get": function() {
			return this.featureType + "complete";
		}
		
	});
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "featureDataTable", {
		
		"get": function() {
			return $("[data-wpgmza-datatable][data-wpgmza-feature-type='" + this.featureType + "']")[0].wpgmzaDataTable;
		}
		
	});
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "featureAccordion", {
		
		"get": function() {
			return $(this.element).closest(".wpgmza-accordion");
		}
		
	});
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "map", {
		
		"get": function() {
			return WPGMZA.mapEditPage.map;
		}
		
	});
	
	Object.defineProperty(WPGMZA.FeaturePanel.prototype, "mode", {
		
		"get": function() {
			return this._mode;
		}
		
	});
	
	WPGMZA.FeaturePanel.prototype.initPreloader = function()
	{
		if(this.preloader)
			return;
		
		this.preloader = $(WPGMZA.preloaderHTML);
		this.preloader.hide();
		
		$(this.element).append(this.preloader);
	}
	
	WPGMZA.FeaturePanel.prototype.initDataTable = function()
	{
		var el = $(this.element).find("[data-wpgmza-datatable][data-wpgmza-rest-api-route]");
		
		this[this.featureType + "AdminDataTable"] = new WPGMZA.AdminFeatureDataTable( el );
	}
	
	WPGMZA.FeaturePanel.prototype.initDefaults = function()
	{
		$(this.element).find("[data-ajax-name]:not([type='radio'])").each(function(index, el) {
			
			var val = $(el).val();
			
			if(!val)
				return;
			
			$(el).attr("data-default-value", val);
			
		});
	}
	
	WPGMZA.FeaturePanel.prototype.setCaptionType = function(type, id)
	{
		var args = arguments;
		var icons = {
			add: "fa-plus-circle",
			save: "fa-pencil-square-o"
		};
		
		switch(type)
		{
			case WPGMZA.FeaturePanel.MODE_ADD:
			case WPGMZA.FeaturePanel.MODE_EDIT:
			
				this.featureAccordion.find("[data-add-caption][data-edit-caption]").each(function(index, el) {
					
					var text = $(el).attr("data-" + type + "-caption");
					var icon = $(el).find("i.fa");
					
					if(id)
						text += " " + id;
				
					$(el).text(text);
					
					if(icon.length)
					{
						// Need to recreate the icon as text() will have wiped it out
						icon = $("<i class='fa' aria-hidden='true'></i>");
						
						icon.addClass(icons[type]);
						
						$(el).prepend(" ");
						$(el).prepend(icon);
					}
				
				});
				
				break;
				
			default:
				throw new Error("Invalid type");
				break;
		}
	}
	
	WPGMZA.FeaturePanel.prototype.setMode = function(type, id)
	{
		this._mode = type;
		this.setCaptionType(type, id);
	}
	
	WPGMZA.FeaturePanel.prototype.setTargetFeature = function(feature)
	{
		var self = this;

		// TODO: Implement fitBounds for all features
		//var bounds = feature.getBounds();
		//map.fitBounds(bounds);
		

		if(WPGMZA.FeaturePanel.prevEditableFeature) {
			var prev = WPGMZA.FeaturePanel.prevEditableFeature;
			
			prev.setEditable(false);
			prev.setDraggable(false);

			prev.off("change");
		}
		if(feature) {
			feature.setEditable(true);
			feature.setDraggable(true);

			feature.on("change", function(event) {
				self.onFeatureChanged(event);
			});
			this.setMode(WPGMZA.FeaturePanel.MODE_EDIT);
			this.drawingManager.setDrawingMode(WPGMZA.DrawingManager.MODE_NONE);
			
			this.showInstructions();
		}
		else {
			this.setMode(WPGMZA.FeaturePanel.MODE_ADD);
		}
		this.feature = WPGMZA.FeaturePanel.prevEditableFeature = feature;
	}
	
	WPGMZA.FeaturePanel.prototype.reset = function()
	{
		$(this.element).find("[data-ajax-name]:not([data-ajax-name='map_id']):not([type='color']):not([type='checkbox']):not([type='radio'])").val("");
		$(this.element).find("select[data-ajax-name]>option:first-child").prop("selected", true);
		$(this.element).find("[data-ajax-name='id']").val("-1");
		
		$(this.element).find("input[type='checkbox']").prop("checked", false);
		
		if(tinyMCE.get("wpgmza-description-editor"))
			tinyMCE.get("wpgmza-description-editor").setContent("");
		else
			$("#wpgmza-description-editor").val("");

		$('#wpgmza-description-editor').val("");
		
		this.showPreloader(false);
		this.setMode(WPGMZA.FeaturePanel.MODE_ADD);
		
		$(this.element).find("[data-ajax-name][data-default-value]").each(function(index, el) {
			
			$(el).val( $(el).data("default-value") );
			
		});
	}
	
	WPGMZA.FeaturePanel.prototype.select = function(arg) {
		var id, expectedBaseClass, self = this;
		
		this.reset();
		
		if($.isNumeric(arg))
			id = arg;
		else
		{
			expectedBaseClass = WPGMZA[ WPGMZA.capitalizeWords(this.featureType) ];
			
			if(!(feature instanceof expectedBaseClass))
				throw new Error("Invalid feature type for this panel");
			
			id = arg.id;
		}
		
		this.showPreloader(true);
		
		WPGMZA.animateScroll($(".wpgmza_map"));
		
		WPGMZA.restAPI.call("/" + this.featureType + "s/" + id + "?skip_cache=1", {
			
			success: function(data, status, xhr) {
				
				var functionSuffix 		= WPGMZA.capitalizeWords(self.featureType);
				var getByIDFunction		= "get" + functionSuffix + "ByID";
				var feature				= self.map[getByIDFunction](id);
				
				self.populate(data);
				self.showPreloader(false);
				self.setMode(WPGMZA.FeaturePanel.MODE_EDIT, id);
				
				self.setTargetFeature(feature);
				
			}
			
		});
	}
	
	WPGMZA.FeaturePanel.prototype.showPreloader = function(show)
	{
		this.initPreloader();
		
		if(arguments.length == 0 || show)
		{
			this.preloader.fadeIn();
			this.element.addClass("wpgmza-loading");
		}
		else
		{
			this.preloader.fadeOut();
			this.element.removeClass("wpgmza-loading");
		}
	}
	
	WPGMZA.FeaturePanel.prototype.populate = function(data)
	{
		var value, target, name;
		
		for(name in data)
		{
			target = $(this.element).find("[data-ajax-name='" + name + "']");
			value = data[name];
			
			switch((target.attr("type") || "").toLowerCase())
			{
				case "checkbox":
				case "radio":
				
					target.prop("checked", data[name] == 1);
				
					break;
				
				case "color":
				
					// NB: Account for legacy color format
					if(!value.match(/^#/))
						value = "#" + value;
					
				default:
				
					if(typeof value == "object")
						value = JSON.stringify(value);
				
					$(this.element).find("[data-ajax-name='" + name + "']:not(select)").val(value);
					
					$(this.element).find("select[data-ajax-name='" + name + "']").each(function(index, el) {
						
						if(typeof value == "string" && data[name].length == 0)
							return;
						
						$(el).val(value);
						
					});
				
					break;
			}
		}
	}
	
	WPGMZA.FeaturePanel.prototype.serializeFormData = function()
	{
		var fields = $(this.element).find("[data-ajax-name]");
		var data = {};
		
		fields.each(function(index, el) {
			
			var type = "text";
			if($(el).attr("type"))
				type = $(el).attr("type").toLowerCase();
			
			switch(type)
			{
				case "checkbox":
					data[$(el).attr("data-ajax-name")] = $(el).prop("checked") ? 1 : 0;
					break;
				
				case "radio":
					if($(el).prop("checked"))
						data[$(el).attr("data-ajax-name")] = $(el).val();
					break;
					
				default:
					data[$(el).attr("data-ajax-name")] = $(el).val()
					break;
			}
			
		});
		
		return data;
	}
	
	WPGMZA.FeaturePanel.prototype.discardChanges = function() {
		if(!this.feature)
			return;
			
		var feature = this.feature;
		
		this.setTargetFeature(null);
		
		if(feature && feature.map)
		{
			this.map["remove" + WPGMZA.capitalizeWords(this.featureType)](feature);
			
			if(feature.id > -1)
				this.updateFeatureByID(feature.id);
		}
	}
	
	WPGMZA.FeaturePanel.prototype.updateFeatureByID = function(id)
	{
		var self = this;
		var feature;
		
		var route				= "/" + this.featureType + "s/";
		var functionSuffix 		= WPGMZA.capitalizeWords(self.featureType);
		var getByIDFunction		= "get" + functionSuffix + "ByID";
		var removeFunction		= "remove" + functionSuffix;
		var addFunction			= "add" + functionSuffix;
		
		WPGMZA.restAPI.call(route + id, {
			success: function(data, status, xhr) {
				
				if(feature = self.map[getByIDFunction](id))
					self.map[removeFunction](feature);
				
				feature	= WPGMZA[WPGMZA.capitalizeWords(self.featureType)].createInstance(data);
				self.map[addFunction](feature);
				
			}
		});
	}
	
	WPGMZA.FeaturePanel.prototype.showInstructions = function()
	{
		switch(this.mode)
		{
			case WPGMZA.FeaturePanel.MODE_ADD:
				$(this.map.element).append(this.drawingInstructionsElement);
				$(this.drawingInstructionsElement).hide().fadeIn();
				break;
			
			default:
				$(this.map.element).append(this.editingInstructionsElement);
				$(this.editingInstructionsElement).hide().fadeIn();
				break;
		}
	}
	
	WPGMZA.FeaturePanel.prototype.onTabActivated = function() {
		this.reset();
		this.drawingManager.setDrawingMode(this.featureType);
		this.onAddFeature(event);

		$(".wpgmza-table-container-title").hide();
		$(".wpgmza-table-container").hide();

		var featureString = this.featureType.charAt(0).toUpperCase() + this.featureType.slice(1);
		
		$("#wpgmza-table-container-"+featureString).show();
		$("#wpgmza-table-container-title-"+featureString).show();

	}
	
	WPGMZA.FeaturePanel.prototype.onTabDeactivated = function()
	{
		this.discardChanges();
		this.setTargetFeature(null);
	}
	
	WPGMZA.FeaturePanel.prototype.onAddFeature = function(event)
	{
		this.drawingManager.setDrawingMode(this.featureType);
		
		//if(this.featureType != "marker")
		//	WPGMZA.animateScroll(WPGMZA.mapEditPage.map.element);
	}
	
	WPGMZA.FeaturePanel.prototype.onEditFeature = function(event)
	{
		var self		= this;
		var name		= "data-edit-" + this.featureType + "-id";
		var id			= $(event.currentTarget).attr(name);

		this.discardChanges();
		
		this.select(id);
	}
	
	WPGMZA.FeaturePanel.prototype.onDeleteFeature = function(event)
	{
		var self		= this;
		var name		= "data-delete-" + this.featureType + "-id";
		var id			= $(event.currentTarget).attr(name);
		var route		= "/" + this.featureType + "s/";
		var feature		= this.map["get" + WPGMZA.capitalizeWords(this.featureType) + "ByID"](id);
		
		this.featureDataTable.dataTable.processing(true);
		
		WPGMZA.restAPI.call(route + id, {
			method: "DELETE",
			success: function(data, status, xhr) {
				
				self.map["remove" + WPGMZA.capitalizeWords(self.featureType)](feature);
				self.featureDataTable.reload();
				
			}
		});
	}
	
	WPGMZA.FeaturePanel.prototype.onDrawingModeChanged = function(event)
	{
		$(this.drawingInstructionsElement).detach();
		$(this.editingInstructionsElement).detach();
		
		if(this.drawingManager.mode == this.featureType)
		{
			this.showInstructions();
		}
	}
	
	WPGMZA.FeaturePanel.prototype.onDrawingComplete = function(event)
	{
		var self			= this;
		var property		= "engine" + WPGMZA.capitalizeWords(this.featureType);
		var engineFeature	= event[property];
		var formData		= this.serializeFormData();
		var geometryField	= $(self.element).find("textarea[data-ajax-name$='data']");
		
		delete formData.polydata;
		
		var nativeFeature = WPGMZA[WPGMZA.capitalizeWords(this.featureType)].createInstance(
			formData,
			engineFeature
		);
		
		this.drawingManager.setDrawingMode(WPGMZA.DrawingManager.MODE_NONE);
		this.map["add" + WPGMZA.capitalizeWords(this.featureType)](nativeFeature);
		
		this.setTargetFeature(nativeFeature);
		
		// NB: This only applies to some features, maybe updateGeometryFields would be better
		if(geometryField.length)
			geometryField.val(JSON.stringify(nativeFeature.getGeometry()));
		
		if(this.featureType != "marker") {
			//WPGMZA.animateScroll( $(this.element).closest(".wpgmza-accordion") );
		}
	}
	
	WPGMZA.FeaturePanel.prototype.onPropertyChanged = function(event)
	{
		var self = this;
		var feature = this.feature;
		
		if(!feature)
			return;	// No feature, we're likely in drawing mode and not editing a feature right now
		
		// Gather all the fields from our inputs and set those properties on the feature
		$(this.element)
			.find(":input[data-ajax-name]")
			.each(function(index, el) {
				
				var key = $(el).attr("data-ajax-name");
				feature[key] = $(el).val();
				
			});
		
		// Now cause the feature to update itself
		feature.updateNativeFeature();
	}
	
	WPGMZA.FeaturePanel.prototype.onFeatureChanged = function(event)
	{
		var geometryField = $(this.element).find("textarea[data-ajax-name$='data']");
		
		if(!geometryField.length)
			return;
		
		geometryField.val(JSON.stringify(this.feature.getGeometry()));
	}
	
	WPGMZA.FeaturePanel.prototype.onSave = function(event) {
		
		var self		= this;
		var id			= $(self.element).find("[data-ajax-name='id']").val();
		var data		= this.serializeFormData();
		
		var route		= "/" + this.featureType + "s/";
		var isNew		= id == -1;
		
		if (this.featureType == 'circle') {
			if (!data.center) {
				alert(WPGMZA.localized_strings.no_shape_circle);
				return;
			}
		}
		if (this.featureType == 'rectangle') {
			if (!data.cornerA) {
				alert(WPGMZA.localized_strings.no_shape_rectangle);
				return;
			}
		}
		if (this.featureType == 'polygon') {
			if (!data.polydata) {
				alert(WPGMZA.localized_strings.no_shape_polygon);
				return;
			}
		}
		if (this.featureType == 'polyline') {
			if (!data.polydata) {
				alert(WPGMZA.localized_strings.no_shape_polyline);
				return;
			}
		}

		if(!isNew)
			route += id;
		
		WPGMZA.mapEditPage.drawingManager.setDrawingMode(WPGMZA.DrawingManager.MODE_NONE);
		this.showPreloader(true);
		
		WPGMZA.restAPI.call(route, {
			method:		"POST",
			data:		data,
			success:	function(data, status, xhr) {
				
				var feature;
				
				var functionSuffix 		= WPGMZA.capitalizeWords(self.featureType);
				var getByIDFunction		= "get" + functionSuffix + "ByID";
				var removeFunction		= "remove" + functionSuffix;
				var addFunction			= "add" + functionSuffix;
				
				self.reset();
				
				if(feature = self.map[getByIDFunction](id))
					self.map[removeFunction](feature);
				
				self.setTargetFeature(null);
				self.showPreloader(false);
				
				feature	= WPGMZA[WPGMZA.capitalizeWords(self.featureType)].createInstance(data);
				self.map[addFunction](feature);
				
				self.featureDataTable.reload();
				self.onTabActivated(event);

			}
		})
	}
	
});
