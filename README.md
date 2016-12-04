SimpleSH is continuation of a project started a long long time ago due to lack of certain features in web shells, mainly autocomplete and dynamic output.  
This project's aim is to provide simple and familiar yet powerful interface whenever classic shell access is not possible.  
While writing this i had in mind maximum backwards compatibility with older PHP versions. I know for sure that it works with PHP 5.3 and 5.5. In theory it should work with PHP versions from 5.1.2. I'm hoping to achieve support from PHP 5.0 but we will see about that.  
Since support for older versions of PHP is much more needed here i chose not to spend time on supporting older browsers, it should work great on most up-to-date desktop browser (and how many people interested in this kind of project would willingly use older browsers anyway). Maybe someday. On mobile right now it kinda works, the experience can be better and that definitely is something that i would like to focus on in the future.  

## Build
1. Make sure you have `tsc` (TypeScript), `sass` (Sass/SCSS) and `make` installed and available in your $PATH, without them it will go **BOOM** and we don't want that
2. Run `make` or `make build` or `make clean-build`, whichever you prefer (or need)
3. Bam `shell.php` is ready (at least it should be, if it isn't let me know what went wrong and i'll try to fix it)

## Features
1. Bash autocomplete - no more blindly typing whole file paths by hand (and whatever else bash can complete)
2. Dynamic output - pooling long running command result, enable it with `enable_dynamic_output` and disable with `disable_dynamic_output` (requires a directory with write access)
3. Multiple windows - for all modules that need their space (right now only Terminal and FileBrowser, more will be coming)
4. Command history - for now only moving backwards and forwards but more is coming eg. Ctrl + R
5. Different shells available: sh, bash, php, perl, python, nodejs, cmd, powershell, use with `set_shell <shell_name>`
6. Automatic focus on command input when typing
7. Protection against accidental window closing (and losing everything due to Ctrl + W that closes the tab instead extending selection, duh)
8. Simple directory browser (file preview WIP)
9. And some other things that i forgot about while writing this

## Server requirements (incomplete)
* Web server with PHP 5.3+ (in theory should work from PHP 5.1.2 but not tested yet)
* At least one not disabled php function for executing commands (Terminal module)
* fileinfo extension enabled (FileBrowser module)

## Global shortcuts
`Ctrl + Q` - close window  
`Ctrl + Insert` - new window popup  
`Ctrl + Alt + Left / Right` - switch between windows  
`Ctrl + Alt + F1..F12` - switch between windows  
`Shift + Up` - maximize window  
`Shift + Down` - revert maximize window  
`Shift + Left` - stick window tho the left side  
`Shift + Right` - stick window tho the right side  

## Terminal shortcuts
`Tab` - autocomplete command (works only in Bash)  
`Enter` - execute command, obviously  
`Up` & `Down` - move through command history  
`Esc` - clear command line  
`Home` & `End` - move to command start / end  
`Ctrl + Home / End` - scroll console to top / bottom  
`Ctrl + C` - break dynamic output command / clear command line / copy (if command is empty)  
`Ctrl + V` - always pastes into command line  
`Ctrl + A` - always selects whole command line content  
`ScrollLock` - lock output scrolling (works great with dynamic output)  

## FileBrowser shortcuts
`Up` & `Down` - move in directory listing  
`Enter` - enter directory  

## TODO
* Add actual file preview functionality to FileBrowser module
* Add window resizing
* Making it look cleaner, it isn't bad right now but it could be better
* Add optional password and/or source ip protection and camouflage (404 when not authenticated) to build script
* Multi-line script handling for python and alike
* Finish implementing translations
* Add self-delete
* Update prompt on shell change
* New modules ideas (lets be honest mostly based on other web shells)
  * SystemInfo - providing basic information about the host
  * ProcessExplorer - as the name suggests, pretty replacement for ps, top or whatever you might use
  * SQLBrowser - MySQL, PostgreSQL and whatever PDO can handle
  * ReverseShell - maybe providing reverse shell back to metasploit, among other ways
  * P0wnModule - built in local privilege escalation exploits using public POC eg. Dirty COW, Rowhammer, semtex.c, docker (Don't know how legal that would be, but i guess since metasploit and other tools do this then it should be fine)
  * NetworkTools - some basic network diagnostic tools, eg. ping, traceroute, dns lookup, hosts file, network interfaces etc
  * DataTools - data analysis / conversion tools eg. md5, random data, base64, histograms
