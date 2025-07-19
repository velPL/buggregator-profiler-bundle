<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Velpl\BuggregatorProfilerBundle\BuggregatorProfilerBundle;

class BuggregatorProfilerBundleTest extends KernelTestCase
{
    public static function configurationsProvider(): array
    {
        return [
            'enabled, custom profiler url with ending slash, custom application name' => [
                'enabled' => true,
                'profilerUrl' => 'http://localhost:1234/',
                'applicationName' => 'Test Application',
                'expectedEnabled' => true,
                'expectedProfilerUrl' => 'http://localhost:1234/api/profiler/store',
                'expectedApplicationName' => 'Test Application',
            ],
            'disabled, custom profiler url without ending slash, custom application name' => [
                'enabled' => false,
                'profilerUrl' => 'http://localhost:1234',
                'applicationName' => 'Test Application',
                'expectedEnabled' => false,
                'expectedProfilerUrl' => 'http://localhost:1234/api/profiler/store',
                'expectedApplicationName' => 'Test Application',
            ],
        ];
    }

    #[DataProvider('configurationsProvider')]
    #[Test]
    public function validConfigurationPassedSetsProperComputedConfigurationParameters(
        ?bool $enabled,
        ?string $profilerUrl,
        ?string $applicationName,
        bool $expectedEnabled,
        string $expectedProfilerUrl,
        string $expectedApplicationName
    ): void
    {
        $bundle = new BuggregatorProfilerBundle();
        $config = [
            'enabled' => $enabled,
            'profiler_url' => $profilerUrl,
            'application_name' => $applicationName,
        ];
        $loader = $this->createMock(PhpFileLoader::class);
        $builder = new ContainerBuilder();
        $instanceOf = [];
        $container = new ContainerConfigurator($builder, $loader, $instanceOf, 'path', 'file');

        $bundle->loadExtension($config, $container, $builder);

        $this->assertTrue($builder->hasParameter('buggregator_profiler.profiler_url'));
        $this->assertSame(
            $expectedProfilerUrl,
            $builder->getParameter('buggregator_profiler.profiler_url')
        );
        $this->assertTrue($builder->hasParameter('buggregator_profiler.enabled'));
        $this->assertSame(
            $expectedEnabled,
            $builder->getParameter('buggregator_profiler.enabled')
        );
        $this->assertTrue($builder->hasParameter('buggregator_profiler.application_name'));
        $this->assertSame(
            $expectedApplicationName,
            $builder->getParameter('buggregator_profiler.application_name')
        );
    }

    #[Test]
    public function invalidProfilerUrlConfigurationThrowsException(): void
    {
        $bundle = new BuggregatorProfilerBundle();
        $config = [
            'enabled' => false,
            'profiler_url' => 'http',
            'application_name' => 'Test Application',
        ];
        $loader = $this->createMock(PhpFileLoader::class);
        $builder = new ContainerBuilder();
        $instanceOf = [];
        $container = new ContainerConfigurator($builder, $loader, $instanceOf, 'path', 'file');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The computed "profiler_url" of');

        $bundle->loadExtension($config, $container, $builder);

    }
    #[Test]
    public function configurationWithDisabledProfilerDoesntAddProfilerSubscriberToServiceContainer(): void
    {
        self::bootKernel(['environment' => 'config_disabled_env']);
        $container = self::getContainer();

        $this->assertSame('http://127.0.0.1/api/profiler/store', $container->getParameter('buggregator_profiler.profiler_url'));
        $this->assertSame('TestApp', $container->getParameter('buggregator_profiler.application_name'));
        $this->assertSame(false, $container->getParameter('buggregator_profiler.enabled'));
        $this->assertFalse($container->has('buggregator_profiler.subscriber'));
    }

    #[Test]
    public function configurationWithEnabledProfilerAddSProfilerSubscriberToServiceContainer(): void
    {
        self::bootKernel(['environment' => 'config_enabled_env']);
        $container = self::getContainer();

        $this->assertSame('http://127.0.0.1/api/profiler/store', $container->getParameter('buggregator_profiler.profiler_url'));
        $this->assertSame('TestApp', $container->getParameter('buggregator_profiler.application_name'));
        $this->assertSame(true, $container->getParameter('buggregator_profiler.enabled'));
        $this->assertTrue($container->has('buggregator_profiler.subscriber'));
    }
}
