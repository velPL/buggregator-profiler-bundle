<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @var string[]
     */
    private const array ENVS = [
        'dev',
        'test',
        'config_disabled_env',
        'config_enabled_env',
        'config_profiler_url_resolved_env',
    ];

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';

        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
        if (in_array($this->getEnvironment(), self::ENVS, true)) {
            yield new BuggregatorProfilerBundle();
        }
    }
}
