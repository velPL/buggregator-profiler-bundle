<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Velpl\BuggregatorProfilerBundle\CompilerPass\ConfigResolver;
use Velpl\BuggregatorProfilerBundle\EventListener\BuggregatorProfilerSubscriber;
use Velpl\BuggregatorProfilerBundle\Profiler\ProfilerFactory;
use Velpl\BuggregatorProfilerBundle\Profiler\ProfilerFactoryInterface;

final class BuggregatorProfilerBundle extends AbstractBundle
{
    public const string PROFILER_SUBSCRIBER_SERVICE_ID = 'buggregator_profiler.subscriber';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigResolver());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // @codeCoverageIgnoreStart
        /** @var ArrayNodeDefinition $node */
        $node = $definition->rootNode();
        $node
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->scalarNode('application_name')->defaultValue('Symfony App')->end()
                ->scalarNode('profiler_url')->isRequired()->defaultValue('http://buggregator:8000')->end()
            ->end();
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param array<string, string|bool> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (str_ends_with($config['profiler_url'], '/')) {
            $config['profiler_url'] = substr($config['profiler_url'], 0, -1);
        }
        $profilerFullUrl = implode('/', [
            $config['profiler_url'],
            'api/profiler/store']
        );
        $isEnabled = boolval($config['enabled']);

        $container->parameters()
            ->set('buggregator_profiler.enabled', $isEnabled)
            ->set('buggregator_profiler.profiler_url', $profilerFullUrl)
            ->set('buggregator_profiler.application_name', $config['application_name']);

        // Register subscriber only if enabled
        if (false === $isEnabled) {
            return;
        }

        $builder->setDefinition(ProfilerFactoryInterface::class, new Definition(ProfilerFactory::class));

        $definition = new Definition(BuggregatorProfilerSubscriber::class);
        $definition->setArgument(0, '%buggregator_profiler.profiler_url%');
        $definition->setArgument(1, '%buggregator_profiler.application_name%');
        $definition->setArgument(2, new Reference(ProfilerFactoryInterface::class));  // Passing the interface here
        $definition->addTag('kernel.event_subscriber');

        $builder->setDefinition(self::PROFILER_SUBSCRIBER_SERVICE_ID, $definition);
    }
}
