<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\Profiler;

use SpiralPackages\Profiler\DriverFactory;
use SpiralPackages\Profiler\Profiler;
use SpiralPackages\Profiler\Storage\WebStorage;
use Symfony\Component\HttpClient\NativeHttpClient;

class ProfilerFactory implements ProfilerFactoryInterface
{
    private const string PROFILER_STORE_API_ENDPOINT = 'api/profiler/store';

    public function create(string $url, string $appName): Profiler
    {
        return new Profiler(
            storage: new WebStorage(
                new NativeHttpClient(),
                sprintf('%s/%s', $url, self::PROFILER_STORE_API_ENDPOINT),
            ),
            driver: DriverFactory::detect(),
            appName: $appName,
            tags: []
        );
    }
}
