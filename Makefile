PWD = $(shell pwd)
APP ?= $(shell basename $(PWD))
## -- Test --

## Spin un a test environment base on docker-compose.test.yml
test-environment:
	@echo ">> running docker-compose"
	@docker-compose -f docker-compose.test.yml -p investment-manager-test up -d

.PHONY: test-environment

.DEFAULT_GOAL := help
HELP_SECTION_WIDTH="      "
HELP_DESC_WIDTH="                       "
help:
	@printf "${APP}\n";
	@awk '{ \
			if ($$0 ~ /^.PHONY: [a-zA-Z\-\_0-9]+$$/) { \
				helpCommand = substr($$0, index($$0, ":") + 2); \
				if (helpMessage) { \
					printf "  \033[32m%-20s\033[0m %s\n", \
						helpCommand, helpMessage; \
					helpMessage = ""; \
				} \
			} else if ($$0 ~ /^[a-zA-Z\-\_0-9.]+:/) { \
				helpCommand = substr($$0, 0, index($$0, ":")); \
				if (helpMessage) { \
					printf "  \033[32m%-20s\033[0m %s\n", \
						helpCommand, helpMessage; \
					helpMessage = ""; \
				} \
			} else if ($$0 ~ /^##/) { \
				if (helpMessage) { \
					helpMessage = helpMessage"\n"${HELP_DESC_WIDTH}substr($$0, 3); \
				} else { \
					helpMessage = substr($$0, 3); \
				} \
			} else { \
				if (helpMessage) { \
					print "\n"${HELP_SECTION_WIDTH}helpMessage"\n" \
				} \
				helpMessage = ""; \
			} \
		}' \
		$(MAKEFILE_LIST)
	@printf "\nUsage\n";
	@printf "  make <flags> [options]\n";
