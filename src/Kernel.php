<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';

        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
        if (in_array($this->getEnvironment(), ['dev', 'test', 'config_disabled_env', 'config_enabled_env'], true)) {
            yield new BuggregatorProfilerBundle();
        }
    }
}
