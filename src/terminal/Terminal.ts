//noinspection JSUnusedGlobalSymbols
class Terminal extends Module {

	protected static nextId = 0;
	protected readonly form: JQuery;
	protected readonly commandInput: JQuery;
	protected readonly currentDirectoryInput: JQuery;
	protected readonly shellClass: JQuery;
	protected readonly outputWrapper: JQuery;
	protected readonly outputBuffer: JQuery;
	protected readonly interpreterPath: JQuery;
	protected readonly prompt: JQuery;
	protected readonly ttyId: number;
	protected history: Array<string> = [];
	protected historyPosition: number = -1;
	protected currentCommand: string|null = null;
	protected dynamicOutputEnabled: boolean = false;
	protected dynamicOutputReference: string|null = null;
	protected dynamicOutputRequestFinished: boolean = true;
	protected dynamicOutputInterval: number|null = null;
	protected scrollLock: boolean = false;

	constructor(windowManager: WindowManager, moduleKey: string) {
		super(windowManager, moduleKey);

		let directory = windowManager.getModuleData(this, 'directory', '');
		let shellClass = windowManager.getModuleData(this, 'shellClass', '');
		let interpreterPath = windowManager.getModuleData(this, 'interpreterPath', '');
		let prompt = windowManager.getModuleData(this, 'prompt', '');
		this.outputWrapper = $('<div/>').addClass('output-wrapper');
		this.outputBuffer = $('<pre/>').addClass('output-buffer');
		this.commandInput = $('<input/>').addClass('command-input').prop('type', 'text').prop('name', 'command').prop('autocomplete', 'off').prop('autocorrect', 'off').prop('autocapitalize', 'off').prop('spellcheck', false);
		this.currentDirectoryInput = $('<input/>').prop('type', 'hidden').prop('name', 'cwd').val(directory);
		this.shellClass = $('<input/>').prop('type', 'hidden').prop('name', 'shellClass').val(shellClass);
		this.interpreterPath = $('<input/>').prop('type', 'hidden').prop('name', 'interpreterPath').val(interpreterPath);
		this.form = $('<form/>').addClass('command-form').prop('method', 'post');
		this.prompt = $('<span/>').text(prompt);
		this.ttyId = Terminal.nextId++;
	}

	// Base implementation methods required by WindowManager
	public build(): JQuery {
		this.outputWrapper.append(this.outputBuffer);

		let table = $('<table/>').append(
			$('<tr/>').append(
				$('<td/>').addClass('command-prompt').append(this.prompt)
			).append(
				$('<td/>').addClass('command-input-container').append(this.commandInput)
			)
		);
		this.form.append(table);
		this.form.append(this.currentDirectoryInput);
		this.form.append(this.shellClass);
		this.form.append(this.interpreterPath);
		this.form.append($('<input/>').prop('type', 'hidden').prop('name', 'module').val(this.getModuleKey()));

		return $(this.outputWrapper).add(this.form);
	}

	public getWindowTitle(): string {
		return 'tty' + this.ttyId;
	}

	public showHelp(): void {
	}

	public onWindowFocus(): void {
		this.updateStatus();
	}

	protected updateStatus(): void {
		let _OFS = function (status: boolean) {
			return (status ? ' ON' : 'OFF');
		};
		this.windowManager.setStatus('SL: ' + _OFS(this.scrollLock) + ' CL: ' + _OFS(this.windowManager.getCapsLockStatus()) + ' DO: ' + _OFS(this.dynamicOutputEnabled));
	}

	updateTitle(title: string): void {
		this.windowManager.updateTitle(this.windowId, title);
	}

	// History manipulation methods
	protected addToHistory(command: string): void {
		if (this.history[0] === command) {
			return;
		}
		this.history.unshift(command);
	}

	protected moveHistoryCursor(position: number|null, relative: boolean): void {
		if (relative) {
			position += this.historyPosition;
		}
		if (position < 0 || position === null) {
			position = -1;
		}
		if (position > this.history.length - 1) {
			position = this.history.length - 1;
		}
		if (this.historyPosition === -1 && this.getCommand().length > 0) {
			this.currentCommand = this.getCommand();
		}
		if (position === this.historyPosition) {
			return;
		}
		this.historyPosition = position;
		if (this.historyPosition === -1) {
			if (this.currentCommand !== null) {
				this.setCommand(this.currentCommand);
				this.currentCommand = null;
			} else {
				this.clearCommandInput();
			}
			return;
		}
		this.setCommand(this.history[this.historyPosition]);
		let _this = this;
		setTimeout(function () {
			_this.moveCommandInputCursorToEnd();
		}, 1);
	}

	// Console manipulation methods
	protected appendToConsole(data: string): void {
		let text: Text = new Text();
		text.appendData(data + "\n");
		this.outputBuffer.append(text);
		this.scrollConsoleToBottom();
	}

	protected appendCommandToConsole(command: string): void {
		this.appendToConsole(this.getPrompt() + command);
	}

	protected appendStatusToConsole(status: string|number): void {
		this.appendToConsole("[" + status + "]\n");
	}

	protected clearConsole(): void {
		this.outputBuffer.html('');
	}

	protected scrollConsoleToPosition(position: number, ignoreScrollLock: boolean = false): void {
		if (!ignoreScrollLock && this.scrollLock) {
			return;
		}
		this.outputWrapper.animate({scrollTop: position}, 'fast', 'linear');
	}

	protected scrollConsoleToBottom(ignoreScrollLock: boolean = false): void {
		this.scrollConsoleToPosition(this.outputBuffer.height(), ignoreScrollLock);
	}

	protected scrollConsoleToTop(ignoreScrollLock: boolean = false): void {
		this.scrollConsoleToPosition(0, ignoreScrollLock);
	}

	protected scrollConsolePage(pages: number, ignoreScrollLock: boolean = false): void {
		this.scrollConsoleToPosition(this.outputWrapper.scrollTop() + (pages * this.outputWrapper.height()), ignoreScrollLock);
	}

	// Command input
	getCommand(): string {
		return this.commandInput.val();
	}

	setCommand(command: string): void {
		this.commandInput.val(command);
	}

	clearCommandInput(): void {
		this.setCommand('');
		this.historyPosition = -1;
		this.currentCommand = null;
	}

	isCommandInputEmpty(): boolean {
		return this.getCommand().length === 0;
	}

	disableCommandInput(): void {
		this.commandInput.prop('readonly', true);
	}

	enableCommandInput(): void {
		this.commandInput.prop('readonly', false);
	}

	isCommandInputDisabled(): boolean {
		return this.commandInput.prop('readonly') === true;
	}

	getCommandInputCursorPosition(): number {
		let node = <HTMLInputElement>this.commandInput[0];

		return node.selectionEnd > node.selectionStart ? node.selectionEnd : node.selectionStart;
	}

	moveCommandInputCursorToEnd(): void {
		this.moveCommandInputCursorToPosition(this.getCommand().length);
	}

	moveCommandInputCursorToPosition(position: number): void {
		let node = <HTMLInputElement>this.commandInput[0];
		node.setSelectionRange(position, position);
		this.commandInput.focus();
	}

// Current directory
	setCurrentDirectory(cwd: string): void {
		this.currentDirectoryInput.val(cwd);
	}

// Prompt
	setPrompt(prompt: string): void {
		this.prompt.text(prompt);
	}

	getPrompt(): string {
		return this.prompt.text();
	}

	updatePrompt(): void {
		// TODO
	}

// Shell
	addShell(_key: string, _type: string, _interpreterPath: string): void {
		// TODO
	}

	changeShell(shell: string): void {
		let shells = this.windowManager.getModuleData<ObjectDict>(this, 'shells', {});
		if (shells.hasOwnProperty(shell)) {
			this.shellClass.val(shells[shell]['shellClass']);
			this.interpreterPath.val(shells[shell]['interpreterPath']);
			this.updatePrompt();
			this.appendStatusToConsole(0);
		} else {
			this.appendToConsole(this.windowManager.getTranslator().get('Error: invalid shell specified [{0}]', Object.keys(shells).join(', ')));
			this.appendStatusToConsole(1);
		}
	}

// Simple command handling
	handleCommand(): void {
		if (this.isCommandInputEmpty() || this.isCommandInputDisabled()) {
			return;
		}
		this.disableCommandInput();
		let command = this.getCommand();
		this.appendCommandToConsole(command);
		this.addToHistory(command);
		if (this.handleSpecialCommands()) {
			this.clearCommandInput();
			this.enableCommandInput();
		} else {
			this.sendCommand();
		}
	}

	handleSpecialCommands(): boolean {
		let command = this.getCommand().trim();
		if (command === 'clear') {
			this.clearConsole();
		} else if (command === 'exit') {
			this.windowManager.removeWindow(this.windowManager.getCurrentWindowId());
		} else if (command.indexOf('set_shell ') === 0 && command.length > 10) {
			let shell = command.substr(10);
			this.changeShell(shell);
		} else if (command === 'enable_dynamic_output') {
			this.enableDynamicOutput();
		} else if (command === 'disable_dynamic_output') {
			this.disableDynamicOutput();
		} else {
			return false;
		}
		return true;
	}

	sendCommand(): void {
		let data = this.form.serialize();
		if (this.dynamicOutputEnabled) {
			data += "&dynamicOutput=1";
		}
		let _this = this;
		$.post(this.windowManager.getHandlerUrl(), data, function (response: ObjectDict) {
			_this.handleResponse(response);
		}, 'json');
	}

	handleResponse(response: ObjectDict): void {
		if (this.dynamicOutputEnabled && response.hasOwnProperty('reference')) {
			this.setDynamicOutputReference(response['reference']);
			return;
		}
		if (response.hasOwnProperty('result') && response['result'] !== null && response['result'].length > 0) {
			this.appendToConsole(response['result']);
		}
		if (response.hasOwnProperty('returnValue')) {
			this.appendStatusToConsole(response['returnValue']);
		}
		if (response.hasOwnProperty('directory')) {
			this.setCurrentDirectory(response['directory']);
		}
		if (response.hasOwnProperty('prompt')) {
			this.setPrompt(response['prompt']);
		}
		this.clearCommandInput();
		this.enableCommandInput();
	}

// Autocomplete
	handleAutocomplete(): void {
		if (this.isCommandInputEmpty()) {
			return;
		}
		let cursorPosition = this.getCommandInputCursorPosition();
		let data = this.form.serialize() + "&cursorPosition=" + cursorPosition + "&autocomplete=1";
		let _this = this;
		$.post(this.windowManager.getHandlerUrl(), data, function (response: ObjectDict) {
			_this.handleAutocompleteResponse(response)
		}, 'json');
	}

	handleAutocompleteResponse(response: ObjectDict): void {
		if (parseInt(response['returnValue']) !== 0) {
			return;
		}
		this.appendCommandToConsole(this.getCommand());
		if (response.hasOwnProperty('result') && response['result'] !== null && response['result'].length > 0) {
			this.appendToConsole(response['result'] + "\n");
		}
		if (response.hasOwnProperty('command') && response['command'] !== this.getCommand()) {
			this.setCommand(response['command']);
			if (response.hasOwnProperty('caretPosition')) {
				this.moveCommandInputCursorToPosition(response['caretPosition']);
			}
		}
	}

	// Keyboard events handler
	handleKeyboardEvent(helper: KeyboardEventHelper): void {
		let event = helper.getEvent();
		if (helper.isKeyPrintable() ||
			(helper.onlyCtrlModifierAllowed && (event.key === 'Backspace' || event.key === 'Delete')) ||
			(helper.onlyCtrlModifier && (event.key === 'v' || event.key === 'a')) ||
			(helper.noModifiers && (event.key === 'Home' || event.key === 'End'))
		) {
			// Printable characters and command input actions - focus on input
			this.commandInput.focus();
		} else if (helper.onlyCtrlModifier && event.key === 'c') {
			// Ctrl + C
			if (this.dynamicOutputReference !== null) {
				event.preventDefault();
				this.breakCommandExecution();
			} else if (window.getSelection().toString().length === 0 && !this.isCommandInputEmpty()) {
				event.preventDefault();
				this.appendCommandToConsole(this.getCommand() + '^C');
				this.clearCommandInput();
			}
		} else if (helper.noModifiers && event.key === 'Tab') {
			// Tab - autocomplete
			this.handleAutocomplete();
			event.preventDefault();
		} else if (helper.noModifiers && event.key === 'Enter') {
			// Enter - execute command
			this.handleCommand();
			event.preventDefault();
		} else if (helper.noModifiers && helper.isEscapeKey()) {
			// Esc - clear command line
			this.clearCommandInput();
			event.preventDefault();
		} else if (helper.noModifiers && helper.isArrowUpKey()) {
			// Up - move history cursor forward (older command)
			this.moveHistoryCursor(1, true);
			event.preventDefault();
		} else if (helper.onlyCtrlModifier && helper.isArrowUpKey()) {
			// Ctrl + Up - move history cursor to end (first command)
			this.moveHistoryCursor(this.history.length, false);
			event.preventDefault();
		} else if (helper.noModifiers && helper.isArrowDownKey()) {
			// Down - move history cursor backward (newer command)
			this.moveHistoryCursor(-1, true);
			event.preventDefault();
		} else if (helper.onlyCtrlModifier && helper.isArrowDownKey()) {
			// Ctrl + Down - move history cursor to current command (not yet executed)
			this.moveHistoryCursor(null, false);
			event.preventDefault();
		} else if (helper.onlyCtrlModifier && event.key === 'Home') {
			// Ctrl + Home - scroll console to top
			this.scrollConsoleToTop(true);
			event.preventDefault();
		} else if (helper.onlyCtrlModifier && event.key === 'End') {
			// Ctrl + End - scroll console to bottom
			this.scrollConsoleToBottom(true);
			event.preventDefault();
		} else if (helper.noModifiers && event.key === 'PageUp') {
			// PageUp - scroll console one page up
			this.scrollConsolePage(-1, true);
			event.preventDefault();
		} else if (helper.noModifiers && event.key === 'PageDown') {
			// PageDown - scroll console one page down
			this.scrollConsolePage(1, true);
			event.preventDefault();
		} else if (helper.noModifiers && helper.isScrollLockKey()) {
			// ScrollLock - toggle console scroll lock
			this.toggleScrollLock();
			event.preventDefault();
		}
	}

	toggleScrollLock(): void {
		this.scrollLock = !this.scrollLock;
		this.updateStatus();
	}

// Dynamic output
	enableDynamicOutput(): void {
		this.dynamicOutputEnabled = true;
		this.updateStatus();
	}

	disableDynamicOutput(): void {
		this.dynamicOutputEnabled = false;
		this.updateStatus();
	}

	setDynamicOutputReference(reference: string): void {
		this.disableCommandInput();
		this.dynamicOutputReference = reference;
		let _this = this;
		this.dynamicOutputInterval = setInterval(function () {
			_this.handleDynamicOutputRequest();
		}, 500);
	}

	clearDynamicOutputReference(): void {
		if (this.dynamicOutputInterval !== null) {
			clearInterval(this.dynamicOutputInterval);
			this.dynamicOutputInterval = null;
		}
		this.dynamicOutputReference = null;
		this.dynamicOutputRequestFinished = true;
		this.clearCommandInput();
		this.enableCommandInput();
	}

	handleDynamicOutputRequest(): void {
		if (!this.dynamicOutputRequestFinished || this.dynamicOutputReference === null) {
			return;
		}
		this.dynamicOutputRequestFinished = false;
		let data = {
			'module': this.getModuleKey(),
			'reference': this.dynamicOutputReference
		};
		let _this = this;
		$.post(this.windowManager.getHandlerUrl(), data, function (response: ObjectDict) {
			_this.handleDynamicOutputResponse(response);
		}, 'json');
	}

	handleDynamicOutputResponse(response: ObjectDict): void {
		if (response.hasOwnProperty('result')) {
			this.appendToConsole(response['result']);
		}
		if (response.hasOwnProperty('directory')) {
			this.setCurrentDirectory(response['directory']);
		}
		if (response.hasOwnProperty('returnValue')) {
			this.appendStatusToConsole(response['returnValue']);
			this.clearDynamicOutputReference();
		}
		this.dynamicOutputRequestFinished = true;
	}

	breakCommandExecution(): void {
		if (this.dynamicOutputReference === null) {
			return;
		}
		let data = {
			'module': this.getModuleName(),
			'reference': this.dynamicOutputReference,
			'kill': true
		};
		let _this = this;
		$.post(this.windowManager.getHandlerUrl(), data, function (response: ObjectDict) {
			_this.appendToConsole("Process " + response['pid'] + " killed\n");
			_this.clearDynamicOutputReference();
			_this.clearCommandInput();
			_this.enableCommandInput();
		}, 'json');
	}

}
