@echo off
chcp 65001 > nul
setlocal
set dlcver=b2
set dlcname=SVGnife Debug Log Creator
set dlccmd=php-gtk\php.exe "core\SVGnife.php" %1
set dlcdebugpart=0

echo %dlcname% for Windows, version %dlcver%
echo.
echo Select menu item by pressing corresponding key.
<nul set /p tmpEcho=.  Key   Action & echo.
<nul set /p tmpEcho=. ----- --------------------------------------------------------------------- & echo.
<nul set /p tmpEcho=.  d     run debug & echo. & echo.

<nul set /p tmpEcho=.  o     don't log, just show PHP output to console & echo. & echo.

<nul set /p tmpEcho=.  p     run partial debug & echo.
<nul set /p tmpEcho=.        (that option in recommended, yet you may be asked to run it) & echo. & echo.

<nul set /p tmpEcho=.  w     open GitHub application issues webpage & echo.
<nul set /p tmpEcho=.        (the best option, allows other developers/users to check the log) & echo. & echo.

<nul set /p tmpEcho=.  c     open developer's private contact webpage & echo.
<nul set /p tmpEcho=.        (use it only when there's no other way. Please, send note if you & echo.
<nul set /p tmpEcho=.        DON'T agree to publishing the log.) & echo. & echo.

<nul set /p tmpEcho=.  q     quit & echo. & echo.

choice /C:dopwcq /N /M "Please select item: "
if errorlevel 6 goto menu_q
if errorlevel 5 goto menu_c
if errorlevel 4 goto menu_w
if errorlevel 3 (
set dlcdebugpart=1
goto menu_d
)
if errorlevel 2 goto menu_o
if errorlevel 1 goto menu_d

:menu_d
echo %dlcname% > debug.log
echo. >> debug.log

echo SYSTEM DATA >> debug.log
echo =========== >> debug.log
echo SVGnife DLC version: %dlcver% >> debug.log
echo Start logging time: %DATE% %TIME% >> debug.log
if %dlcdebugpart%==1 (
echo Log type: partial >> debug.log
) else (
echo Log type: full >> debug.log
)
<nul set /p tmpEcho=Windows version: >> debug.log
ver | findstr /C:"Windows" >> debug.log
<nul set /p tmpEcho=SVGnife version: >> debug.log
type core\config.php | findstr /C:"$appVer =" >> debug.log
echo PHP command line: %dlccmd% >> debug.log
echo Current working directory: %CD% >> debug.log

echo. >> debug.log
echo MAIN FILES >> debug.log
echo ========== >> debug.log
if exist php-gtk\php.exe (
echo PHP found >> debug.log
) else (
echo PHP not found >> debug.log
)

if exist core\svgnife.php (
echo Main script file found >> debug.log
) else (
echo Main script file not found >> debug.log
)

if exist svgnife.ini (
echo Configuration file found >> debug.log
) else (
echo Configuration file not found >> debug.log
)

echo. >> debug.log
echo. >> debug.log
echo FILE LISTING >> debug.log
echo ============ >> debug.log
dir /b /s >> debug.log

if %dlcdebugpart%==1 goto debugpartend
echo. >> debug.log
echo RUNNING SVGNIFE >> debug.log
echo =============== >> debug.log
set svgnife-dlc=1
cmd /c %dlccmd% >> debug.log 2>&1
echo. >> debug.log
echo PHP exit status: %ERRORLEVEL% >> debug.log

:debugpartend
echo. >> debug.log
echo ================ >> debug.log
echo END OF DEBUG LOG >> debug.log

debug.log
goto menu_q

:menu_o
cmd /c %dlccmd%
pause
goto menu_q

:menu_w
start https://github.com/Krzysiu/SVGnife/issues/new
goto menu_q

:menu_c
start http://krzysiu.net/contact
goto menu_q

:menu_q
endlocal
