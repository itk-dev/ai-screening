# Testing

[Playwright](https://playwright.dev/) is set up and used to run tests.

Update trusted host patterns:

``` php
// settings.local.php
$settings['trusted_host_patterns'][] = '^nginx$';
```

``` shell name=playwright-run
docker compose run --rm node npm install
docker compose run --rm playwright npx playwright install
docker compose run --rm playwright npx playwright test
open playwright-report/index.html
```

``` shell name=playwright-run-ui
# @see https://gist.github.com/cschiewek/246a244ba23da8b9f0e7b11a68bf3285#file-x11_docker_mac-md
# Install XQuartz: brew install xquartz
xhost + 127.0.0.1
docker compose run --rm --env DISPLAY=host.docker.internal:0 playwright npx playwright test --ui
```

See
[itk-dev/hoeringsportal/blob/develop/documentation/Testing.md](https://github.com/itk-dev/hoeringsportal/blob/develop/documentation/Testing.md)
for details.

A couple of tasks can be used to run the tests:

* `task test-playwright-test`: Run playwright tests
* `task test-playwright-test-ui`: Run playwright tests UI

## Setting up Playwright

This section is used for future reference on how to set up Playwright in a project.

``` shell name=playwright-set-up
# https://playwright.dev/docs/intro#installing-playwright
docker compose run --rm node npm init playwright@latest
```

``` console
Need to install the following packages:
create-playwright@1.17.134
Ok to proceed? (y)


> app@1.0.0 npx
> create-playwright

Getting started with writing end-to-end tests with Playwright:
Initializing project in '.'
✔ Do you want to use TypeScript or JavaScript? · TypeScript
✔ Where to put your end-to-end tests? · tests/playwright
✔ Add a GitHub Actions workflow? (y/N) · true
✔ Install Playwright browsers (can be done manually via 'npx playwright install')? (Y/n) · true
✔ Install Playwright operating system dependencies (requires sudo / root - can be done manually via 'sudo npx playwright install-deps')? (y/N) · false
Installing Playwright Test (npm install --save-dev @playwright/test)…
```
