<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\Tests;

use PHPUnit\Framework\Attributes\Test;
use SpiralPackages\Profiler\Profiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Stopwatch\Stopwatch;
use Velpl\BuggregatorProfilerBundle\Profiler\ProfilerFactoryInterface;

class BuggregatorProfilerSubscriberTest extends KernelTestCase
{
    #[Test]
    public function enabledProfilerTriggersProfilerKernelEvents(): void
    {
        self::bootKernel(['environment' => 'config_enabled_env']);
        $container = self::getContainer();

        $profilerMock = $this->createMock(Profiler::class);
        $profilerMock->expects($this->once())
            ->method('start')
        ;
        $profilerMock->expects($this->once())
            ->method('end')
        ;
        $factoryMock = $this->createMock(ProfilerFactoryInterface::class);

        $factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profilerMock);
        $container->set(ProfilerFactoryInterface::class, $factoryMock);
        $container->set(Profiler::class, $profilerMock);

        $eventDispatcher = new TraceableEventDispatcher(
            new EventDispatcher(),
            new Stopwatch()
        );

        $event = $this->createMock(RequestEvent::class);
        $event->expects($this->once())
            ->method('isMainRequest')
            ->willReturn(true);

        $eventDispatcher->addSubscriber($container->get('buggregator_profiler.subscriber'));
        $eventDispatcher->dispatch($event, KernelEvents::REQUEST);
        $eventDispatcher->dispatch($event, KernelEvents::TERMINATE);

        $calledEvents = array_column($eventDispatcher->getCalledListeners(), 'event');
        $this->assertContains(
            KernelEvents::REQUEST,
            $calledEvents
        );
        $this->assertContains(
            KernelEvents::TERMINATE,
            $calledEvents
        );
    }

    #[Test]
    public function enabledProfilerTriggersProfilerConsoleEvents(): void
    {
        self::bootKernel(['environment' => 'config_enabled_env']);
        $container = self::getContainer();

        $profilerMock = $this->createMock(Profiler::class);
        $profilerMock->expects($this->once())
            ->method('start')
        ;
        $profilerMock->expects($this->once())
            ->method('end')
        ;
        $factoryMock = $this->createMock(ProfilerFactoryInterface::class);

        $factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profilerMock);

        $container->set(ProfilerFactoryInterface::class, $factoryMock);
        $container->set(Profiler::class, $profilerMock);
        $eventDispatcher = new TraceableEventDispatcher(
            new EventDispatcher(),
            new Stopwatch()
        );

        $event = $this->createMock(ConsoleCommandEvent::class);

        $eventDispatcher->addSubscriber($container->get('buggregator_profiler.subscriber'));
        $eventDispatcher->dispatch($event, ConsoleEvents::COMMAND);
        $eventDispatcher->dispatch($event, ConsoleEvents::TERMINATE);

        $calledEvents = array_column($eventDispatcher->getCalledListeners(), 'event');
        $this->assertContains(
            ConsoleEvents::COMMAND,
            $calledEvents
        );
        $this->assertContains(
            ConsoleEvents::TERMINATE,
            $calledEvents
        );
    }

    #[Test]
    public function disabledProfilerDoesNotTriggerProfilerKernelEvents(): void
    {
        self::bootKernel(['environment' => 'config_disabled_env']);
        $container = self::getContainer();

        $this->assertFalse($container->has('buggregator_profiler.subscriber'));
    }
}
