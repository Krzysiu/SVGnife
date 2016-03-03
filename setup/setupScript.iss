; Inno Setup install script for SVGnife.
; If you want to build setup by yourself, please change paths.

#define MyAppName "SVGnife"
#define MyAppVersion "0.1.5"
#define MyAppPublisher "krzysiu.net"
#define MyAppURL "https://github.com/Krzysiu/SVGnife"
#define MyAppExeName "SVGnife.exe"

[Setup]
AppId={{0E454E1C-6F7B-49C6-B87E-B2F7B7936322}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={pf}\SVGnife
DefaultGroupName=SVGnife
DisableProgramGroupPage=auto
LicenseFile=C:\Skrypty\!Aplikacje\SVGnife\setup\setupLicense.txt
InfoBeforeFile=C:\Skrypty\!Aplikacje\SVGnife\CHANGELOG
OutputDir=C:\Skrypty\!Aplikacje\SVGnife\setup\bin
OutputBaseFilename=svgnife_setup
SetupIconFile=C:\Skrypty\!Aplikacje\SVGnife\resources\SVGnife.ico
SolidCompression=yes
AppCopyright=krzysiu.net
RestartIfNeededByRun=False
ShowLanguageDialog=no
CloseApplications=False
RestartApplications=False
VersionInfoVersion=0.1.5
VersionInfoCompany=krzysiu.net
VersionInfoDescription=SVGnife setup
VersionInfoCopyright=krzysiu.net
VersionInfoProductName=SVGnife setup

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Files]
Source: "..\SVGnife.exe"; DestDir: "{app}"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\CHANGELOG"; DestDir: "{app}"; DestName: "changelog.txt"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\debug.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\KEYBOARD"; DestDir: "{app}"; DestName: "keyboard.txt"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\LICENSE"; DestDir: "{app}"; DestName: "license.txt"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\README.md"; DestDir: "{app}"; DestName: "readme.txt"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\TODO"; DestDir: "{app}"; DestName: "todo.txt"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\setup\.firstrun"; DestDir: "{app}"; Flags: ignoreversion
Source: "C:\Skrypty\!Aplikacje\SVGnife\core\*"; DestDir: "{app}\core"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "C:\Skrypty\!Aplikacje\SVGnife\dialogs\*"; DestDir: "{app}\dialogs"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "C:\Skrypty\!Aplikacje\SVGnife\i18n\*"; DestDir: "{app}\i18n"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "C:\Skrypty\!Aplikacje\SVGnife\lib\*"; DestDir: "{app}\lib"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "C:\Skrypty\!Aplikacje\SVGnife\php-gtk\*"; DestDir: "{app}\php-gtk"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "C:\Skrypty\!Aplikacje\SVGnife\resources\*"; DestDir: "{app}\resources"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{group}\{cm:UninstallProgram,{#MyAppName}}"; Filename: "{uninstallexe}"
Name: "{commondesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#MyAppName}}"; Flags: nowait postinstall skipifsilent