<?php

declare(strict_types=1);

$configFile = __DIR__ . '/../config/packages/buggregator_profiler.yaml';

if (!file_exists($configFile)) {
    echo "ℹ️ Config file does not exist, nothing to remove: {$configFile}\n";
    exit(0);
}

unlink($configFile);

echo "🗑️ Removed config file: {$configFile}\n";
