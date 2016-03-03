<?
	$dialogAbout = null;
	
	function showAboutDialog() {
		global $dialogAbout, $appVer;
		$dialogAbout = new GladeXML('resources/dialogAbout.glade');
		$_dialogAbout = $dialogAbout->get_widget('_dialogAbout');
		$_dialogAbout->connect('activate-link', 'openURLHook');
		$_dialogAbout->connect_simple('response', 'closeAboutDialog');
		$_dialogAbout->set_version($appVer);		
		
		$_aboutDescription = $_dialogAbout->get_children()[0]->get_children()[0]->get_children()[2];
		$_aboutDescription->set_use_markup(true);
		$_aboutDescription->connect('activate-link', 'openURLHook');
	}		
	
	function closeAboutDialog() {
		global $dialogAbout;
		$dialogAbout->get_widget('_dialogAbout')->destroy(); 
	}
