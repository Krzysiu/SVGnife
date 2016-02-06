@echo This is the debug file. > debug.log
@echo If you see any non-empty lines below, you should send back them to the author >> debug.log
c:\php-gtk\php.exe "C:\Skrypty\!Aplikacje\SVG Metadata reader\core\SVGnife.php" %1 >> debug.log 2>&1
debug.log
pause