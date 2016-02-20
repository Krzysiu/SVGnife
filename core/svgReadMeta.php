<?
	
	function getSVGMetadata($infile) {
	if (!$fileContent = file_get_contents($infile)) return -1; // can't access file
	
	$svgMetaRoot = '/svg:svg/svg:metadata/rdf:RDF/cc:Work';
	$svgElements = [
	'Title' => 'dc:title',
	'Date' => 'dc:date',
	'Publisher' => 'dc:publisher/cc:Agent/dc:title',
	'Language' => 'dc:language',
	'Description' => 'dc:description'	
	];
	$meta = [];
	
	$xml = new SimpleXMLElement($fileContent);
	
	if (!$xml->xpath($svgMetaRoot)) return false; // metadata block not found
	
	foreach ($svgElements as $title => $path) {
	$el = $xml->xpath("{$svgMetaRoot}/{$path}");
	$meta[$title] = $el ? $el[0]->__toString() : '';
	}
	
	// getting keywords
	$meta['Tags'] = '';
	$svgKeywords = $xml->xpath("{$svgMetaRoot}/dc:subject/rdf:Bag/rdf:li");
	
	if ($svgKeywords) {
	foreach ($svgKeywords as $keyword) {
	$keyword = trim($keyword->__toString());
	if ($keyword)	$meta['Tags'] .= "{$keyword}, ";
	}
	$meta['Tags'] = substr($meta['Tags'], 0, -2);
	}
	$meta = array_map('trim', $meta);
		
		return $meta;
	}
	