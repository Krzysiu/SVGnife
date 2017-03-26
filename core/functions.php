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
	
	function detectInkscapePath() {
		if (detectOS(HK_OS_WINDOWS)) {
			// automatic attempt to recognize Inkscape path for Windows
			$inkPath = "Inkscape";
			$inkBin = "inkscape.com";
			
			// try standard locations
			if (isset($_SERVER['ProgramFiles(x86)']) && file_exists($tryPath = "{$_SERVER['ProgramFiles(x86)']}\\{$inkPath}\\{$inkBin}")) return $tryPath;	
			if (isset($_SERVER['ProgramW6432']) && file_exists($tryPath = "{$_SERVER['ProgramW6432']}\\{$inkPath}\\{$inkBin}")) return $tryPath;	
			
			// try to read from registry
			if (!extension_loaded('com_dotnet')) dl('php_com_dotnet.dll');
			$sh = new COM('WScript.Shell');
			
			$checkKeys = [
			"HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\App Paths\\inkscape.exe\\",
			"HKCR\\inkscape.svg\\shell\\open\\command\\"
			];
			
			foreach ($checkKeys as $rk) {
				try {
					$regRead = $sh->regRead($rk);
					// no need to check if read passed, because if not, the rest of block won't be executed
					$regRead = str_replace('%1', '', $regRead);
					$tryPath = dirname(trim($regRead, '" ')) . "\\{$inkBin}";
					if (file_exists($tryPath)) return $tryPath;
				} catch (Exception $e) {}				
			}
			
			return ''; // not found
			} else {
			// automatic attempt to recognize Inkscape path for other systems
			if ($shOut = shell_exec('command -v inkscape')) return trim($shOut); // passes if Inkscape is within default path
			
			// try standard location
			$tryPath = '/usr/bin/inkscape';
			if (file_exists($tryPath)) return $tryPath;
			
			return ''; // not found
		}
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
			
			$sh = new COM('WScript.Shell');		
			$sh->Run("cmd /c \"$cmd\"", 0, !$asynchronous);
			return;
			} else {
			if ($asynchronous) $cmd = $cmd . ' > /dev/null 2>&1 &'; 
			exec($cmd);
			return;
		}
	}
	
	function openURLHook($widget, $url) {
	// Handled by own hook, because in many GTK instalations default browser is broken and user can't open link
	openURL($url);
	return true;
	}
	
	function openURL($url) {
		// opens URL in default browser according to current OS

		$opener = [HK_OS_UNIX => 'xdg-open', HK_OS_WINDOWS => 'start', HK_OS_MAC => 'open'];
		$opener = $opener[detectOS()];
		$url = shellEncode($url);
		execQuiet("$opener $url", true);
		
		return;
	}
	
	function shellEncode($str) {
	// Encodes shell command according to current system
	$os = detectOS();
	if ($os === HK_OS_WINDOWS) $str = strtr($str, ['&' => '^&', '|' => '^|', '^|' => '^^']); // escape Windows characters
	else $str = strtr($str, ['&' => '\&', "\\" => "\\\\", '|' => '\|', '^|' => '^^']); // escape other system characters
	
	return $str;
	}
	
	function makeDirHierarchy($path, $dirSep = false) {
		// create path, even if it has more than one directory to create
		
		if ($dirSep === false) $dirSep = DIRECTORY_SEPARATOR;
		$path = rtrim($path, $dirSep);
		$pathArr = explode($dirSep, substr($path, 1));
		$pathArr[0] = substr($path, 0, 1) . $pathArr[0];
		
		$createPath = '';
		foreach ($pathArr as $dir) if ($res && !is_dir($createPath .= $dir . $dirSep)) if (!mkdir($createPath)) return false;
		return true;
	}
