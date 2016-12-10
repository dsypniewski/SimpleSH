abstract class Module {
	protected windowId: string;
	protected windowManager: WindowManager;
	protected moduleKey: string;

	constructor(windowManager: WindowManager, moduleKey: string) {
		this.windowManager = windowManager;
		this.moduleKey = moduleKey;
	}

	public getModuleKey(): string {
		return this.moduleKey;
	}

	public getModuleName(): string {
		return this.windowManager.getModuleData(this, 'name', '');
	}

	public setWindowId(windowId: string) {
		this.windowId = windowId;
	}

	public getWindowId(): string {
		return this.windowId;
	}

	public onExit(): void {
	}

	abstract build(): JQuery;

	abstract onWindowFocus(): void;

	abstract handleKeyboardEvent(helper: KeyboardEventHelper): void;

	abstract getWindowTitle(): string;

	abstract showHelp(): void;

}
