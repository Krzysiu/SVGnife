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
	
	function openURL($url) {
	// opens URL in default browser according to current OS
	
	$opener = [0 => 'xdg-open', 1 => 'start', 2 => 'open'];
	$opener = $opener[detectOS()];
	execQuiet("$opener $url", true);
	}
	
	define('HK_OS_UNIX', 0);
	define('HK_OS_WINDOWS', 1);
	define('HK_OS_MAC', 2);	
	function detectOS($isOS = false) {
	// if $isOS = (int), it returns boolean - given OS (t) or other (f)
	
	$sys = php_uname('s');
	
	if (substr($sys, 0, 7) === 'Windows') $os = HK_OS_WINDOWS;
	elseif (substr($sys, 0, 6) === 'Darwin') $os = HK_OS_MAC;
	else $os = HK_OS_UNIX;
	
	if ($isOS !== false) return ($isOS === $os); else return $os;	
	}
	