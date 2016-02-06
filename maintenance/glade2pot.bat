@echo off
setlocal EnableDelayedExpansion
rem Config
rem 1. Gettext binary
set gtBin="C:\Program Files\^!Programowanie\getText\xgettext.exe"
rem End of config

echo Glade to gettext .po files
echo ==========================
echo.

if [%1]==[] (
echo Usage:
echo glade2pot.bat language-code
echo.
echo Example:
echo glade2pot.bat de_DE
exit /b
)

if not exist !gtBin! (
echo gettext binary not found.
echo Edit code of %~nx0 to set the binary path.
exit /b
)

for %%f in ("..\resources\*.glade") do (
set outputFile="..\i18n\glade-source\%1.%%~nf.po"
set gtUpdate=
if exist !outputFile! set gtUpdate=--join-existing
<nul set /p tmpEcho=Running xgettext with %%~nxf...
!gtBin! -o !outputFile! --language=Glade !gtUpdate! --force-po "%%f"
<nul set /p tmpEcho=. Done!
echo.

)

endlocal