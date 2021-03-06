// FileBrowser tab
//noinspection JSUnusedGlobalSymbols
class FileBrowser extends Module {

	protected directory: string;
	protected container: JQuery;
	protected filterInput: JQuery;
	protected positions: {[key: string]: number};

	//noinspection JSUnusedGlobalSymbols
	constructor(windowManager: WindowManager, moduleKey: string) {
		super(windowManager, moduleKey);
		this.directory = this.windowManager.getModuleData(this, 'directory', '');
		this.positions = {};
	}

	build(): JQuery {
		this.container = $('<table/>');
		this.container.append(
			$('<thead/>').append(
				$('<tr/>').append(
					$('<th/>').addClass('permissions').text('Permissions')
				).append(
					$('<th/>').addClass('user').text('User')
				).append(
					$('<th/>').addClass('group').text('Group')
				).append(
					$('<th/>').addClass('modification_time').text('Modification time')
				).append(
					$('<th/>').addClass('size').text('Size')
				).append(
					$('<th/>').addClass('filename').text('Name')
				)
			)
		);
		this.filterInput = $('<input/>').prop('type', 'text').addClass('filterInput').addClass('hidden');
		let _this = this;
		this.container.on('click', 'tr.directory', function (event: JQueryEventObject) {
			event.preventDefault();
			_this.changeDirectory($(event.currentTarget).data('path'));
		});
		this.container.on('click', 'tr.file', function (event: JQueryEventObject) {
			event.preventDefault();
			_this.openFile($(event.currentTarget).data('path'));
		});
		this.filterInput.on('change keyup paste', function () {
			_this.updateFilter();
		});
		this.changeDirectory(this.directory);
		
		let directoryContextMenu = new ContextMenu(this.container, 'tr.directory');
		directoryContextMenu.addCallbackAction('Open', function(context: JQuery) {
			_this.changeDirectory(context.data('path'));
		});
		
		let fileContextMenu = new ContextMenu(this.container, 'tr.file');
		fileContextMenu.addCallbackAction('Download', function(context: JQuery) {
			let urlData = {
				'module': _this.getModuleKey(),
				'action': 'download',
				'file': context.data('path'),
			};
			window.open(_this.windowManager.getHandlerUrl() + '?' + $.param(urlData));
		});

		return $('<div/>').addClass('container').append(this.container).append(this.filterInput);
	}

	showHelp(): void {
	}

	onWindowFocus(): void {
	}

	handleKeyboardEvent(helper: KeyboardEventHelper): void {
		let event = helper.getEvent();
		if (helper.noModifiers && helper.isEscapeKey()) {
			// Esc - clear directory listing filter
			this.clearFilter();
		} else if (helper.noModifiers && (helper.isArrowUpKey() || helper.isArrowDownKey())) {
			// Up / Down - move selection between listing items
			event.preventDefault();
			let focused = this.container.find('tr.active');
			let rows = this.container.children('tbody').children();
			if (focused.length === 0) {
				rows.first().addClass('active');
			} else {
				let index = focused.index();
				focused.removeClass('active');
				if (helper.isArrowUpKey()) {
					index -= 1;
				} else {
					index += 1;
				}
				rows.eq(index).addClass('active');
			}
		} else if (helper.noModifiers && event.key === 'Enter') {
			// Enter - 'click' on active item
			$('tr.active').click();
		} else if (helper.noModifiers && event.key === '/' && this.isFilterInputHidden()) {
			// / - show filter popup
			event.preventDefault();
			this.showFilterInput();
		}
	}

	getWindowTitle(): string {
		return this.windowManager.getModuleData(this, 'name', '');
	}

	updateFilter(): void {
		let rows = this.container.children('tbody').children();
		let filterString = this.filterInput.val();
		if (filterString.length === 0) {
			rows.removeClass('hidden');
			return;
		}

		let filter = new RegExp(filterString, 'i');
		$.each(rows, function (_index, element) {
			element = $(element);
			if (!filter.test(element.data('name'))) {
				element.addClass('hidden');
			} else {
				element.removeClass('hidden');
			}
		});
	}

	isFilterInputHidden(): boolean {
		return this.filterInput.hasClass('hidden');
	}

	showFilterInput(): void {
		this.filterInput.removeClass('hidden');
		this.filterInput.focus();
	}

	hideFilterInput(): void {
		this.filterInput.addClass('hidden');
	}

	clearFilter(hideFilter: boolean = true): void {
		this.filterInput.val('');
		this.updateFilter();
		if (hideFilter) {
			this.hideFilterInput();
		}
	}

	changeDirectory(directory: string): void {
		this.positions[this.directory] = this.container.find('tr.active').index();
		this.clearFilter();
		let data = {
			'module': this.getModuleKey(),
			'directory': directory
		};
		let _this = this;
		$.post(this.windowManager.getHandlerUrl(), data, function (response: ObjectDict) {
			_this.handleResponse(response);
		}, 'json');
	}

	openFile(file: string): void {
		let module = new FilePreview(this.windowManager, this.getModuleKey());
		this.windowManager.createWindow(module);
		module.openFile(file);
	}

	handleResponse(response: ObjectDict): void {
		if (!response.hasOwnProperty('data')) {
			return;
		}
		let body = $('<tbody/>');
		$.each(response['data'], function (_index: number, value: ObjectDict) {
			body.append(
				$('<tr/>').append(
					$('<td/>').text(value['mode_string'])
				).append(
					$('<td/>').text(value['user']).prop('title', 'uid: ' + value['uid'])
				).append(
					$('<td/>').text(value['group']).prop('title', 'gid: ' + value['gid'])
				).append(
					$('<td/>').text(value['modification_time'])
				).append(
					$('<td/>').addClass('size').text(value['human_size'])
				).append(
					$('<td/>').text(value['filename'])
				).addClass(value['type']).data('path', value['path']).data('name', value['filename'])
			)
		});
		this.container.children('tbody').remove();
		this.container.append(body);
		if (response.hasOwnProperty('directory')) {
			this.directory = response['directory'];
			if (this.positions.hasOwnProperty(response['directory'])) {
				this.container.find('tbody').children().eq(this.positions[response['directory']]).addClass('active');
			}
		}
	}

}
