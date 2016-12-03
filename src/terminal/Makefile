MODULE_NAME = terminal
MODULE_SRC = $(SRC_DIR)/$(MODULE_NAME)

PHP_FILES = \
	$(MODULE_SRC)/Terminal_Module.php \
	$(MODULE_SRC)/Terminal_ShellInterface.php \
	$(MODULE_SRC)/Terminal_AutocompleteInterface.php \
	$(MODULE_SRC)/Terminal_Shell.php \
	$(MODULE_SRC)/Terminal_DynamicOutputShell.php \
	$(MODULE_SRC)/Terminal_Sh.php \
	$(wildcard $(MODULE_SRC)/*.php)
$(MODULES_BUILD_DIR)/$(MODULE_NAME).php: $(PHP_FILES)
	@[ -d $(MODULES_BUILD_DIR) ] || mkdir -p $(MODULES_BUILD_DIR)
	@echo Building $@
	@$(call cat_php, $^, $@)
