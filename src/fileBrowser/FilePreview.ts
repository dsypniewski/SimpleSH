class FilePreview extends Module {

	protected filePath: string;
	protected offset: string;
	protected container: JQuery;

	constructor(windowManager: WindowManager, moduleKey: string) {
		super(windowManager, moduleKey);
		this.container = $('<div/>');
	}
	
	build(): JQuery {
		return this.container;
	}

	showHelp(): void {
	}

	onWindowFocus(): void {
	}

	handleKeyboardEvent(_helper: KeyboardEventHelper): void {
	}

	getWindowTitle(): string {
		return this.windowManager.getModuleData(this, 'name', '');
	}

	openFile(filePath: string): void {
		this.filePath = filePath;
		let data = {
			'module': this.getModuleKey(),
			'action': 'view-file',
			'file': this.filePath,
		};
		let src = this.windowManager.getHandlerUrl() + '?' + $.param(data);
		let iframe = $('<iframe/>').prop('src', src).addClass('view-file').on('load', function () {
			iframe.contents().find('body').css('color', $(document.body).css('color'));
		});
		this.container.append(iframe);
	}

}
