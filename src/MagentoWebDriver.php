<?php

declare(strict_types=1);

namespace Rnscommerce\Mftf\BiDi;

use Magento\FunctionalTestingFramework\Module\MagentoWebDriver as MftfWebDriver;
use Rnscommerce\Codeception\WebDriverBiDi\WebDriverBiDi;

/**
 * MFTF's MagentoWebDriver whose page waits ask the WebDriverBiDi module enabled after it for readiness, and fall
 * back to MFTF's own when that module is not enabled or the browser session has no BiDi.
 */
class MagentoWebDriver extends MftfWebDriver
{
    /**
     * @param int|null $timeout seconds, pageload_timeout when not given
     */
    public function waitForPageLoad($timeout = null): void
    {
        if ($this->bidi() === null) {
            parent::waitForPageLoad($timeout);
            return;
        }
        $this->waitForAjaxLoad($timeout);
        $this->waitForLoadingMaskToDisappear($timeout);
    }

    /**
     * @param int|null $timeout seconds, pageload_timeout when not given
     */
    public function waitForAjaxLoad($timeout = null): void
    {
        $bidi = $this->bidi();
        if ($bidi === null) {
            parent::waitForAjaxLoad($timeout);
            return;
        }
        $bidi->waitForPageReady($this->pageLoadTimeout($timeout));
    }

    /**
     * @param int|null $timeout seconds, pageload_timeout when not given
     */
    public function waitForLoadingMaskToDisappear($timeout = null): void
    {
        $bidi = $this->bidi();
        if ($bidi === null) {
            parent::waitForLoadingMaskToDisappear($timeout);
            return;
        }
        $timeout = $this->pageLoadTimeout($timeout);
        foreach (array_filter($this->loadingMasksLocators, is_string(...)) as $locator) {
            $count = count($this->_findElements($locator));
            for ($i = 1; $i <= $count; $i++) {
                $bidi->waitForElementNotVisible("($locator)[$i]", $timeout);
            }
        }
    }

    /**
     * @return WebDriverBiDi|null the enabled WebDriverBiDi module, while the browser session has BiDi
     */
    private function bidi(): ?WebDriverBiDi
    {
        foreach ($this->getModules() as $module) {
            if ($module instanceof WebDriverBiDi) {
                return $module->_hasBiDi() ? $module : null;
            }
        }
        return null;
    }

    private function pageLoadTimeout(?int $timeout): int
    {
        $configured = $this->_getConfig('pageload_timeout');
        return $timeout ?? (is_numeric($configured) ? (int) $configured : 0);
    }
}
