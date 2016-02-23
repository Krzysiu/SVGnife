<?
	/*
		General use functions. Please note that this application is released under free license
		and some of following functions could be reused in your applications!
	*/
	
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
	
	function saveConfigFile() {
		global $configFile, $config;
		
		$content = '';
		foreach ($config as $key => $value) $content .= "{$key}={$value}" . PHP_EOL;
		file_put_contents($configFile, $content);
	}
	
	function execQuiet($cmd, $asynchronous = false) {
		// Quiet and optionally asynchronous executing command in shell
		
		if (detectOS(HK_OS_WINDOWS)) {
			if (!extension_loaded('com_dotnet')) dl('php_com_dotnet.dll');
			
			$sh = new COM("WScript.Shell");		
			$sh->Run("cmd /c \"$cmd\"", 0, !$asynchronous);
			return;
			} else {
			if ($asynchronous) $cmd = $cmd . ' > /dev/null 2>&1 &'; 
			exec($cmd);
			return;
		}
	}
	
	function openURL($url) {
		// opens URL in default browser according to current OS
		
		$opener = [0 => 'xdg-open', 1 => 'start', 2 => 'open'];
		$opener = $opener[detectOS()];
		execQuiet("$opener $url", true);
	}
	
	function makeDirHierarchy($path, $dirSep = false) {
		// create path, even if it has more than one directory to create
		
		if ($dirSep === false) $dirSep = DIRECTORY_SEPARATOR;
		$path = rtrim($path, $dirSep);
		$pathArr = explode($dirSep, substr($path, 1));
		$pathArr[0] = substr($path, 0, 1) . $pathArr[0];
		
		$res = true;
		$createPath = '';
		foreach ($pathArr as $dir) if ($res && !is_dir($createPath .= $dir . $dirSep)) $res = mkdir($createPath) || $res;
	}
	