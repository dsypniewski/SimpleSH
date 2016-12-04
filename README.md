SimpleSH is continuation of a project started a long long time ago, started due to lack of certain features in most popular web shells mainly autocomplete.  
This project's aim is to provide simple, familiar and powerful interface whenever classic shell access is not possible.  
While writing this i had in mind maximum backwards compatibility with older PHP versions and i know for sure that it works with PHP 5.3 and 5.5 but it should also work from PHP 5.1.2 up to PHP 7.1  
As far as browser support goes i did not bother with it much, support for backend is much more needed here, it should work great on most modern desktop browser. Mobile is right now a little bit wired since i don't have much experience with it but it kinda works.

## Build
1. make sure you have TypeScript compiler (tsc) and SCSS (sass) installed without them it will go BOOM
2. run `make` or `make build` or `make clean-build` whichever you prefer (or need)
3. bam `shell.php` is ready (at least is should be if it isn't let me know what went wrong)

## Features
1. Much better support for BASH autocomplete. (Yes you read that right)
2. Pooling long running command results, enable it with `enable_dynamic_output` and disable with `disable_dynamic_output`
3. Multiple windows for all modules (right now only Terminal and FileBrowser, more will be coming)
4. Command history
5. Different shell-s available: sh, bash, php, perl, python, nodejs, cmd (windows), powershell (windows), use with `set_shell <shell_name>` (prompt updates after first command @TODO)
6. Automatic focus to command input on typing
7. Protection against accidental window closing (and losing everything due to stupid Ctrl + W that closes the tab instead extending selection, duh)
8. Simple directory browser (for now lacking file preview)
9. And some other things that i forgot about while writing this

## Server requirements (subject to change, not all are present)
* Web server with PHP 5.3+ (should work from PHP 5.1.2 but not really tested yet)
* fileinfo extension enabled (for FileBrowser)

## Global shortcuts
`Ctrl + Q` - close window  
`Ctrl + Insert` - new window popup  
`Ctrl + Alt + Left / Right` - switch between windows  
`Ctrl + Alt + F1 ... F12` - switch between windows  
`Shift + Up` - maximize window  
`Shift + Down` - revert maximize window  
`Shift + Left` - stick window tho the left side  
`Shift + Right` - stick window tho the right side  

## Terminal shortcuts
`Tab` - autocomplete command (works only in BASH)  
`Enter` - execute command, obviously  
`Up` & `Down` - move through command history  
`Esc` - clear command  
`Home` & `End` - move to command start / end  
`Ctrl + Home / End` - scroll console to top / bottom  
`Ctrl + C` - break dynamic output command / clear command / copy (if command is empty)  
`Ctrl + V` - always pastes into command input  
`Ctrl + A` - always selects whole command  
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
* New modules ideas (lets be honest mostly based on other web shells)
  * SystemInfo - providing basic information about the host
  * ProcessExplorer - as the name suggests, pretty replacement for ps, top or whatever you might use
  * SQLBrowser - MySQL, PostgreSQL and whatever PDO can handle
  * ReverseShell - maybe providing reverse shell back to metasploit, among other ways
  * P0wnModule - built in local privilege escalation exploits using public POC eg. Dirty COW, Rowhammer, semtex.c, docker (Don't know how legal that would be, but i guess since metasploit and other tools do this then it should be fine)
  * NetworkTools - some basic network diagnostic tools, eg. ping, traceroute, dns lookup, hosts file, network interfaces etc
  * DataTools - data analysis / conversion tools eg. md5, random data, base64, histograms
