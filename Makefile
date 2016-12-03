SRC_DIR = src
BIN_DIR = .
BUILD_DIR = build
LIBRARIES_DIR = lib
MODULES_BUILD_DIR = $(BUILD_DIR)/modules
OUTPUT_FILE = shell.php

cat_php = echo '<?php' > $(2); $(foreach f, $(1), tail -n +2 $(f) >> $(2);)
cat_php_closed = $(call cat_php, $(1), $(2)) echo >> $(2); echo '?>' >> $(2)

build: $(BIN_DIR)/$(OUTPUT_FILE)

clean:
	@echo Removing $(BIN_DIR)/$(OUTPUT_FILE)
	@rm -f $(BIN_DIR)/$(OUTPUT_FILE)
	@echo Removing $(BUILD_DIR)/\*
	@rm -rf $(BUILD_DIR)/*
	
clean-js:
	@echo Removing $(BUILD_DIR)/build.js
	@rm -f $(BUILD_DIR)/build.js
	
clean-css:
	@echo Removing $(BUILD_DIR)/build.css
	@rm -f $(BUILD_DIR)/build.css

clean-build: clean build

build-js: clean-js $(BUILD_DIR)/build.js

build-css: clean-css $(BUILD_DIR)/build.css

$(BIN_DIR)/$(OUTPUT_FILE): $(BUILD_DIR)/build.php $(BUILD_DIR)/build.css $(BUILD_DIR)/build.js
	@[ -d $(BIN_DIR) ] || mkdir -p $(BIN_DIR)
	@echo Building $@
	@./template.sh $(BUILD_DIR)/build.php $(BUILD_DIR)/build.js $(BUILD_DIR)/build.css > $@

PHP_MODULES_FILES = $(patsubst $(SRC_DIR)/%,$(MODULES_BUILD_DIR)/%.php,$(wildcard $(SRC_DIR)/*))
$(BUILD_DIR)/build.php: $(MODULES_BUILD_DIR)/core.php $(PHP_MODULES_FILES)
	@[ -d $(BUILD_DIR) ] || mkdir -p $(BUILD_DIR)
	@echo Building $@
	@$(call cat_php_closed, $^, $@)

# This rule can be overwritten by modules Makefile to maintain order when joining files
$(MODULES_BUILD_DIR)/%.php: $(SRC_DIR)/%/*.php
	@[ -d $(MODULES_BUILD_DIR) ] || mkdir -p $(MODULES_BUILD_DIR)
	@echo Building $@
	@$(call cat_php, $^, $@)

$(BUILD_DIR)/build.css: $(wildcard $(SRC_DIR)/core/*.scss) $(wildcard $(SRC_DIR)/*/*.scss)
	@[ -d $(BUILD_DIR) ] || mkdir -p $(BUILD_DIR)
	@echo Building $@
	@cat $^ | sass --scss -s > $@

$(BUILD_DIR)/modules.js: $(LIBRARIES_DIR)/jquery.d.ts $(wildcard $(SRC_DIR)/*/*.ts)
	@[ -d $(BUILD_DIR) ] || mkdir -p $(BUILD_DIR)
	@echo Building $@
	@tsc --project tsconfig.json

$(BUILD_DIR)/build.js: $(LIBRARIES_DIR)/jquery-3.1.1.min.js $(BUILD_DIR)/modules.js
	@echo Building $@
	@cat $^ > $@

include $(SRC_DIR)/*/Makefile

.PHONY: build clean clean-js clean-build build-js
 
