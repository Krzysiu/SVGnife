# SVGnife
SVG preview, metadata reader and OpenClipart.org uploader in PHP-GTK

Warning! It's still in alpha version. 

This version assumes that PHP-GTK is installed in c:\php-gtk\

The working directory have to be set to main directory and the file to run is `core\SVGnife.php`. You can run it via `run.bat`.

PHP-GTK downloads: http://gtk.php.net/download.php (use PHP 5.5/GTK+ 2.24.10 version or newer).

Requiments:
* Base:
** PHP-GTK for PHP 5.5
*** Extenstion: `fileinfo`
** [Windows only] Visual C++ Redistributable for Visual Studio 2012 Update 4 (x86 version! https://www.microsoft.com/en-us/download/details.aspx?id=30679)

* For preview
** Inkscape
** [Windows only] PHP extension `com_dotnet`

* For uploads
** PHP extensions `openssl` and `curl`
