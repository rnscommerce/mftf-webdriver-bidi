<?php

declare(strict_types=1);

namespace Rnscommerce\Mftf\BiDi\Tests\Integration\Project\Readiness;

use PHPUnit\Framework\Assert;
use Rnscommerce\Mftf\BiDi\Tests\Integration\Project\Support\ReadinessTester;

final class PageLoadCest
{
    public function amOnPageWaitsForTheRequestAndTheMaskWithoutSleeping(ReadinessTester $I): void
    {
        $started = microtime(true);
        $I->amOnPage('/mask.html?ms=800');
        $elapsed = microtime(true) - $started;
        codecept_debug(sprintf('On the page after %.3f s', $elapsed));

        Assert::assertSame('done', $I->grabTextFrom('#result'));
        $I->dontSeeElement('.loading-mask');
        Assert::assertGreaterThan(0.8, $elapsed);
        Assert::assertLessThan(1.6, $elapsed);
    }

    public function readinessAloneLeavesAMaskHiddenLaterVisible(ReadinessTester $I): void
    {
        $I->amOnPage('/mask.html?ms=300&linger=1000');
        $I->click('#load');

        $I->waitForPageReady(5);
        $I->seeElement('.loading-mask');

        $I->waitForLoadingMaskToDisappear();
        $I->dontSeeElement('.loading-mask');
    }

    public function waitForPageLoadWaitsForAMaskHiddenLater(ReadinessTester $I): void
    {
        $I->amOnPage('/mask.html?ms=300&linger=1000');
        $I->click('#load');

        $started = microtime(true);
        $I->waitForPageLoad();
        $elapsed = microtime(true) - $started;
        codecept_debug(sprintf('Loaded after %.3f s', $elapsed));

        Assert::assertSame('done', $I->grabTextFrom('#result'));
        $I->dontSeeElement('.loading-mask');
        Assert::assertGreaterThan(1.0, $elapsed);
        Assert::assertLessThan(2.0, $elapsed);
    }
}
