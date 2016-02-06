<?
	$dialogPreferences = null;
	$radioGroup['toolbarStyle'] = ['_prefToolbarIcons',	'_prefToolbarLabels',	'_prefToolbarIconsLabels'];
	$radioGroup['previewArea'] = ['_prefPreviewPage', '_prefPreviewDrawing'];
	
	function showPreferencesDialog() {
		global $dialogPreferences, $config, $radioGroup, $i18n, $langList;
		$dialogPreferences = new GladeXML(gtTranslateGlade('dialogPreferences'));
		$_btnSave = $dialogPreferences->get_widget('_btnSave');
		$_btnCancel = $dialogPreferences->get_widget('_btnCancel');
		$_noticeLabel = $dialogPreferences->get_widget('_noticeLabel');
		$_prefDisplayPreview = $dialogPreferences->get_widget('_prefDisplayPreview');
		$_prefLanguageSelect = $dialogPreferences->get_widget('_prefLanguageSelect');
		$_btnCancel->connect_simple('clicked', 'cancelPreferencesDialog'); 
		$_btnSave->connect_simple('clicked', 'savePreferencesDialog');
		
		gtIcon($_btnSave, Gtk::STOCK_SAVE);
		gtIcon($_btnCancel, Gtk::STOCK_CANCEL);
		gtColor($dialogPreferences->get_widget('_noticeBar'), 'bg', '#2C6DA9');
		gtColor($_noticeLabel, 'fg', '#F7F7F7');
		gtFont($dialogPreferences->get_widget('_noticeLabel'), 'bold');
		
		// Setting states/strings basing on config variable
		$dialogPreferences->get_widget('_prefDisplayInfobar')->set_active($config['displayInfobar']);
		$dialogPreferences->get_widget('_prefDisplayNavbar')->set_active($config['displayNavbar']);
		$_prefDisplayPreview->set_active($config['displayPreview']);
		$dialogPreferences->get_widget('_prefPreviewAreaBox')->set_sensitive($config['displayPreview']);
		$dialogPreferences->get_widget($radioGroup['toolbarStyle'][$config['toolbarStyle']])->set_active(true);
		$dialogPreferences->get_widget($radioGroup['previewArea'][$config['previewArea']])->set_active(true);
		
		// setting language combobox
		$comboIndex = 0;
		foreach ($langList as $code => $name) {
			$comboStr = "{$name} ({$code})";
			$_prefLanguageSelect->append_text($comboStr);

			if ($config['language'] === $code) $_prefLanguageSelect->set_active($comboIndex);
			$comboIndex++;
			
		}
		
		// signals that displays infobar notices; they have to be set after widget setup
		$_prefDisplayPreview->connect('toggled', 'setDisplayPreview');
		foreach ($radioGroup['previewArea'] as $radio) $dialogPreferences->get_widget($radio)->connect_simple('toggled', 'setPreferencesNoticeBox', $i18n->_('prefNoticeRefresh'));
	}
	
	function setDisplayPreview($widget) {
		global $i18n, $dialogPreferences;
		
		$state = $widget->get_active();
		gtToggler($widget, $dialogPreferences->get_widget('_prefPreviewAreaBox'));
		if ($state) setPreferencesNoticeBox($i18n->_('prefNoticeRefresh'));	
	}
	
	function setPreferencesNoticeBox($msg) {
	global $dialogPreferences;
	$dialogPreferences->get_widget('_noticeBar')->show();
	gtSetText($dialogPreferences->get_widget('_noticeLabel'), $msg);
}

function cancelPreferencesDialog() {
	global $dialogPreferences;
	$dialogPreferences->get_widget('_dialogPreferences')->destroy(); 
}

function savePreferencesDialog() {
	global $dialogPreferences, $glade, $config, $radioGroup;
	
	// Display info bar
	$config['displayInfobar'] = $dialogPreferences->get_widget('_prefDisplayInfobar')->get_active();
	gtShow($glade->get_widget('_infoBar'), $config['displayInfobar']);
	
	// Display navigation bar
	$config['displayNavbar'] = $dialogPreferences->get_widget('_prefDisplayNavbar')->get_active();
	gtShow($glade->get_widget('_navBar'), $config['displayNavbar']);
	
	$config['displayPreview'] = $dialogPreferences->get_widget('_prefDisplayPreview')->get_active();
	gtShow($glade->get_widget('_previewArea'), $config['displayPreview']);
	
	$i = 0;
	foreach ($radioGroup['toolbarStyle'] as $radio) { if ($dialogPreferences->get_widget($radio)->get_active()) { $config['toolbarStyle'] = $i; break; }; $i++; }
	$glade->get_widget('_toolbar')->set_toolbar_style($config['toolbarStyle']);
	
	$i = 0;
	foreach ($radioGroup['previewArea'] as $radio) { if ($dialogPreferences->get_widget($radio)->get_active()) { $config['previewArea'] = $i; break; }; $i++; }
	
	// Active language
	preg_match('/\(([a-zA-Z_]*)\)/', $dialogPreferences->get_widget('_prefLanguageSelect')->get_active_text(), $matches);
	$config['language'] = $matches[1];
	unset($matches);	
	
	saveConfigFile(); // @functions.php
	
	$dialogPreferences->get_widget('_dialogPreferences')->destroy(); 		
}

