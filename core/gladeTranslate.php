<?	
	require_once 'lib/gettext/po.php';
	
	
	function gtTranslateGlade($dialog) {
	global $config;

		$langFile = "i18n/glade-source/{$config['language']}.{$dialog}.po";
		$gladeFile = "resources/{$dialog}.glade";
		$outFile = "resources/{$config['language']}.{$dialog}.glade";
		
		if (!file_exists($langFile)) return $gladeFile;
		
		$poFile = new File_Gettext_PO();
		$poFile->load($langFile);
		
		$poStrings = $poFile->strings;
		
		$find = []; $replace = [];
		foreach ($poStrings as $in => $out) {
			if ($out) {
				$find[] = '/<property(.*)translatable="yes"(.*)>' . preg_quote($in, '/') . '(<\/property>)/';
				$replace[] = '<property\\1\\2>' . preg_quote($out, '/') . '\\3';
			}
			
		}
		
		$result = preg_replace($find, $replace, file_get_contents($gladeFile));
		file_put_contents($outFile, $result);
		return $outFile;
	}	