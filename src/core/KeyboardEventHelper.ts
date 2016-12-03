class KeyboardEventHelper {
	protected readonly event: JQueryKeyEventObject;
	public readonly noModifiers: boolean;
	public readonly onlyCtrlModifier: boolean;
	public readonly onlyMetaModifier: boolean;

	constructor(event: JQueryKeyEventObject) {
		this.event = event;
		this.noModifiers = (!event.ctrlKey && !event.altKey && !event.shiftKey && !event.metaKey);
		this.onlyCtrlModifier = (!event.altKey && !event.shiftKey && !event.metaKey);
		this.onlyMetaModifier = (!event.ctrlKey && !event.altKey && !event.shiftKey);
	}

	public isKeyPrintable(): boolean {
		return (
			(((!this.event.ctrlKey && !this.event.altKey) || (this.event.ctrlKey && this.event.altKey)) && this.event.key.length === 1) ||
			this.event.key === 'Alphanumeric' ||
			this.event.key === 'Decimal' ||
			this.event.key === 'Multiply' ||
			this.event.key === 'Add' ||
			this.event.key === 'Divide' ||
			this.event.key === 'Subtract' ||
			this.event.key === 'Separator' ||
			this.event.key === 'Spacebar'
		);
	}
	
	public getModifierState(key: string): boolean {
		return (<KeyboardEvent>this.event.originalEvent).getModifierState(key);
	}
	
	public isFunctionKey(): boolean {
		return (this.event.keyCode >= 112 && this.event.keyCode <= 123);
	}
	
	public isEscapeKey(): boolean {
		return (this.event.key === 'Escape' || this.event.key === 'Esc');
	}
	
	public isScrollLockKey(): boolean {
		return (this.event.key === 'ScrollLock' || this.event.key === 'Scroll');
	}
	
	public isArrowUpKey(): boolean {
		return (this.event.key === 'ArrowUp' || this.event.key === 'Up');
	}
	
	public isArrowDownKey(): boolean {
		return (this.event.key === 'ArrowDown' || this.event.key === 'Down');
	}
	
	public isArrowLeftKey(): boolean {
		return (this.event.key === 'ArrowLeft' || this.event.key === 'Left');
	}
	
	public isArrowRightKey(): boolean {
		return (this.event.key === 'ArrowRight' || this.event.key === 'Right');
	}
	
	public getEvent(): JQueryKeyEventObject {
		return this.event;
	}
}
