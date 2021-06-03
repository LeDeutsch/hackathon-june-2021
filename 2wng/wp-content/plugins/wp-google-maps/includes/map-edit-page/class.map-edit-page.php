<?php

namespace WPGMZA;

class MapEditPage extends Page
{
	protected $_document;
	protected $form;
	
	public function __construct($map_id = null) {
		global $wpgmza;
		global $wpgmza_pro_version;
		
		if($map_id === null)
			$map_id = $_REQUEST['map_id'];



		$map = $this->map = Map::createInstance($map_id);
		$map->element->setInlineStyle('min-height', '400px');	// Safeguard for map edit page zero height
		$map->element->setAttribute('id', 'wpgmza_map');		// Legacy HTML

		

		
		// Load the document
		$this->_document = $document = new DOMDocument();
		$this->_document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/map-edit-page/map-edit-page.html.php');
		
		// Add-on span
		if(!$wpgmza->isProVersion())
			$document->querySelectorAll("#wpgmza-title-label, .add-new-editor")->remove();
		
		// HTTP referrer input
		if($element = $document->querySelector("input[name='http_referer']"))
			// NB: Used to be PHP_SELF. We're going to alter this to the current URI. Shouldn't be used, will need checking
			$element->setAttribute('value', $_SERVER['REQUEST_URI']);
		
		// Map ID
		if($element = $document->querySelector('#wpgmza_id'))
			$element->setAttribute('value', $map_id);
		
		// Shortcode
		if($element = $document->querySelector('.wpgmza_copy_shortcode'))
			$element->setAttribute('value', "[wpgmza id=\"$map_id\"]");
		

			

		// Theme panel
		if($element = $document->querySelector('.wpgmza-theme-panel'))
		{
			if($wpgmza->settings->engine != "open-layers")
			{
				$themePanel = new ThemePanel($map);
				$element->import($themePanel);
			}
		}
		
		// User interface style logic
		if($wpgmza->settings->user_interface_style == "legacy")
		{
			foreach($document->querySelectorAll("[data-require-legacy-user-interface-style='false']") as $element)
				$element->remove();
		}
		else
		{
			foreach($document->querySelectorAll("[data-require-legacy-user-interface-style='true']") as $element)
				$element->remove();
		}
		
		// Store locator radius dropdown
		if($element = $document->querySelector('.wpgmza-store-locator-default-radius'))
		{
			$suffix  = ($map->store_locator_distance == 1 ? __('mi', 'wp-google-maps') : __('km', 'wp-google-maps'));
			
			$default = 10;
			
			if(!empty($map->store_locator_default_radius))
				$default = $map->store_locator_default_radius;
			
			$radii = StoreLocator::DEFAULT_RADII;
			
			if(!empty($wpgmza->settings->wpgmza_store_locator_radii) && preg_match_all('/\d+/', $wpgmza->settings->wpgmza_store_locator_radii, $m))
				$radii = $m[0];
			
			foreach($radii as $radius)
			{
				$option = $document->createElement('option');
				
				$option->setAttribute('value', $radius);
				$option->appendText($radius);
				
				if($radius == $default)
					$option->setAttribute('selected', 'selected');
				
				$element->appendChild($option);
			}
			
			$element->parentNode->appendText($suffix);
		}
		
		// Now populate from the map, we need to wait until now so that all the controls are ready
		// NB: Do NOT populate from the map when POSTing - because checkboxes will get stuck ON
		if($_SERVER["REQUEST_METHOD"] != "POST")
			@$document->populate($map);
		
		$document->populate(array(
			'map_id'			=> $map_id,
			'real_post_nonce'	=> wp_create_nonce('wpgmza')
		));
		
		// Form setup
		$this->form = $document->querySelector('form');
		$this->form->setAttribute('action', admin_url('admin-post.php'));
		$this->form->querySelector("input[name='redirect_to']")->setAttribute('value', $_SERVER['REQUEST_URI']);
		
		$this->addFormNonces();
		
		// The map itself
		$document->querySelector("#wpgmza-map-container")->import($map->element);
		
		// Feature accordions
		$containers = $document->querySelectorAll(".wpgmza-feature-accordion");
		
		foreach($containers as $container)
		{
			$featureTypePlural = rtrim(ucwords($container->getAttribute('id')), 's');
			$featureTypeSingle = preg_replace('/s$/', '', $featureTypePlural);
			
			// Panel
			$featurePanelClass = "WPGMZA\\{$featureTypeSingle}Panel";
			$panel = new $featurePanelClass($map_id);


			$container->querySelector('.wpgmza-feature-panel-container')->import($panel);
			


			// Table
			$featureTableClass = "WPGMZA\\Admin{$featureTypePlural}DataTable";
			
			if($featureTypePlural === "Heatmap" && !$wpgmza->isProVersion()){
				//We require a more elegant solution. Move this to Pro entirely, to be addressed later
				continue;
			}

			$table = new $featureTableClass(array(
				'map_id' => $map_id
			));
			
			
			$document->querySelector("#wpgmza-table-container-".$featureTypePlural)->import($table->element);
		
		}

		if(empty($wpgmza->settings->wpgmza_google_maps_api_key) && $wpgmza->settings->engine == "google-maps"){
			$document->querySelector(".wpgmza-missing-key-notice")->removeClass('wpgmza-hidden');
		}

		$engineDialog = new MapsEngineDialog();
		@$document->querySelector("#wpgmza-map-container")->import($engineDialog->html());
		

		
		
	

		$this->disableProFeatures();
		$this->hideSelectedProFeatures();
		$this->removeProMarkerFeatures();
		$this->handleEngineSpecificFeatures();
		
		$this->populateAdvancedMarkersPanel();


	}



	public function createMapPage() {
		if (isset($_GET) && $_GET['action'] == 'create-map-page' && isset($_GET['map_id'])) {
			
			// NB: Suggest using $this->map->id, global functions should be dropped
	    	$res = wpgmza_get_map_data($_GET['map_id']);
	    	
	        // Set the post ID to -1. This sets to no action at moment
	        $post_id = -1;
	     
	        // Set the Author, Slug, title and content of the new post
	        $author_id = get_current_user_id();
	        if ($author_id) {
		        $slug = 'map';
		        $title = $res->map_title;
		        $content = '[wpgmza id="'.$res->id.'"]';
		        

		        // do we have this slug?
		        $args_posts = array(
				    'post_type'      => 'page',
				    'post_status'    => 'any',
				    'name'           => $slug,
				    'posts_per_page' => 1,
				);
				
				$loop_posts = new \WP_Query( $args_posts );
				if ( ! $loop_posts->have_posts() ) {
				    
				    // we dont
				    $post_id = wp_insert_post(
		                array(
		                    'comment_status'    =>   'closed',
		                    'ping_status'       =>   'closed',
		                    'post_author'       =>   $author_id,
		                    'post_name'         =>   $slug,
		                    'post_title'        =>   $title,
		                    'post_content'      =>  $content,
		                    'post_status'       =>   'publish',
		                    'post_type'         =>   'page'
		                )
		            );
		            echo '<script>window.location.href = "post.php?post='.$post_id.'&action=edit";</script>';
		            return;
				} else {
				    $loop_posts->the_post();
				    
				    // we do!
				    $post_id = wp_insert_post(
		                array(
		                    'comment_status'    =>   'closed',
		                    'ping_status'       =>   'closed',
		                    'post_author'       =>   $author_id,
		                    'post_name'         =>   $slug."-".$res->id,
		                    'post_title'        =>   $title,
		                    'post_content'      =>  $content,
		                    'post_status'       =>   'publish',
		                    'post_type'         =>   'page'
		                )
		            );
		            
		            echo '<script>window.location.href = "post.php?post='.$post_id.'&action=edit";</script>';
		            return;
				}
			} else {
				echo "There was a problem creating the map page.";
				return;
			}
	            
	        return;
	    }
	}
	
	// NB: This function is static because only the >= 8.1.0 admin UI manager will instantiate MapEditPage. Called by ScriptLoader.
	public static function enqueueLegacyScripts()
	{
		// NB: Legacy map edit page JavaScript support. This was historically called from basic, which is why it resides here now.
		add_action('admin_head', 'wpgmaps_admin_javascript_pro');
	}
	
	protected function removeProMarkerFeatures()
	{
		$this->document->querySelectorAll(".wpgmza-marker-panel .wpgmza-pro-feature")->remove();
	}
	
	protected function handleEngineSpecificFeatures()
	{
		global $wpgmza;
		
		if($wpgmza->settings->engine == "open-layers")
			$this->document->querySelectorAll("[data-wpgmza-require-engine='google-maps']")->remove();
		else
			$this->document->querySelectorAll("[data-wpgmza-require-engine='open-layers']")->remove();
	}
	
	protected function populateAdvancedMarkersPanel() {
		$panel		= new MarkerPanel($this->map->id);
		$container	= $this->document->querySelector("#advanced-markers");



		

		
		$source		= $panel->querySelector(".wpgmza-feature-panel.wpgmza-marker-panel");
		
		$source->removeAttribute("data-wpgmza-feature-type");
		$source->removeAttribute("class");
		
		$container->import($source);
		
		$container->querySelectorAll("input, select, textarea")
			->setAttribute("disabled", "disabled")
			->setAttribute("title", __('Enable this feature with WP Google Maps - Pro add-on', 'wp-google-maps'));
	}
	
	public function onSubmit()
	{
		$ignore = array(
			'redirect_to',
			'shortcode',
			'nonce',
			'wpgmza_savemap'
		);
		
		// Check nonces
		if(!$this->isNonceValid($this->form, $_POST['nonce']))
			throw new \Exception("Invalid nonce");
		
		// Copy the data
		$data = stripslashes_deep($_POST);

		// Don't write "redirect_to" to the map settings
		foreach($ignore as $key)
			unset($data[$key]);
		
		// Fill out the form
		$this->form->populate($data);
		
		// Get the form data back
		$data = $this->form->serializeFormData();
		
		// Set the data on the map settings
		$this->map->set($data);
		
		// Done! Redirect to the specified URL
		wp_redirect($_POST['redirect_to']);
	}

}

add_action('admin_post_wpgmza_save_map', function() {
	
	$mapEditPage = MapEditPage::createInstance();
	$mapEditPage->onSubmit();
	
});
