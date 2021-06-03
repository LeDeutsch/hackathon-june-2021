<?php

namespace WPGMZA;

class MapListPage extends Page
{
	public function __construct()
	{
		global $wpgmza;
		
		$this->_document = new \WPGMZA\DOMDocument();
		$this->_document->loadPHPFile(WPGMZA_PLUGIN_DIR_PATH . 'html/map-list-page.html.php');
		
		// Review nag
		if(isset($_GET['wpgmza-close-review-nag']))
			update_option('wpgmza-review-nag-closed', true);
		
		$daysSinceFirstRun = 0;
		if($str = get_option('wpgmza-first-run'))
		{
			$datetime	= \DateTime::createFromFormat(\DateTime::ISO8601, $str);
			$now		= new \DateTime();
			$diff		= $now->diff($datetime);
			
			$daysSinceFirstRun = $diff->days;
		}
		
		if($wpgmza->isProVersion() 
			|| get_option('wpgmza-review-nag-closed') 
			|| $daysSinceFirstRun < 10)
		{
			$this->document->querySelectorAll(".wpgmza-review-nag")->remove();

			if($wpgmza->isProVersion())
			{
				ProPage::enableProFeatures($this->document);
				ProPage::removeUpsells($this->document);
			}
		}
		
		// The map table
		$adminMapDataTableOptions = array(
			"pageLength" => 25,
			 "order" => [[ 1, "desc" ]]
		);

		$adminMapDataTable = new \WPGMZA\AdminMapDataTable(null, $adminMapDataTableOptions);
		$this->hideSelectedProFeatures();
		$this->_document->querySelector("#wpgmza-admin-map-table-container")->import($adminMapDataTable->document->html);
	}
	
	public function __get($name)
	{
		switch($name)
		{
			case "document":
				return $this->{"_$name"};
				break;
				
			case "html":
				return $this->document->html;
				break;
		}
	}
}