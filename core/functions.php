<?
	/*
	General use functions
	*/
	
	function saveConfigFile() {
	global $configFile, $config;
	
	$content = '';
	foreach ($config as $key => $value) $content .= "{$key}={$value}" . PHP_EOL;
	file_put_contents($configFile, $content);
}

	function execQuiet($cmd, $asynchronous = false) {
	// Quiet and optionally asynchronous executing command in Windows shell
		if (!extension_loaded('com_dotnet')) dl('php_com_dotnet.dll');
		
		$sh = new COM("WScript.Shell");		
		$sh->Run("cmd /c \"$cmd\"", 0, !$asynchronous);
		
		return;
	}
