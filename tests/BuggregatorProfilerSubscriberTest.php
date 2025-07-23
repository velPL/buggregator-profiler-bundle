<?php

declare(strict_types=1);

namespace Velpl\BuggregatorProfilerBundle\Tests;

use PHPUnit\Framework\Attributes\Test;
use SpiralPackages\Profiler\Driver\NullDriver;
use SpiralPackages\Profiler\Profiler;
use SpiralPackages\Profiler\Storage\NullStorage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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

        $factoryMock = $this->createMock(ProfilerFactoryInterface::class);

        $factoryMock->method('create')->willReturn(new Profiler(
            new NullStorage(),
            new NullDriver(),
            'TestApp',
        ));
        $container->set(ProfilerFactoryInterface::class, $factoryMock);
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
    public function disabledProfilerDoesNotTriggerProfilerKernelEvents(): void
    {
        self::bootKernel(['environment' => 'config_disabled_env']);
        $container = self::getContainer();

        $this->assertFalse($container->has('buggregator_profiler.subscriber'));
    }
}
