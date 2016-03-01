; Windows runner script. Compiled version available in setup package.

SetWorkingDir %A_ScriptDir%
Run, php-gtk\php-win.exe core\SVGnife.php "%1%"