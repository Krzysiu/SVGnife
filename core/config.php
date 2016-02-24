<?
	// Nothing to config here! It's just SVGnife.ini parser.
	$appVer = '0.1.4';
	
	$configFile = 'SVGnife.ini';
	if (file_exists($configFile)) {
		$iniFile = file_get_contents($configFile);
		// replacing variables, which can be used manually in .ini file
		$sysVars = [
		'%PROGRAMFILES%' => isset($_SERVER['ProgramW6432']) ? $_SERVER['ProgramW6432'] : $_SERVER['ProgramFiles'],
		'%TEMP%' => $_SERVER['TEMP']
		];
		$iniFile = str_replace(array_keys($sysVars), $sysVars, $iniFile);
		
		$config = parse_ini_string($iniFile);
		$config['firstTime'] = false;
		
		unset($iniFile, $sysVars);
		} else {
		// default config
		
		$config = [
		'firstTime' => true,
		'tempDirectory' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'svgNife',
		'inkscapePath' => detectInkscapePath(),
		'displayInfobar' => true,
		'displayNavbar' => true,
		'toolbarStyle' => '2',
		'previewArea' => '0',
		'language' => 'en_US',
		'enableUpload' => false,
		'NSFWTagToFlag' => true,
		'uploadUsername' => '',
		'uploadAPIKey' => ''
		];
		
		if ($config['inkscapePath']) $config['displayPreview'] = true; // enable preview if Inkscape was found
	}	