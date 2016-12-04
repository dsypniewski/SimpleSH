class TranslationManager {

	protected translations: TranslationSet = {};
	
	add(translations: TranslationSet): void {
		let _this = this;
		$.each(translations, function (key: string, value: string) {
			_this.translations[key] = value;
		});
	}

	get(message: string, ...argv: string[]): string {
		if (this.translations.hasOwnProperty(message)) {
			message = this.translations[message];
		}

		if (argv.length === 1) {
			return message;
		}

		return message.replace(/{(\d+)}/g, function (match: string, number: number): string {
			return (number >= 0 && argv.length > number) ? argv[number] : match;
		});
	}

}
