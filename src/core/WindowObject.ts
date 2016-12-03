class WindowObject {
	protected static newWindowId: number = 1;
	protected readonly windowId: string;
	protected readonly moduleInstance: Module;
	protected readonly handle: JQuery;
	protected readonly windowContainer: JQuery;
	protected readonly menu: JQuery;
	protected readonly windowCanvas: JQuery;

	constructor(moduleInstance: Module, handle: JQuery) {
		this.windowId = String(WindowObject.newWindowId++);
		this.moduleInstance = moduleInstance;
		this.handle = handle;
		
		this.windowContainer = $('<div class="window"/>').addClass(moduleInstance.getModuleKey()).data('windowId', this.getWindowId());
		
		this.menu = $('<div class="menu"/>').text(moduleInstance.getWindowTitle());
		this.windowContainer.append(this.menu);

		this.windowCanvas = $('<div class="canvas"/>').append(moduleInstance.build());
		this.windowContainer.append(this.windowCanvas);
	}

	public getWindowId(): string {
		return this.windowId;
	}

	public getModule(): Module {
		return this.moduleInstance;
	}

	public getContainer(): JQuery {
		return this.windowContainer;
	}

	public getCanvas(): JQuery {
		return this.windowCanvas;
	}
	
	public getMenu(): JQuery {
		return this.menu;
	}

	public getHandle(): JQuery {
		return this.handle;
	}

}
