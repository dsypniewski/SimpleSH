class WindowObject {
	protected static newWindowId: number = 1;
	protected readonly windowId: string;
	protected readonly moduleInstance: Module;
	protected readonly handle: JQuery;
	protected readonly windowContainer: JQuery;
	protected readonly menu: JQuery;
	protected readonly windowCanvas: JQuery;
	protected readonly menuActions: JQuery;

	constructor(moduleInstance: Module, handle: JQuery) {
		this.windowId = String(WindowObject.newWindowId++);
		this.moduleInstance = moduleInstance;
		this.handle = handle;
		
		this.windowContainer = $('<div class="window"/>').addClass(moduleInstance.getModuleKey()).data('windowId', this.getWindowId());
		
		this.menu = $('<div class="menu"/>').text(moduleInstance.getWindowTitle());
		this.menuActions = $('<ul class="menu-actions"/>');
		this.menu.append(this.menuActions);
		this.windowContainer.append(this.menu);

		this.windowCanvas = $('<div class="canvas"/>').append(moduleInstance.build());
		this.windowContainer.append(this.windowCanvas);

		this.addMenuAction('Close', function (_menuItem: JQuery, _event: JQueryEventObject, window: WindowObject) {
			window.close();
		});
		this.addMenuAction('Maximize', function (_menuItem: JQuery, _event: JQueryEventObject, window: WindowObject) {
			window.toggleMaximize();
		});
	}
	
	public addMenuAction(label: string|Text|JQuery, callback: (item: JQuery, event: JQueryEventObject, window: WindowObject) => void, append: boolean = false): void {
		if (typeof(label) === 'string') {
			let text = new Text;
			text.appendData(label);
			label = text;
		}
		let _this = this;
		let item = $('<li/>').append(label).on('mousedown', function () {
			// Prevents dragging
			event.stopPropagation();
		}).on('click', function (this: Element, event: JQueryEventObject) {
			callback($(this), event, _this);
		});
		if (append) {
			this.menuActions.append(item);
		} else {
			this.menuActions.prepend(item);
		}
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
	
	public toggleMaximize(): void {
		this.getContainer().toggleClass('maximized');
	}
	
	public close(): void {
		this.getModule().onExit();
		this.getHandle().remove();
		this.getContainer().remove();
		// TODO: remove WindowObject from WindowManager
	}

}
