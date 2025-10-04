<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class ConfigResolver implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $param = 'buggregator_profiler.profiler_url';

        if ($container->hasParameter($param)) {
            $resolvedProfilerUrl = $container->resolveEnvPlaceholders($container->getParameter($param), true);
            // Ensure computed profiler_url is a valid url address
            if (false === filter_var($resolvedProfilerUrl, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException(sprintf('The computed "profiler_url" of %s is invalid url address', $resolvedProfilerUrl));
            }
            $container->setParameter(
                $param,
                $resolvedProfilerUrl
            );
        } else {
            throw new \InvalidArgumentException(sprintf('The "%s" parameter is not defined', $param));
        }
    }
}
