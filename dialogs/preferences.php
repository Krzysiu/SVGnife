<?
	$dialogPreferences = null;
	$radioGroup['toolbarStyle'] = ['_prefToolbarIcons',	'_prefToolbarLabels',	'_prefToolbarIconsLabels'];
	$radioGroup['previewArea'] = ['_prefPreviewPage', '_prefPreviewDrawing'];
	
	function showPreferencesDialog() {
		global $dialogPreferences, $config, $radioGroup, $i18n, $langList, $gui;
		
		$dialogPreferences = new GladeXML(gtTranslateGlade('dialogPreferences'));
		
		$_btnSave = $dialogPreferences->get_widget('_btnSave');
		$_btnCancel = $dialogPreferences->get_widget('_btnCancel');
		$_btnRecognizeInkscape = $dialogPreferences->get_widget('_btnRecognizeInkscape');
		$_noticeLabel = $dialogPreferences->get_widget('_noticeLabel');
		$_prefDisplayPreview = $dialogPreferences->get_widget('_prefDisplayPreview');
		$_prefLanguageSelect = $dialogPreferences->get_widget('_prefLanguageSelect');
		$_prefUploadEnable = $dialogPreferences->get_widget('_prefUploadEnable');
		$_prefUploadTable = $dialogPreferences->get_widget('_prefUploadTable');
		$_prefInkscapePath = $dialogPreferences->get_widget('_prefInkscapePath');		
		
		$_btnCancel->connect_simple('clicked', 'cancelPreferencesDialog');
		$_btnSave->connect_simple('clicked', 'savePreferencesDialog');
		$_btnRecognizeInkscape->connect_simple('clicked', 'recognizeInkscape');
		$_prefDisplayPreview->connect('toggled', 'setDisplayPreview');
		$_prefUploadEnable->connect_simple('toggled', 'gtToggler', $_prefUploadEnable, $_prefUploadTable);
		$dialogPreferences->get_widget('_prefUploadGetKey')->connect_simple('clicked', 'openURL', 'https://openclipart.org/manage/profile');
		
		gtIcon($_btnSave, Gtk::STOCK_SAVE);
		gtIcon($_btnCancel, Gtk::STOCK_CANCEL);
		gtIcon($_btnRecognizeInkscape, Gtk::STOCK_FIND);
		
		gtColor($_noticeLabel, 'fg', $gui['CNoticeBarFG']);
		gtFont($_noticeLabel, $gui['SNoticeBar']);
		gtFont($dialogPreferences->get_widget('_prefUploadGetKeyLBottom'), $gui['XSmallNote']);
		gtFont($dialogPreferences->get_widget('_prefUploadAPIWarn'), $gui['XSmallNote']);
		
		// Setting states/strings basing on config variable
		if ($config['firstTime']) setPreferencesNoticeBox($i18n->_('prefNoticeFirstTime')); // First time, autorun of preferences dialog
		
		$dialogPreferences->get_widget('_prefDisplayInfobar')->set_active($config['displayInfobar']);
		$dialogPreferences->get_widget('_prefDisplayNavbar')->set_active($config['displayNavbar']);
		$_prefDisplayPreview->set_active($config['displayPreview']);
		$dialogPreferences->get_widget('_prefPreviewAreaBox')->set_sensitive($config['displayPreview']);
		$dialogPreferences->get_widget($radioGroup['toolbarStyle'][$config['toolbarStyle']])->set_active(true);
		$dialogPreferences->get_widget($radioGroup['previewArea'][$config['previewArea']])->set_active(true);
		$_prefUploadEnable->set_active($config['enableUpload']);
		$dialogPreferences->get_widget('_prefUploadNSFW')->set_active($config['NSFWTagToFlag']);
		$_prefUploadTable->set_sensitive($config['enableUpload']);
		gtSetText($dialogPreferences->get_widget('_prefUploadUsername'), $config['uploadUsername']);
		gtSetText($dialogPreferences->get_widget('_prefUploadAPIKey'), $config['uploadAPIKey']);
		$dialogPreferences->get_widget('_prefTempPath')->select_uri($config['tempDirectory']);
		if ($config['inkscapePath']) $_prefInkscapePath->select_uri($config['inkscapePath']); else $_prefInkscapePath->unselect_all();
		
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
		$dialogPreferences->get_widget('_prefPreviewPage')->connect_simple('toggled', 'setPreferencesNoticeBox', $i18n->_('prefNoticeRefresh'));
		$dialogPreferences->get_widget('_prefPreviewDrawing')->connect_simple('toggled', 'setPreferencesNoticeBox', $i18n->_('prefNoticeDrawingArea') . "\n" . $i18n->_('prefNoticeRefresh'), $gui['CNoticeBarWarnBG']);
		if (detectOS(HK_OS_WINDOWS)) $_prefInkscapePath->connect_simple('file-set', 'checkInkscapePath');
	}
	
	function recognizeInkscape() {
		global $dialogPreferences, $i18n, $gui;
		
		$recRes = detectInkscapePath();
		if ($recRes) {
			$dialogPreferences->get_widget('_prefInkscapePath')->select_uri($recRes);
			setPreferencesNoticeBox($i18n->_('prefInkscapeAutoOk', $recRes));
		} else setPreferencesNoticeBox($i18n->_('prefInkscapeAutoFail'), $gui['CNoticeBarWarnBG']);
	}
	
	function checkInkscapePath() {
		global $dialogPreferences, $i18n, $gui;
		$inkPath = $dialogPreferences->get_widget('_prefInkscapePath')->get_filenames();
		if (isset($inkPath[0]) && (strtolower(basename($inkPath[0])) === 'inkscape.exe')) setPreferencesNoticeBox($i18n->_('prefInkscapeWinWrongVer'), $gui['CNoticeBarWarnBG']); else setPreferencesNoticeBox(false);
	}
	
	function setDisplayPreview($widget) {
		global $i18n, $dialogPreferences;
		
		$state = $widget->get_active();
		gtToggler($widget, $dialogPreferences->get_widget('_prefPreviewAreaBox'));
		if ($state) setPreferencesNoticeBox($i18n->_('prefNoticeRefresh'));
	}
	
	function setPreferencesNoticeBox($msg, $color = false) {
		global $dialogPreferences, $gui;
		
		if ($msg === false) { $dialogPreferences->get_widget('_noticeBar')->hide(); return; }
		$color = $color ?: $gui['CNoticeBarBG'];
		gtColor($dialogPreferences->get_widget('_noticeBar'), 'bg', $color);
		gtSetText($dialogPreferences->get_widget('_noticeLabel'), $msg);
		$dialogPreferences->get_widget('_noticeBar')->show();
	}
	
	function cancelPreferencesDialog() {
		global $dialogPreferences, $config;
		if ($config['firstTime']) Gtk::main_quit(); else $dialogPreferences->get_widget('_dialogPreferences')->destroy();
	}
	
	function savePreferencesDialog() {
		global $dialogPreferences, $glade, $config, $radioGroup, $i18n, $gui;
		
		// Display preview
		$config['displayPreview'] = $dialogPreferences->get_widget('_prefDisplayPreview')->get_active();
		gtShow($glade->get_widget('_previewArea'), $config['displayPreview']);
		
		// Check if temporary directory is accessible
		$config['tempDirectory'] = $dialogPreferences->get_widget('_prefTempPath')->get_filenames()[0];
		if (!is_dir($config['tempDirectory'])) { setPreferencesNoticeBox($i18n->_('prefErrTempDir404'), $gui['CNoticeBarErrBG']); return false; }
		$accessTest = $config['tempDirectory'] . DIRECTORY_SEPARATOR . 'tmpTest';
		if (!touch($accessTest)) { setPreferencesNoticeBox($i18n->_('prefErrTempDirAccess', $config['tempDirectory']), $gui['CNoticeBarErrBG']); return false; }
		unlink($accessTest); unset($accessTest);
		
		$inkPath = $dialogPreferences->get_widget('_prefInkscapePath')->get_filenames();
		$config['inkscapePath'] = isset($inkPath[0]) ? $inkPath[0] : '';
		unset($inkPath);
		
		if (!file_exists($config['inkscapePath']) && $config['displayPreview']) { setPreferencesNoticeBox($i18n->_('prefErrInkscapeAccess'), $gui['CNoticeBarErrBG']);  return false; }
		
		// Display info bar
		$config['displayInfobar'] = $dialogPreferences->get_widget('_prefDisplayInfobar')->get_active();
		gtShow($glade->get_widget('_infoBar'), $config['displayInfobar']);
		
		// Display navigation bar
		$config['displayNavbar'] = $dialogPreferences->get_widget('_prefDisplayNavbar')->get_active();
		gtShow($glade->get_widget('_navBar'), $config['displayNavbar']);
		
		// Hide upload button if upload is disabled
		$config['enableUpload'] = $dialogPreferences->get_widget('_prefUploadEnable')->get_active();
		gtShow($glade->get_widget('_btnUpload'), $config['enableUpload']);
		
		$i = 0;
		foreach ($radioGroup['toolbarStyle'] as $radio) { if ($dialogPreferences->get_widget($radio)->get_active()) { $config['toolbarStyle'] = $i; break; }; $i++; }
		$glade->get_widget('_toolbar')->set_toolbar_style($config['toolbarStyle']);
		
		$i = 0;
		foreach ($radioGroup['previewArea'] as $radio) { if ($dialogPreferences->get_widget($radio)->get_active()) { $config['previewArea'] = $i; break; }; $i++; }
		
		// Active language
		preg_match('/\(([a-zA-Z_]*)\)/', $dialogPreferences->get_widget('_prefLanguageSelect')->get_active_text(), $matches);
		$config['language'] = $matches[1];
		unset($matches);
		
		$config['uploadUsername'] = gtGetText($dialogPreferences->get_widget('_prefUploadUsername'));
		$config['uploadAPIKey'] = gtGetText($dialogPreferences->get_widget('_prefUploadAPIKey'));
		$config['NSFWTagToFlag'] = $dialogPreferences->get_widget('_prefUploadNSFW')->get_active();
		saveConfigFile(); // @functions.php
		
		$dialogPreferences->get_widget('_dialogPreferences')->destroy();
	}
	
