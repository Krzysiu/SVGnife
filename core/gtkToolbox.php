<?
	
	define('GT_TOGGLE_SENSITIVE', 1);
	define('GT_TOGGLE_VISIBLE', 2);
	
	function gtGetWidget($widget) {
		// Gets widget providing either name or widget object. It allows to provide
		// either object or string as parameter to most, if not all, gtkToolbox 
		// functions.	
		global $glade;
		
		if (gettype($widget) === 'string') return $glade->get_widget($widget); 
		elseif (gettype($widget) === 'object') return $widget; 
		
		return false;
	}
	
	function gtType($widget) {
		// Gets type of widget. Also used internally in gtkToolbox.
		$type = get_class(gtGetWidget($widget));
		
		if (substr($type, 0, 3) === 'Gtk') return substr($type, 3);
	}
	
	function gtIcon($widget, $icon, $size = Gtk::ICON_SIZE_BUTTON) {
		// General function - set stock icon
		$type = gtType($widget);
		
		if ($type === 'Image') gtGetWidget($widget)->set_from_stock($icon, $size);
		if ($type === 'Button') gtGetWidget($widget)->set_image(GtkImage::new_from_stock($icon, $size));
	}
	
	function gtColor($widget, $type, $color, $state = Gtk::STATE_NORMAL) {
		// General function - set fb or bg color
		$widget = gtGetWidget($widget);
		
		if ($type === 'bg') 	$widget->modify_bg($state, GdkColor::parse($color));
		if ($type === 'fg') 	$widget->modify_fg($state, GdkColor::parse($color));
	}
	
	function gtFont($widget, $font) {
		// General function - set font
		$widget = gtGetWidget($widget);
		
		$widget->modify_font(new PangoFontDescription($font));
	}
	
	function gtSetText($widget, $text) {
		// General function - set text
		$type = gtType($widget);
		$widget = gtGetWidget($widget);
		
		if ($type === 'TextView') {
			$textBuffer = new GtkTextBuffer();
			$textBuffer->set_text($text);
			$widget->set_buffer($textBuffer);				
		} else $widget->set_text($text);
	}
	
	function gtGetText($widget) {
		// General function - set text
		$type = gtType($widget);
		$widget = gtGetWidget($widget);
		
		if ($type === 'TextView') {
			$buffer = $widget->get_buffer();
			return $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());  
		} else return $widget->get_text();
	}
	
	function gtFileFilter($widget, $pattern, $name, $nameExtSuffix = true) {
		// Add file filter for file chooser (including button). If $nameExtSuffix
		// is set to true, suffix with pattern in parenthesis will be added to name
		$widget = gtGetWidget($widget);
		$fileFilter = new GtkFileFilter();
		$fileFilter->add_pattern($pattern);
		if ($nameExtSuffix && ($pattern !== '*')) $name = "{$name} ({$pattern})";
		$fileFilter->set_name($name);
		$widget->add_filter($fileFilter);
	}	
	
	function gtShow($widget, $state) {
		// Shows or hides element using boolean value
		$widget = gtGetWidget($widget);
		
		if ($state) $widget->show(); else $widget->hide();
	}
	
	function gtEnable($widget, $state) {
		// gtToolbox wrapper for set_sensitive
		$widget = gtGetWidget($widget);
		$widget->set_sensitive($state);
	}
	
	function gtToggler($widgetIn, $widgetOut, $mode = GT_TOGGLE_SENSITIVE, $reversed = false) {
	$widgetIn = gtGetWidget($widgetIn);
	$widgetOut = gtGetWidget($widgetOut);
	
	$state = $reversed ? !$widgetIn->get_active() : $widgetIn->get_active();

	if ($mode === GT_TOGGLE_SENSITIVE) $widgetOut->set_sensitive($state);
	if ($mode === GT_TOGGLE_VISIBLE) gtShow($widgetOut, $state);
	// 
	}
	
	function gtSpin($widget, $state) {
		// Starts or stops spinner using boolean value
		$widget = gtGetWidget($widget);
		$type = gtType($widget);
		
		if ($type !== 'Spinner') return false;
		if ($state) $widget->start(); else $widget->stop();
	}
	