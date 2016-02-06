<?
	// Nothing to config here! It's just SVGnife.ini parser.
	$appVer = '0.1.3';
	
	$configFile = 'SVGnife.ini';
	$iniFile = file_get_contents($configFile);
	
	// replacing variables, which can be used manually in .ini file
	$sysVars = [
	'%PROGRAMFILES%' => isset($_SERVER['ProgramW6432']) ? $_SERVER['ProgramW6432'] : $_SERVER['ProgramFiles'],
	'%TEMP%' => $_SERVER['TEMP']
	];
	$iniFile = str_replace(array_keys($sysVars), $sysVars, $iniFile);
	
	$config = parse_ini_string($iniFile);

	unset($iniFile, $sysVars);
