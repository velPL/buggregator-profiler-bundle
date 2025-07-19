<?php
declare(strict_types=1);

$configDir = __DIR__ . '/../config/packages';
$configFile = $configDir . '/buggregator_profiler.yaml';

if (!is_dir($configDir)) {
    echo "Config directory does not exist: {$configDir}\n";
    exit(0);
}

if (file_exists($configFile)) {
    echo "Config file already exists, skipping: {$configFile}\n";
    exit(0);
}

file_put_contents($configFile, <<<YAML
buggregator_profiler:
    enabled: false
    application_name: 'Symfony App'
    profiler_url: 'http://127.0.0.1:8000'
YAML
);

echo "✅ Created default config file: {$configFile}\n";
