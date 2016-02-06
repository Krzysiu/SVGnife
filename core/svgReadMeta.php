<?
	
	function getSVGMetadata($infile) {
		if (!$fileContent = file_get_contents($infile)) return -1; // can't access file
		preg_match('/<metadata.*?>.*<cc:Work.*?>(.*)<\/cc:Work>.*<\/metadata>/s', $fileContent, $match);
		
		if (!isset($match[1])) return false;
		$metaRaw = $match[1];
		
		preg_match('/<dc:title>(.*?)<\/dc:title>/s', $metaRaw, $match);		
		$meta['Title'] = isset($match[1]) ? $match[1] : '';
		
		preg_match('/<dc:date>(.*)<\/dc:date>/s', $metaRaw, $match);		
		$meta['Date'] = isset($match[1]) ? $match[1] : '';
		
		preg_match('/<dc:publisher>.*<cc:Agent.*?>.*<dc:title.*?>(.*)<\/dc:title>.*<\/cc:Agent>.*<\/dc:publisher>/s', $metaRaw, $match);		
		$meta['Publisher'] = isset($match[1]) ? $match[1] : '';
		
		preg_match('/<dc:language>(.*)<\/dc:language>/s', $metaRaw, $match);		
		$meta['Language'] = isset($match[1]) ? $match[1] : '';
		
		preg_match('/<dc:description>(.*)<\/dc:description>/s', $metaRaw, $match);		
		$meta['Description'] = isset($match[1]) ? $match[1] : '';
		
		$meta = array_map('html_entity_decode', $meta);
		$meta = array_map('trim', $meta);
		
		$meta['Tags'] = '';	
		$meta['TagsArray'] = [];
		preg_match('/<dc:subject>.*<rdf:Bag>(.*)<\/rdf:Bag>.*<\/dc:subject>/s', $fileContent, $match);
		if (isset($match[1])) {
		preg_match_all('/<rdf:li>(.*?)<\/rdf:li>/s', $match[1], $match);		
		$meta['TagsArray'] = isset($match[1]) ? $match[1] : [];			
		foreach ($meta['TagsArray'] as $key => &$keyword) {
		$keyword = trim(html_entity_decode($keyword));
		if ($keyword !== '') $meta['Tags'] .= "#{$keyword} "; else unset($meta['TagsArray'][$key]);
		}
		
		$meta['Tags'] = strlen($meta['Tags']) ? substr($meta['Tags'], 0, -1) : '';
		}
		
		return $meta;
	}
	