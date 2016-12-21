class ContextMenu {
	protected container: JQuery;
	protected context: JQuery|null = null;

	constructor(anchor: string|JQuery, selector: string) {
		this.container = $('<ul class="context-menu">');
		
		let _this = this;
		$(anchor).on('contextmenu', selector, function (event: JQueryEventObject) {
			event.preventDefault();
			_this.context = $(event.currentTarget);
			_this.container.css({left: event.pageX, top: event.pageY});
			ContextMenu.hideAll();
			$('body').append(_this.container);
		});

		$(document).on('mousedown', function(event: JQueryEventObject) {
			if($(event.target).closest('ul.context-menu').length) {
				return;
			}
			ContextMenu.hideAll();
		});
	}
	
	public static hideAll() {
		$('body').children('ul.context-menu').detach();
	}

	public addCallbackAction(label: string, callback: (context: JQuery) => void): void {
		let _this = this;
		let item = $('<li/>').on('click', function () {
			if (_this.context !== null) {
				callback(_this.context);
				_this.context = null;
			}
			ContextMenu.hideAll();
		}).text(label);
		this.container.append(item);
	}

}
