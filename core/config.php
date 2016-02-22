<?
	// Nothing to config here! It's just SVGnife.ini parser.
	$appVer = '0.1.3';
	
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
		
		unset($iniFile, $sysVars);
		} else {
		// default config
		
		$config = [
		'firstTime' => true,
		'tempDirectory' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'svgNife',
		'displayInfobar' => true,
		'displayNavbar' => true,
		'displayPreview' => true,
		'toolbarStyle' => '2',
		'previewArea' => '0',
		'language' => 'en_US',
		'enableUpload' => '0',
		'NSFWTagToFlag' => '1',
		'uploadUsername' => '',
		'uploadAPIKey' => ''
		];
	}	