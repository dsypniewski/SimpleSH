SimpleSH is continuation of a project started a long long time ago, and now a much better version is available.

Since first single-file versions that were available on my website a lot has changed, now project is spread over multiple files and `make` has been put to work to build it into one file.

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

## Global shortcuts
`Ctrl + Q` - close window  
`Ctrl + Insert` - new window popup  
`Ctrl + Alt + Left / Right` - switch between windows  
`Ctrl + Alt + F1 ... F12` - switch between windows  
`Shift + Up` - maximize window  
`Shift + Down` - revert maximize window  
`Shift + Left` - stick window tho the left side  
`Shift + Right` - stick window tho the right side  

## Console shortcuts
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
