<?php

declare(strict_types=1);

namespace Rnscommerce\Mftf\BiDi\Tests\Integration\Project\Fallback;

use PHPUnit\Framework\Assert;
use Rnscommerce\Mftf\BiDi\Tests\Integration\Project\Support\FallbackTester;

final class PageLoadCest
{
    public function amOnPageWaitsAsMftfDoes(FallbackTester $I): void
    {
        $started = microtime(true);
        $I->amOnPage('/mask.html?ms=800');
        $elapsed = microtime(true) - $started;
        codecept_debug(sprintf('On the page after %.3f s', $elapsed));

        Assert::assertSame('done', $I->grabTextFrom('#result'));
        $I->dontSeeElement('.loading-mask');
        Assert::assertGreaterThan(1.8, $elapsed);
    }
}
