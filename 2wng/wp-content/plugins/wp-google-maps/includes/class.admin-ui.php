<?php

namespace WPGMZA\UI;

class Admin extends \WPGMZA\Factory
{
	public function __construct()
	{
		add_action( 'admin_menu', array($this, 'onAdminMenu') );
		add_action( 'admin_enqueue_scripts', array($this, 'onAdminEnqueueScripts') );
	}
	
	public function onAdminEnqueueScripts()
	{
		global $wpgmza;
		
		$wpgmza->loadScripts(false);
	}

	public function onAdminMenu()
	{
		global $wpgmza;
		global $wpgmza_pro_version;
		
		if(!empty($wpgmza_pro_version) && version_compare($wpgmza_pro_version, '8.1.0', '<'))
		{
			return wpgmaps_admin_menu();
		}
				
		$access_level = $wpgmza->getAccessCapability();
		
		add_menu_page(
			'WPGoogle Maps', 
			__('Maps', 'wp-google-maps'), 
			$access_level, 
			'wp-google-maps-menu', 
			'WPGMZA\\UI\\legacy_on_main_menu',
			WPGMZA_PLUGIN_DIR_URL . "images/menu-icon.png"
		);
		
		add_submenu_page(
			'wp-google-maps-menu', 
			'WP Google Maps - Settings', 
			__('Settings', 'wp-google-maps'), 
			$access_level,
			'wp-google-maps-menu-settings',
			'WPGMZA\\UI\\legacy_on_sub_menu',
			1
		);
		
		add_submenu_page(
			'wp-google-maps-menu',
			'WP Google Maps - Support',
			__('Support','wp-google-maps'),
			$access_level ,
			'wp-google-maps-menu-support',
			'WPGMZA\\UI\\legacy_on_sub_menu',
			999
		);
	}
	
	public function onMainMenu()
	{
		global $wpgmza;
		
		$action = (isset($_GET['action']) ? $_GET['action'] : null);
		
		switch($action)
		{
			case "welcome_page":
				$document = new \WPGMZA\DOMDocument();
				$document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/welcome.html.php');
				echo $document->html;
				break;
			
			case "credits":
				$document = new \WPGMZA\DOMDocument();
				$document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/credits.html.php');
				echo $document->html;
				break;
				
			default:
				
				if($action == 'edit')
					$page = \WPGMZA\MapEditPage::createInstance();
				else if ($action == 'create-map-page')
					$page = \WPGMZA\MapEditPage::createMapPage();
				else
					$page = \WPGMZA\MapListPage::createInstance();
				
				echo $page->html;
				
				break;
		}
		
		$document = new \WPGMZA\DOMDocument();
		$document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/footer.html.php');
		echo $document->html;
		
		do_action("wpgmza_check_map_editor_backwards_compat");
	}
	
	public function onSubMenu()
	{
		switch($_GET['page'])
		{
			case 'wp-google-maps-menu-settings':
				$page = \WPGMZA\SettingsPage::createInstance();
				echo $page->html;
				break;
			
			case 'wp-google-maps-menu-support':
				$document = new \WPGMZA\DOMDocument();
				$document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/support.html.php');
				echo $document->html;
				break;
		}
	}
}

function legacy_on_main_menu()
{
	global $wpgmza;
	$wpgmza->adminUI->onMainMenu();
}

function legacy_on_sub_menu()
{
	global $wpgmza;
	$wpgmza->adminUI->onSubMenu();
}


