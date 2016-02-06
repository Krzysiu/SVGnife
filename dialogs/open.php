<?
	$dialogOpen = null;
	
	function openDialogChooseFile() {
		global $dialogOpen;
		
		$_fileChooser = $dialogOpen->get_widget('_fileChooser');
		if (isset($_fileChooser->get_filenames()[0]) && file_exists($_fileChooser->get_filenames()[0])) {
			readSVG($_fileChooser->get_filenames()[0]);
			$_fileChooser->destroy();
		}
	}
	
	function openDialogCheckFile() {
		global $dialogOpen;
		
		$_fileChooser = $dialogOpen->get_widget('_fileChooser');
		$dialogOpen->get_widget('_btnOpen')->set_sensitive((isset($_fileChooser->get_filenames()[0]) && file_exists($_fileChooser->get_filenames()[0])));
		
	}
	
	function openDialogCancel() {
		global $dialogOpen;
		
		$dialogOpen->get_widget('_fileChooser')->destroy();
	}
	
	
	function showOpenDialog() {
		global $dialogOpen, $fileList;
		$dialogOpen = new GladeXML('resources/dialogOpen.glade');
		
		$_fileChooser = $dialogOpen->get_widget('_fileChooser'); 
		$_btnOpen = $dialogOpen->get_widget('_btnOpen'); 
		$_btnCancel = $dialogOpen->get_widget('_btnCancel'); 
		
		$_btnOpen->connect_simple('clicked', 'openDialogChooseFile');
		$_fileChooser->connect_simple('file-activated', 'openDialogChooseFile');
		$_fileChooser->connect_simple('selection-changed', 'openDialogCheckFile');
		$_btnCancel->connect_simple('clicked', 'openDialogCancel');	
		
		gtIcon($_btnOpen, Gtk::STOCK_OPEN);
		gtIcon($_btnCancel, Gtk::STOCK_CANCEL);
		gtFileFilter($_fileChooser, '*.svg', _('SVG files'));
		gtFileFilter($_fileChooser, '*', _('All files'));
	
		if (isset($fileList['directory'])) $_fileChooser->select_uri($fileList['currentFile']);		
	}	
	