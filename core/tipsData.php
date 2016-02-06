<?php
	// Tip of the day, displayed on the bottom of the main window
	// You can use some HTML tags in messages. That's magical.
	
	$tips['prefix'] = _('<b>Did you know that</b> ');
	$tips['tips'] = [
	_("you can drop the file on the window to open it?"),
	_("the bold labels in main window\nare the fields that would be uploaded to OpenCliparts.org?"),
	_("you can edit metadata which would be\nsend to OpenCliparts.org?"),
	_("you can hide this bar in preferences?"),
	_("you can navigate thought the files\nusing Ctrl+arrows or Ctrl+Shift+arrows?"),
	_("you can preview whole drawing area\nwhich sometimes is quite different than page area?")
	];
	
	$tips['current'] = array_rand($tips['tips']);
