<?php

declare(strict_types=1);

namespace Rnscommerce\Mftf\BiDi\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Runs each suite of the Codeception project in Project/ against headless Chrome, with a driver and a fixture server
 * started for the class.
 */
final class ChromeTest extends TestCase
{
    private const MODULE = '\Rnscommerce\Mftf\BiDi\MagentoWebDriver';

    private static ?Process $driver = null;

    private static ?Process $server = null;

    public static function setUpBeforeClass(): void
    {
        $chromedriver = getenv('CHROMEDRIVER');
        if (!is_string($chromedriver) || !is_executable($chromedriver)) {
            self::markTestSkipped('CHROMEDRIVER does not name a chromedriver');
        }
        self::$server = Process::start(
            static fn (int $port): array => [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', __DIR__ . '/fixtures'],
            '/mask.html',
            // A slow fixture request must not hold up the others.
            ['PHP_CLI_SERVER_WORKERS' => '8'],
        );
        self::$driver = Process::start(static fn (int $port): array => [$chromedriver, "--port=$port"], '/status');
    }

    public static function tearDownAfterClass(): void
    {
        self::$driver?->stop();
        self::$server?->stop();
        self::$driver = self::$server = null;
    }

    public function testWaitingWithReadiness(): void
    {
        $this->runSuite('Readiness');
    }

    public function testFallingBackWithoutTheBiDiModule(): void
    {
        $this->runSuite('Fallback');
    }

    private function runSuite(string $suite): void
    {
        $server = self::$server ?? throw new \LogicException('No server');
        $driver = self::$driver ?? throw new \LogicException('No driver');
        $config = json_encode([
            'url' => "http://127.0.0.1:{$server->port}/",
            'host' => '127.0.0.1',
            'port' => $driver->port,
            'capabilities' => ['goog:chromeOptions' => self::chromeOptions()],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $output = tmpfile();
        self::assertIsResource($output);
        $process = proc_open(
            [
                PHP_BINARY,
                dirname(__DIR__, 2) . '/vendor/bin/codecept',
                'run',
                $suite,
                '--config',
                __DIR__ . '/Project',
                '--debug',
                '--no-colors',
                '--override',
                'modules: config: ' . self::MODULE . ": $config",
            ],
            [0 => ['file', '/dev/null', 'r'], 1 => $output, 2 => $output],
            $pipes,
        );
        self::assertIsResource($process);
        $exited = proc_close($process);
        rewind($output);

        self::assertSame(0, $exited, (string) stream_get_contents($output));
    }

    /**
     * @return array<string, mixed>
     */
    private static function chromeOptions(): array
    {
        $options = ['args' => ['--headless=new', '--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']];
        $binary = getenv('CHROME_BINARY');
        if (is_string($binary) && $binary !== '') {
            $options['binary'] = $binary;
        }
        return $options;
    }
}
