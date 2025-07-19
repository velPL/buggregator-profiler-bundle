<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\Profiler;

use SpiralPackages\Profiler\Profiler;

interface ProfilerFactoryInterface
{
    public function create(string $url, string $appName): Profiler;
}
