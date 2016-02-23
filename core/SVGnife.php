<?
	
	/* Setting up variables */
	$fields = ['Title', 'Tags', 'Date', 'Language', 'Publisher', 'Description'];
	$dialogs = ['open', 'about', 'preferences'];
	$toolbarStyles = [Gtk::TOOLBAR_ICONS, Gtk::TOOLBAR_TEXT, Gtk::TOOLBAR_BOTH];
	$fileList = ['properSVG' => false, 'loadedFile' => false];
	$timerSpin = null;
	
	/* Initialization */
	set_include_path(get_include_path() . PATH_SEPARATOR . 'lib');
	
	foreach ($dialogs as $dialog) require_once("dialogs/{$dialog}.php"); // including additional dialogs
	
	require_once('core/functions.php');
	require_once('core/guiOptions.php');
	require_once('core/Krzysiui18n.class.php');
	require_once('core/gtkToolbox.php');
	require_once('core/config.php');
	require_once('Gtk2/FileDrop.php');
	require_once('core/svgReadMeta.php');
	require_once('core/gladeTranslate.php');
	require_once('i18n/langList.php');
	
	$i18n = new Krzysiui18n('i18n');
	$i18n->setLang($config['language'], 'en_US');
	
	$glade = new GladeXML('resources/mainWindow.glade');
	
	/* Preparing tip of the day */
	$tips = [];
	$tips['tips'] = explode('|', $i18n->_('TOTD'));
	$tips['current'] = array_rand($tips['tips']);	
	
	/* Getting widgets */
	$_mainWindow = $glade->get_widget('_mainWindow');
	$_btnCopy = $glade->get_widget('_btnCopy');
	$_topLabel = $glade->get_widget('_topLabel');
	$_tipLabel = $glade->get_widget('_tipLabel');
	$_eventBox = $glade->get_widget('_eventBox');
	$_topIcon = $glade->get_widget('_topIcon');
	$_btnOpen = $glade->get_widget('_btnOpen');
	$_btnAbout = $glade->get_widget('_btnAbout');
	$_btnUpload = $glade->get_widget('_btnUpload');
	$_btnConfig = $glade->get_widget('_btnConfig');
	$_btnExit = $glade->get_widget('_btnExit');
	$_preview = $glade->get_widget('_preview');
	$_previewSpinner = $glade->get_widget('_previewSpinner');
	$_navIndex = $glade->get_widget('_navIndex');
	$_navName = $glade->get_widget('_navName');
	$_btnNavRefresh = $glade->get_widget('_btnNavRefresh');
	$_btnNav[-2] = $glade->get_widget('_btnNavFirst');
	$_btnNav[-1] = $glade->get_widget('_btnNavPrev');
	$_btnNav[1] = $glade->get_widget('_btnNavNext');
	$_btnNav[2] = $glade->get_widget('_btnNavLast');
	$_infoHBox = $glade->get_widget('_infoHBox');
	$_infoTipBox = $glade->get_widget('_infoTipBox');	
	
	/* Signals */
	Gtk2_FileDrop::attach($_eventBox, ['image/svg+xml', '*/*'], 'fileDropped', false);	
	$_mainWindow->connect_simple('destroy', array('Gtk','main_quit'));
	$_btnExit->connect_simple('clicked', array('Gtk','main_quit'));
	$_btnUpload->connect_simple('clicked', 'uploadClick');
	$_btnOpen->connect_simple('clicked', 'showOpenDialog');
	$_btnConfig->connect_simple('clicked', 'showPreferencesDialog');
	$glade->get_widget('_btnTipUp')->connect_simple('clicked', 'setTip', -1);
	$glade->get_widget('_btnTipDown')->connect_simple('clicked', 'setTip', 1);
	
	$_btnAbout->connect_simple('clicked', 'showAboutDialog');
	$_btnNavRefresh->connect_simple('clicked', 'refreshFile');
	
	foreach ($_btnNav as $type => $btn) $btn->connect_simple('clicked', 'navBarClick', $type);
	
	/* Widget customization */
	/* See guiOptions.php */
	gtColor('_infoBox', 'bg', $gui['CNoticeBarBG']);
	gtColor($_topLabel, 'fg', $gui['CNoticeBarFG']);
	gtFont($_topLabel, $gui['SNoticeBar']);	
	gtColor($_tipLabel, 'fg', $gui['CNoticeBarFG']);
	gtFont($_tipLabel, $gui['XTipOfTheDay']);
	
	/* Setting widgets according to user config */
	gtShow($glade->get_widget('_infoBar'), $config['displayInfobar']);
	gtShow($glade->get_widget('_navBar'), $config['displayNavbar']);
	gtShow($glade->get_widget('_previewArea'), $config['displayPreview']);
	$glade->get_widget('_toolbar')->set_toolbar_style($toolbarStyles[$config['toolbarStyle']]);
	gtShow($_btnUpload, $config['enableUpload']);
	
	/* Run */
	if (!is_dir($config['tempDirectory'])) makeDirHierarchy($config['tempDirectory']);
	
	$_previewSpinner->hide();
	setTip(); // set initial, random tip of the day
	if (isset($argv[1]) && file_exists($argv[1])) readSVG($argv[1]); // cmd line parameters support
	
	while (Gtk::events_pending()) Gtk::main_iteration();
	if ($config['firstTime']) showPreferencesDialog();
	Gtk::main();

	/* Functions */
	function uploadClick() {
		global $i18n, $glade, $fileList;
		
		if (!$fileList['loadedFile']) {
			setTopBar($i18n->_('uploadNoFile'), Gtk::STOCK_DIALOG_WARNING);
			return;
		}
		
		if (!$fileList['properSVG']) {
			setTopBar($i18n->_('uploadWrongFile'), Gtk::STOCK_DIALOG_WARNING);
			return;
		}		
		
		$neededFields = [];
		$content['file'] = $fileList['currentFile'];
		$content['title'] = gtGetText('_entryTitle');
		$content['description'] = gtGetText('_entryDescription');
		$content['tags'] = gtGetText('_entryTags');
		
		if (trim($content['title']) === '') $neededFields[] = $i18n->_('uploadFieldTitle');
		if (trim($content['description']) === '') $neededFields[] = $i18n->_('uploadFieldDescription');
		if (trim($content['tags']) === '') $neededFields[] = $i18n->_('uploadFieldTags');
		
		if (count($neededFields) > 0) {
			setTopBar($i18n->_('uploadFillFields', implode(', ', $neededFields)), Gtk::STOCK_DIALOG_WARNING);
			return;
		}
		
		if (strpos($content['tags'], '#') === false) {
			setTopBar($i18n->_('uploadWrongTags'), Gtk::STOCK_DIALOG_WARNING);
			return;
		}
		
	}
	
	function setTip($delta = 0) {
		// Sets so called "tip of the day" or "did you know that"
		global $_tipLabel, $tips, $i18n;
		
		if ($delta === -1) {
			if ($tips['current'] === 0)	$tips['current'] = count($tips['tips']) - 1; else $tips['current'] = $tips['current'] - 1;
		}
		
		if ($delta === 1) {
			if ($tips['current'] === count($tips['tips']) - 1)	$tips['current'] = 0; else $tips['current'] = $tips['current'] + 1;
		}
		
		$_tipLabel->set_markup("{$i18n->_('TOTDPrefix')} {$tips['tips'][$tips['current']]}");
	}
	
	function spinnerToggle($state) {
		global $_previewSpinner, $_preview;
		gtShow($_preview, !$state);
		gtShow($_previewSpinner, $state);
		gtSpin($_previewSpinner, $state);
	}
	
	function timerSpin($file) {
		// Timer which checks if Inkscape created preview of file
		global $timerSpin, $_preview;
		
		if (file_exists("{$file}.nul")) {
			// Preview created;
			Gtk::timeout_remove($timerSpin);
			$timerSpin = null;
			
			try {
				// Try to load the file to don't crash when file is broken
				$pixBuffer = GdkPixbuf::new_from_file_at_size($file, 200, 270);
				$_preview->set_from_pixbuf($pixBuffer);
				} catch (Exception $e) {
				gtIcon($_preview, Gtk::STOCK_MISSING_IMAGE);
			}
			unlink($file);
			unlink("{$file}.nul");
			
			spinnerToggle(false);
			
			return false;
		}
		return true;
	}
	
	function refreshFile() {
		global $fileList;
		readSVG($fileList['currentFile']);
	}
	
	function fileDropped($widget, $files) {
		readSVG($files[0]);
	}
	
	function navBarClick($type) {
		global $fileList;
		
		readSVG($fileList['nav'][$type]);
	}
	
	function readSVG($file) {
		global $fields, $config, $timerSpin, $_navName, $_navIndex, $_btnNav, $fileList, $_btnNavRefresh, $i18n;
		
		if (!file_exists($file)) {
			setTopBar($i18n->_('loadProblems', basename($file)), Gtk::STOCK_DIALOG_WARNING);
			return;
		}
		
		resetFields();
		$_btnNavRefresh->set_sensitive(true); // Enable refresh button with first (and every other) reading file
		
		// Set navigation bar - it's done everytime file is loading to refresh directory, which could change meanwhile
		$fileList['directory'] = dirname($file);
		$fileList['currentFile'] = $file;
		$fileList['list'] = glob("{$fileList['directory']}\\*.[Ss][Vv][Gg]");
		$fileList['total'] = count($fileList['list']);
		
		$fileList['current'] = intval(array_search($file, $fileList['list'])) + 1;
		$fileList['isLast'] = ($fileList['current'] === $fileList['total']);
		$fileList['isFirst'] = ($fileList['current'] === 1);
		
		// As index is set during loading, the destination files for nav buttons are set same time, to avoid weird behavior on directory change
		if (!$fileList['isFirst']) { 
			$fileList['nav'][-2] = $fileList['list'][0]; 
			$fileList['nav'][-1] = $fileList['list'][$fileList['current']-2]; 
		}
		if (!$fileList['isLast']) { 
			$fileList['nav'][1] = $fileList['list'][$fileList['current']]; 
			$fileList['nav'][2] = $fileList['list'][$fileList['total']-1]; 
		}
		
		// For now navigation buttons don't loop, so turn off unusable and on usable
		$_btnNav[-2]->set_sensitive(!$fileList['isFirst']);
		$_btnNav[-1]->set_sensitive(!$fileList['isFirst']);
		$_btnNav[1]->set_sensitive(!$fileList['isLast']);			
		$_btnNav[2]->set_sensitive(!$fileList['isLast']);
		
		gtSetText($_navName, basename($file));
		gtSetText($_navIndex, $fileList['current'] . '/' . $fileList['total']);
		
		// That's to be changed, as SVG with embedded raster files aren't recognized as image/svg+xml
		if (mime_content_type($file) === 'image/svg+xml') { 
			$fileList['properSVG'] = true;
			if ($config['displayPreview']) { // display preview only when it's visible, so people without Inkscape won't get errors
				// Generate preview
				$outputFile = $config['tempDirectory'] . DIRECTORY_SEPARATOR . uniqid('preview', true) . '.png';				
				$previewArea = ['page', 'drawing'];
				$cmdPreview = '--export-area-' . $previewArea[$config['previewArea']];
				if (!empty($timerSpin)) Gtk::timeout_remove($timerSpin); // needed for navigating to other file before preview is done
				
				$timerSpin = Gtk::timeout_add(250, 'timerSpin', $outputFile); // checking for Inkscape preview to be done
				spinnerToggle(true);
				
				// This command runs asynchronously two synchronous commands, so _after_ Inkscape is done it 
				// creates an empty file, so app would know when file is ready.
				$asynchFix = detectOS(HK_OS_WINDOWS) ?  "& type nul >>\"$outputFile.nul\"" : "&& touch \"$outputFile.nul\"";
				execQuiet("\"{$config['inkscapePath']}\" --file=\"{$file}\" -w=200 {$cmdPreview} --export-png=\"{$outputFile}\" $asynchFix", true);	
			}
			
			// Get SVG metadata
			$meta = getSVGMetadata($file);
			if (!$meta) {
				// Parsing metadata error
				setTopBar(sprintf(_('%s metadata not found'), basename($file)), Gtk::STOCK_CAPS_LOCK_WARNING);
				return;
			}
			
			// Parsing metadata ok
			foreach ($fields as $id) gtSetText("_entry{$id}", $meta[$id]);	
			setTopBar(sprintf(_('%s parsed'), basename($file)), Gtk::STOCK_APPLY);
			} else {
			// Not SVG file
			$fileList['properSVG'] = false;
			setTopBar($i18n->_('loadIncorrectSVG', basename($file)), Gtk::STOCK_DIALOG_WARNING);
		}
		$fileList['loadedFile'] = true;
	}
	
	function resetFields() {
		global $fields, $_preview;
		gtIcon($_preview, Gtk::STOCK_CANCEL);
		foreach ($fields as $id) gtSetText("_entry{$id}", '');
	}
	
	function setTopBar($label, $icon) {
		global $_topLabel, $_topIcon, $_infoTipBox, $_infoHBox;
		
		$_infoTipBox->hide(); // hide tip of the day and show info bar
		$_infoHBox->show(); 
		gtIcon($_topIcon, $icon);
		gtSetText($_topLabel, $label);
	}
