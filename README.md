# mftf-webdriver-bidi

MFTF's `MagentoWebDriver` with its page waits answered by WebDriver BiDi, through
[rnscommerce/codeception-module-webdriver-bidi](https://github.com/rnscommerce/codeception-module-webdriver-bidi).

MFTF waits for the page from inside its own actions, such as `amOnPage`, by
polling jQuery and the document and then sleeping for a second. Those calls never
reach a module listed beside it, so this class takes `MagentoWebDriver`'s place
and makes them ask the BiDi module instead:

- `waitForAjaxLoad` waits for the page to be ready: no request in flight, the DOM
  quiet and animations ended. It does not sleep.
- `waitForLoadingMaskToDisappear` waits for each visible loading mask to be
  hidden, checking again when the page changes rather than every 250 ms.
- `waitForPageLoad` does both.

Each takes `pageload_timeout` when not given a timeout.

## Enable it

Name it in place of `MagentoWebDriver` in the suite, with its config under the
new name, and list the BiDi module after it with MFTF's own actions that act on
the page:

```yaml
modules:
    enabled:
        - \Rnscommerce\Mftf\BiDi\MagentoWebDriver
        - \Rnscommerce\Codeception\WebDriverBiDi\WebDriverBiDi:
              wait_before_actions:
                  - fillSecretField
                  - conditionalClick
                  - rapidClick
                  - searchAndMultiSelectOption
                  - selectMultipleOptions
                  - closeAdminNotification
        - \Magento\FunctionalTestingFramework\Module\MagentoSequence
        # …the rest of MFTF's modules
    config:
        \Rnscommerce\Mftf\BiDi\MagentoWebDriver:
            url: "%MAGENTO_BASE_URL%"
            # …as MagentoWebDriver's
```

## Falling back

Without the BiDi module enabled, or in a browser session without BiDi, each of
the three waits is `MagentoWebDriver`'s own.

## Development

```sh
composer install
composer check
CHROMEDRIVER=… CHROME_BINARY=… composer test:integration
```

`composer test:integration` runs the Codeception project in `tests/Integration/Project`
against headless Chrome, and is skipped when `CHROMEDRIVER` does not name a
chromedriver. `CHROME_BINARY` is optional.
