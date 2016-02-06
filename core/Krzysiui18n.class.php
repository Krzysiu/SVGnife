<?
	/* 
	Language class for internationalizing strings in PHP.
	
	This is the class I use in other private projects, so it's not documented. If you need 
	explaination, contact me. Here's two functions:	
	The main command - "_" can take more arguments to work as sprintf
	getUnused - could be used on the end of application to dump all strings that are in language file
	            yet they weren't used
	setLang('de_DE', 'en_US') - set language and get strings from de_DE and when something's missing,
	                            use en_US strings
	*/
	
	class Krzysiui18n {
		private $langList;
		private $langDirectory;
		private $curLangFile;
		public $currentLang;
		public $allStrings = [];
		private $usedStrings = [];
		
		
		function __construct($directory) {
			$directory = rtrim($directory, "\\/");
			
			$currDir = getcwd();
			chdir($directory);
			$this->langDirectory = getcwd();
			chdir($currDir);
			
			$files = glob("$directory\*.[pP][hH][pP]");
			$files = array_map('basename', $files);
			$files = array_values(preg_grep("/^[a-z]{2}(|_[A-Z]{2,3})\.[pP][hH][pP]$/", $files));
			
			$this->langList = $files;	
		}
		
		function getLangs() {
			foreach ($this->langList as $key => $lang) {
				$codes[$key] = explode('.', $lang)[0];
			}	
			return $codes;
		}
		
		function setLang($langCode = 0, $default = false) {
			if (is_int($langCode)) $fileListKey = $langCode; else $fileListKey = $this->codeToKey($langCode);
			$fileName = $this->keyToPath($fileListKey);
			
			$this->curLangFile = $fileName;
			$this->currentLang = $this->getLangs()[$fileListKey];
			
			require $this->curLangFile;
			$this->allStrings = $lang;
			
			if ($default) {
				require $this->codeToPath($default);
				$this->allStrings = array_merge($lang, $this->allStrings);
			}
		}
		
		function codeToKey($code) {
			return array_search($code, $this->getLangs());
		}
		
		function keyToPath($key) {
			return "{$this->langDirectory}/{$this->langList[$key]}";
		}
		
		function codeToPath($code) {
			return $this->keyToPath($this->codeToKey($code));
		}
		
		function getStr($key) {
			$this->usedStrings[$key] = true;
			return $this->allStrings[$key];
		}
		
		function _() {
			$argCnt = func_num_args();
			if (!$argCnt) return false; 
			
			$this->usedStrings[func_get_arg(0)] = true;
			if ($argCnt == 1) return $this->allStrings[func_get_arg(0)]; else {
				for ($i = 1; $i < $argCnt; $i++) $replEl[] = func_get_arg($i);
				return vsprintf($this->allStrings[func_get_arg(0)], $replEl);
			}
		}
		
		function changeDir($directory) {
			$this->__construct($directory);
		}
		
		function getUnused() {
			$unused = array_diff_key($this->allStrings, $this->usedStrings);
			return $unused;
		}
		
	}	