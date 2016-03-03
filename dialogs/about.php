<?
	$dialogAbout = null;
	
	function showAboutDialog() {
		global $dialogAbout, $appVer;
		
		$dialogAbout = new GladeXML('resources/dialogAbout.glade');
		$_dialogAbout = $dialogAbout->get_widget('_dialogAbout');
		$_dialogAbout->connect_simple('response', 'closeAboutDialog');
		$_dialogAbout->connect('activate-link', 'openURLHook');
		
		$_dialogAbout->set_version($appVer);
	}		
	
	function closeAboutDialog() {
		global $dialogAbout;
		$dialogAbout->get_widget('_dialogAbout')->destroy(); 
	}
