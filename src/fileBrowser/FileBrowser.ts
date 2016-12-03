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

	build() {
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
		this.container.on('click', 'tr.directory', function (this: Element, event: JQueryEventObject) {
			event.preventDefault();
			_this.changeDirectory($(this).data('path'));
		});
		this.filterInput.on('change keyup paste', function () {
			_this.updateFilter();
		});
		this.changeDirectory(this.directory);

		return $('<div/>').addClass('container').append(this.container).append(this.filterInput);
	}

	showHelp(): void {
	}

	onWindowFocus(): void {
	}

	handleKeyboardEvent(helper: KeyboardEventHelper): void {
		let event = helper.getEvent();
		if (event.keyCode == 27) {
			this.clearFilter();
		} else if (event.keyCode === 38 || event.keyCode === 40) { // Up and Down arrow
			event.preventDefault();
			let focused = this.container.find('tr.active');
			let rows = this.container.children('tbody').children();
			if (focused.length === 0) {
				rows.first().addClass('active');
			} else {
				let index = focused.index();
				focused.removeClass('active');
				if (event.keyCode === 38) {
					index -= 1;
				} else {
					index += 1;
				}
				rows.eq(index).addClass('active');
			}
		} else if (event.keyCode === 13) {
			$('tr.active').click();
		} else if (event.keyCode === 191 && this.isFilterInputHidden()) {
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
		$.post(this.windowManager.getHandlerUrl(), data, function (response: {'data': Object, 'directory': string}) {
			_this.handleResponse(response);
		}, 'json');
	}

	handleResponse(response: {'data': Object, 'directory': string}): void {
		if (!response.hasOwnProperty('data')) {
			return;
		}
		let body = $('<tbody/>');
		$.each(response['data'], function (_index: number, value: {[key: string]: string}) {
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
