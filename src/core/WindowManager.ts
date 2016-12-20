// WindowManager
class WindowManager {
	protected windows: {[index: string]: WindowObject} = {};
	protected windowsOrder: string[] = [];
	protected currentWindowId: string|null = null;
	protected capsLockStatus: boolean = false;
	protected dragWindow: WindowObject|null = null;
	protected dragOffsetX: number = 0;
	protected dragOffsetY: number = 0;
	protected readonly status: JQuery;
	protected readonly taskBar: JQuery;
	protected readonly handlerUrl: string;
	protected readonly windowsContainer: JQuery;
	protected readonly translator: TranslationManager;
	protected readonly newWindowButton: JQuery;
	protected readonly newWindowMenu: JQuery;
	protected readonly modulesData: ObjectDict;

	constructor(selector: string, modulesData: ObjectDict) {
		let container = $(selector);
		this.handlerUrl = window.location.pathname;
		this.translator = new TranslationManager();
		this.modulesData = modulesData;

		this.status = $('<li/>').addClass('status');
		this.newWindowButton = $('<li/>').addClass('new-window-button').html('+');
		this.taskBar = $('<ol/>').addClass('tabs');
		this.taskBar.append(this.status);
		this.taskBar.append(this.newWindowButton);
		container.append(this.taskBar);

		this.windowsContainer = $('<div/>').addClass('windows-container');
		container.append(this.windowsContainer);

		let _this = this;
		this.newWindowMenu = $('<div/>').addClass('new-window-menu');
		$.each(this.getModulesData(), function (_key: string, moduleData: ObjectDict) {
			let link: JQuery = $('<a/>').data('jsClass', moduleData['jsClass']).data('moduleKey', moduleData['moduleKey']).text(moduleData['name']);
			_this.newWindowMenu.append(link);
		});
		container.append(this.newWindowMenu);


		this.taskBar.on('click', 'li.tab', function (this: Element, event: JQueryEventObject) {
			event.stopPropagation();
			_this.switchWindow($(this).data('windowId'));
		});
		this.taskBar.on('click', '.close-window', function (this: Element, event: JQueryEventObject) {
			event.stopPropagation();
			_this.removeWindow($(this).parent('li').data('windowId'));
		});
		this.newWindowButton.on('click', function (event: JQueryEventObject) {
			event.stopPropagation();
			_this.showNewWindowPopup();
		});
		this.windowsContainer.on('click', '.window', function (this: Element, event: JQueryEventObject) {
			event.stopPropagation();
			let windowId: string = $(this).data('windowId');
			_this.switchWindow(windowId);
		});
		this.windowsContainer.on('mousedown', '.menu', function (this: Element, event: JQueryEventObject) {
			let window = $(this).closest('.window');
			let windowId: string = window.data('windowId');
			_this.switchWindow(windowId);
			_this.dragWindow = _this.getWindowById(windowId);
			let windowOffset = window.offset();
			let targetOffset = $(event.target).offset();
			_this.dragOffsetX = targetOffset.left - windowOffset.left + event.offsetX;
			_this.dragOffsetY = targetOffset.top - windowOffset.top + event.offsetY;
		});
		this.windowsContainer.on('dblclick', '.menu', function (this: Element) {
			let windowId: string = $(this).closest('.window').data('windowId');
			_this.getWindowById(windowId).getContainer().toggleClass('maximized');
		});
		this.windowsContainer.on('mouseup', function () {
			_this.dragWindow = null;
		});
		this.windowsContainer.on('mousemove', function (event: JQueryEventObject) {
			if (_this.dragWindow === null) {
				return;
			}
			event.preventDefault();
			_this.dragWindow.getContainer().offset({left: event.pageX - _this.dragOffsetX, top: event.pageY - _this.dragOffsetY});
		});
		this.newWindowMenu.on('click', 'a', function (this: Element, event: JQueryEventObject) {
			event.preventDefault();
			event.stopPropagation();
			let element: JQuery = $(this);
			let className: string = element.data('jsClass');
			let moduleKey: string = element.data('moduleKey');
			let moduleInstance: Module = _this.getModuleInstance(className, moduleKey);
			_this.createWindow(moduleInstance);
			_this.hideNewWindowPopup();
		});
		$(window).on('keydown', function (event: JQueryKeyEventObject) {
			_this.handleKeyboardEvent(event);
		});
		$(window).on('beforeunload', function (): string|void {
			if (_this.hasWindows()) {
				return _this.translator.get('You still have windows open');
			}
		});
		this.showNewWindowPopup();
	}

	public getModulesData(): ObjectDict {
		return this.modulesData;
	}

	public getModuleData<T>(module: Module|string, key: string, defaultValue: T): T {
		let moduleKey: string;
		if (module instanceof Module) {
			moduleKey = module.getModuleKey();
		} else {
			moduleKey = module;
		}
		if (!this.modulesData.hasOwnProperty(moduleKey)) {
			return defaultValue;
		}
		if (key === null) {
			return this.modulesData[moduleKey];
		}
		if (!this.modulesData[moduleKey].hasOwnProperty(key)) {
			return defaultValue;
		}
		return this.modulesData[moduleKey][key];
	}

	public getHandlerUrl(): string {
		return this.handlerUrl;
	}

	protected getModuleInstance(className: string, moduleKey: string): Module {
		let instance = Object.create<Module>((<ObjectDict>window)[className].prototype);
		instance.constructor.apply(instance, [this, moduleKey]);

		return instance;
	}

	protected showNewWindowPopup(): void {
		this.newWindowMenu.show();
	}

	protected hideNewWindowPopup(): void {
		this.newWindowMenu.hide();
	}

	protected handleKeyboardEvent(event: JQueryKeyEventObject): void {
		let helper: KeyboardEventHelper = new KeyboardEventHelper(event);
		let capsLock = helper.getModifierState('CapsLock');
		if (this.capsLockStatus !== capsLock) {
			this.capsLockStatus = capsLock;
			if (this.hasWindows()) {
				this.getCurrentWindow().getModule().onWindowFocus();
			}
		}
		if (helper.onlyCtrlModifier && event.key === 'Insert') {
			// Ctrl + Insert - opens new window popup
			event.preventDefault();
			this.showNewWindowPopup();
		} else if (event.ctrlKey && event.altKey && (helper.isArrowLeftKey() || helper.isArrowRightKey())) {
			// Ctrl + Alt + Left arrow / Right arrow - switch between windows
			event.preventDefault();
			if (this.hasWindows() && this.hasActiveWindow()) {
				let position = $.inArray(this.getCurrentWindowId(), this.windowsOrder);
				position += helper.isArrowLeftKey() ? -1 : 1;
				if (position >= this.windowsOrder.length) {
					position = 0;
				} else if (position < 0) {
					position = this.windowsOrder.length - 1;
				}
				this.switchWindow(this.windowsOrder[position]);
			} else {
				let windowId = Object.keys(this.windows)[0];
				this.switchWindow(windowId);
			}
		} else if (event.ctrlKey && event.altKey && helper.isFunctionKey()) {
			// Ctrl + Alt + F1..F12 - switch between windows
			event.preventDefault();
			let windowId: string = this.taskBar.children('.tab').eq(event.keyCode - 112).data('windowId');
			this.switchWindow(windowId);
		} else if (this.hasWindows()) {
			if (this.hasActiveWindow()) {
				if (helper.onlyCtrlModifier && event.key === 'q') {
					// Ctrl + Q - close window
					event.preventDefault();
					this.removeWindow(this.getCurrentWindowId());
				} else if (helper.noModifiers && event.key === 'F1') {
					// F1 - show help
					event.preventDefault();
					this.getCurrentWindow().getModule().showHelp();
				} else if (helper.onlyShiftModifier && helper.isArrowLeftKey()) {
					// Shift + Left arrow - stick window to left side
					event.preventDefault();
					let halfWidth: number = this.windowsContainer.width() / 2;
					let height: number = this.windowsContainer.height();
					let container: JQuery = this.getCurrentWindow().getContainer();
					container.css('left', 0);
					container.css('top', 0);
					container.outerWidth(halfWidth);
					container.outerHeight(height);
				} else if (helper.onlyShiftModifier && helper.isArrowRightKey()) {
					// Shift + Right arrow - stick window to right side
					event.preventDefault();
					let halfWidth = this.windowsContainer.width() / 2;
					let height: number = this.windowsContainer.height();
					let container: JQuery = this.getCurrentWindow().getContainer();
					container.css('left', halfWidth);
					container.css('top', 0);
					container.outerWidth(halfWidth);
					container.outerHeight(height);
				} else if (helper.onlyShiftModifier && helper.isArrowUpKey()) {
					// Shift + Up arrow - maximize window
					event.preventDefault();
					this.getCurrentWindow().getContainer().addClass('maximized');
				} else if (helper.onlyShiftModifier && helper.isArrowDownKey()) {
					// Shift + Down arrow - revert maximize window
					event.preventDefault();
					let halfHeight: number = this.windowsContainer.height() / 2;
					let container = this.getCurrentWindow().getContainer();
					container.removeClass('maximized');
					container.outerHeight(halfHeight);
				} else {
					this.getCurrentWindow().getModule().handleKeyboardEvent(helper);
				}
			}
		}
	}

	public createWindow(moduleInstance: Module): void {
		let moduleKey = moduleInstance.getModuleKey();

		let windowHandle = $('<li/>').addClass('tab').addClass(moduleKey).text(moduleInstance.getWindowTitle());
		//.append($('<span/>').addClass('close-window').html('&#10005;'));
		this.taskBar.append(windowHandle);

		let window = new WindowObject(moduleInstance, windowHandle);
		let windowId = window.getWindowId();
		this.windows[windowId] = window;
		this.windowsOrder.push(windowId);
		this.windowsContainer.append(window.getContainer());

		windowHandle.data('windowId', windowId);
		moduleInstance.setWindowId(windowId);

		this.switchWindow(windowId);
	}

	public removeWindow(windowId: string|null): void {
		if (windowId === null) {
			return;
		}
		if (!this.hasWindows()) {
			this.setCurrentWindowId(null);
			return;
		}

		let window = this.getWindowById(windowId);
		window.getModule().onExit();
		window.getHandle().remove();
		window.getContainer().remove();
		delete(this.windows[windowId]);
		let position = $.inArray(windowId, this.windowsOrder);
		if (position !== -1) {
			this.windowsOrder.splice(position, 1);
		}
		if (position >= this.windowsOrder.length) {
			position = this.windowsOrder.length - 1;
		}

		this.switchWindow(this.windowsOrder[position])
	}

	getWindowById(windowId: string | null): WindowObject {
		if (windowId === null) {
			throw new Error;
		}
		if ($.inArray<string>(windowId, Object.keys(this.windows)) !== -1) {
			return this.windows[windowId];
		}

		throw new Error;
	}

	public hasWindows(): boolean {
		return this.getWindowsCount() > 0
	}

	public getCurrentWindow(): WindowObject {
		return this.getWindowById(this.getCurrentWindowId());
	}

	protected setCurrentWindowId(windowId: string | null): void {
		if (windowId === null || !this.hasWindows()) {
			this.clearStatus();
			this.currentWindowId = null;
			return;
		}

		try {
			this.getWindowById(windowId);
		} catch (Error) {
			this.currentWindowId = null;
			return;
		}

		this.currentWindowId = windowId;
	}

	public switchWindow(windowId: string | null): void {
		this.taskBar.children('.tab').removeClass('active');
		this.windowsContainer.children('.window').removeClass('active');
		this.setCurrentWindowId(windowId);
		if (windowId === null || !this.hasWindows()) {
			return;
		}

		let window = this.getWindowById(windowId);
		window.getHandle().addClass('active');
		window.getContainer().addClass('active');
		window.getModule().onWindowFocus();
	}

	getWindowsCount(): number {
		return Object.keys(this.windows).length;
	}

	clearStatus(): void {
		this.setStatus('');
	}

	setStatus(status: string): void {
		this.status.text(status);
	}

	updateTitle(windowId: string, title: string): void {
		let window = this.getWindowById(windowId);
		window.getHandle().text(title);
	}

	public getCapsLockStatus(): boolean {
		return this.capsLockStatus;
	}

	public getTranslator(): TranslationManager {
		return this.translator;
	}

	public getCurrentWindowId(): string|null {
		return this.currentWindowId;
	}

	protected hasActiveWindow(): boolean {
		return this.currentWindowId !== null;
	}
}
