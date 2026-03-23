<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\EventListener;

use SpiralPackages\Profiler\Profiler;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Velpl\BuggregatorProfilerBundle\Profiler\ProfilerFactoryInterface;

class BuggregatorProfilerSubscriber implements EventSubscriberInterface
{
    private const int EVENT_HIGHEST_PRIORITY = 9999;
    private const int EVENT_LOWEST_PRIORITY = -9999;
    private ?Profiler $profiler = null;

    public function __construct(
        private readonly string $profilerUrl,
        private readonly string $applicationName,
        private readonly ProfilerFactoryInterface $profilerFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', self::EVENT_HIGHEST_PRIORITY],
            ConsoleEvents::COMMAND => ['onCommandStart', self::EVENT_HIGHEST_PRIORITY],
            KernelEvents::TERMINATE => ['onKernelTerminate', self::EVENT_LOWEST_PRIORITY],
            ConsoleEvents::TERMINATE => ['onCommandTermination', self::EVENT_LOWEST_PRIORITY],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $this->profiler = $this->profilerFactory->create($this->profilerUrl, $this->applicationName);
        $this->profiler->start();
    }

    public function onCommandStart(ConsoleCommandEvent $commandEvent): void
    {
        $this->profiler = $this->profilerFactory->create($this->profilerUrl, $this->applicationName);
        $this->profiler->start();
    }

    public function onKernelTerminate(): void
    {
        $this->profiler?->end();
    }

    public function onCommandTermination(): void
    {
        $this->onKernelTerminate();
    }
}
