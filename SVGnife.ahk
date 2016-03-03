; Windows runner script. Compiled version available in setup package.

#NoEnv ; avoid conflicts with system env variables
#NoTrayIcon

SetWorkingDir %A_ScriptDir% ; set working directory as directory of the application
	
; global variables init
guiLabelCurr := ""
btnMode := -1
btnMode2 := -1
	
IfExist %A_ScriptDir%\.firstrun
{
	appTitle = Missing requirement
	guiLabelDots = 1
	guiLabelDotsDelay = 700
	guiWidth = 400
	downloadTarget = %A_Temp%\vcredist_2012_x86.exe

	; Get local names of message box buttons
	dllStr := DllCall("LoadLibrary", "str", "user32.dll") ; loads user32.dll, optionally with user32.dll.mui (local strings) in some systems
	dllBufSize = 32 ; 32 chars, even for unicode, should be ok for yes/no/cancel
	VarSetCapacity(strYes, dllBufSize) ; setting buffer size
	VarSetCapacity(strNo, dllBufSize)
	VarSetCapacity(strCancel, dllBufSize)
	DllCall("LoadString", "uint", dllStr, "uint", 805, "str", strYes, "int", dllBufSize) ; getting user32.dll strings
	DllCall("LoadString", "uint", dllStr, "uint", 806, "str", strNo, "int", dllBufSize)
	DllCall("LoadString", "uint", dllStr, "uint", 801, "str", strCancel, "int", dllBufSize)
	DllCall("FreeLibrary", "Ptr", dllStr)

	StringReplace, strYes, strYes, % "&" ; removing &, as we will use it in text, not buttons
	StringReplace, strNo, strNo, % "&"
	StringReplace, strCancel, strCancel, % "&"

	; Check is VC2012 x86 is installed
	vcRegPrefix32 := ""
	If A_Is64bitOS = 1
	{
		vcRegPrefix32 = \Wow6432Node ; set correct reg key for 64-bit OS
	}
	vcRegKey := "HKLM\SOFTWARE" vcRegPrefix32 "\Microsoft\DevDiv\VC\Servicing\11.0\RuntimeMinimum"
	RegRead, regVal, %vcRegKey%, Install

	If regVal != 1 ; if VC2012 x86 is not installed...
	{
		MsgBox, 0x1033, %appTitle%, This application requires Visual C++ Redistributable for Visual Studio 2012 x86. Do you want to download (~6 MB) and install it?`n`nPress:`n• "%strYes%" to proceed`n• "%strNo%" to ignore it and run application anyways (not recommended!)`n• "%strCancel%" to cancel and exit application
		
		IfMsgBox, No ; forced run
			RunSVGNife()
		
		IfMsgBox, Cancel
			ExitApp
		
		; the rest will process only when user clicked on yes
		
		OnExit, ExitHandler ; add exit handler (del temp files, only needed when installing VC++)

		Gui, Add, Text, vTopLabel w%guiWidth% h50 x0 y10 center, Please wait.
		Gui, Add, Text, vActionLabel w%guiWidth% x0 y30 center, ...
		Gui Add, Button, x140 y60 w120 h40 gActionButton vActionButton, Working...
		GuiControl, Disable, ActionButton
		Gui, Show, w%guiWidth% h150 , %appTitle%

		changeDynLabel("Downloading Visual C++ Redistributable (~6 MB)")
		UrlDownloadToFile, https://download.microsoft.com/download/1/6/B/16B06F60-3B20-4FF2-B699-5E9B7962F9AE/VSU_4/vcredist_x86.exe, %downloadTarget% ; download VC redist
		If ErrorLevel 
		{
			lastStep("Problems with downloading requirement.`n`nCheck your Internet connection.", "Exit", 1, "&Try again", 2) ; try again just reboot app
			return
		} Else { ; download complete
			changeDynLabel("Installing Visual C++ Redistributable")
			RunWait, %downloadTarget% /q /norestart ; quiet install
			If ErrorLevel = 3010 ; installation is fine, yet user have to reboot computer
			{
				lastStep("Visual C++ Redistributable installed.`n`nYou have to reboot your computer.", "Reboot now", 2, "Exit and manually`nreboot later", 1)
				return
			} Else if ErrorLevel ; installation failed
			{
				lastStep("Visual C++ Redistributable installation failed.", "Exit", 1, "Try to run application", 3)
				return
			} Else { ; everything's fine
				lastStep("Installation complete`n`n`You may run the application now.", "Run SVGnife", -1)
			}
		}
	} Else 
			RunSVGNife(1) ; VC found, run app
} Else
	RunSVGNife() ; not a first time run, so just run
return

ActionButton:
; actions of the first, always visible button
global btnMode

if btnMode = -1 ; everything is OK
	RunSVGNife(1)
	
if btnMode = 1 ; download error
	ExitApp	

if btnMode = 2 ; VC installed, but reboot is needed
{
	Shutdown, 2 ; 2 means reboot without force
	ExitApp
}
		
return

SecondButton:
; actions of the second, optional button; for modes see lastStep() function
global btnMode2

if btnMode2 = 1
	ExitApp
	
if btnMode2 = 2
{
	Reload
	Sleep 2000 ; timeout 2s
	ExitApp ; in case if app didn't get rebooted
}

if btnMode2 = 3
	RunSVGNife()	
	
return

workingLabel:
; timer action to animate dots on the end of the label, while loader is working; for modes see lastStep() function
	If guiLabelDots = 3 ; maximum 3 dots
		guiLabelDots = 0
	Else
		guiLabelDots++
	
	changeDynLabel(guiLabelCurr, 1) ; refresh dots in label
return

ExitHandler:
IfExist, %downloadTarget%
	FileDelete, %downloadTarget%
ExitApp
return


RunSVGNife(delFirstRun := 0) {
	; run application
	global btnMode
	
	if delFirstRun
		FileDelete, %A_ScriptDir%\.firstrun ; if it's non-forced run, delete file created by setup, to don't check for VC anymore
	; if forced run will be successful, SVGnife will delete .firstrun anyways
	Run, php-gtk\php-win.exe core\SVGnife.php "%1%"
	ExitApp
}

lastStep(info, btnLabel, action, btn2Label := "", action2 := -1) {
	global btnMode
	; GUI change on the last step, either error or not
	; if second pair of parameters is given, adds new buton
	
	btnMode := action ; sets action for first button
	
	SetTimer, workingLabel, off ; turn off timer
	GuiControl, Hide, ActionLabel ; hide dynamic, animated layer label
	GuiControl,, ActionButton, % btnLabel ; set the label to the main button
	GuiControl, Enable, ActionButton
	GuiControl,, TopLabel, % info ; set info label
	
	if btn2Label ; if 3rd parameter is provided, add 2nd button
		addSecondButton(btn2Label, action2)	
}

addSecondButton(btnLabel, action2) {
	; Moves the first button to the left and adds second button
	global btnMode2
	
	Gui Add, Button, x205 y60 w120 h40 gSecondButton, % btnLabel
	GuiControl, Move, ActionButton, x75
	btnMode2 := action2 ; sets action for the second button
	return
}

	
changeDynLabel(newLabel, refreshLabel := 0) {
	; sets dynamic/animated label or updated dots at the end
	global guiLabelDotsDelay, guiLabelDots, guiLabelCurr
	
	If !refreshLabel
		{
			; if new label is set
			guiLabelCurr = %newLabel% ; current label to reuse by timer workingLabel
			SetTimer, workingLabel, %guiLabelDotsDelay% ; sets the timer
			guiLabelDots = 0 ; reset dot count
			GuiControl,, ActionLabel, % newLabel ; sets the label for the first time, because sometimes timer doesn't run for the first time
		}
	
	GuiControl,, ActionLabel, % newLabel StringRepeat(".", guiLabelDots)
	
	return
}

StringRepeat(str, count)
{
	; string repeat function, used here only to generate animated dots
  Loop, %count%
    ret .= str
  return ret
}