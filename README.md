# A Buggregator profiler bundle for Symfony framework
A Symfony bundle that integrates [Buggregator](https://buggregator.dev) into your Symfony application, 
enabling you to send profiling data to a running Buggregator server instance.
See [Buggregator docs](https://docs.buggregator.dev/getting-started.html) to learn more about Buggregator itself 
and how to make the most out of it.

## Requirements

- **PHP:** 8.3 or 8.4
- **Symfony:** 6.4 or 7.4 or 8.0+ (for Symfony 8 PHP 8.4 is required)

## Installation

> ⚠️ Please mind this is a work in progress and not yet a stable release – breaking changes are possible.
> Please provide feedback, suggestions and bug reports using GitHub issues. Pull requests are very welcome!

The recommended way to install this bundle is as a **dev dependency**, since profiling is typically only needed during development:

```bash
composer require --dev velpl/buggregator-profiler-bundle
```

If you need profiling available in production environments as well, you can install it as a regular dependency:

```bash
composer require velpl/buggregator-profiler-bundle
```

## Configuration

### Using Symfony Flex (recommended)

This bundle ships with a [Symfony Flex](https://github.com/symfony/flex) recipe available 
in the [symfony/recipes-contrib](https://github.com/symfony/recipes-contrib) repository.

When installing the bundle via Composer with Flex enabled, you will be prompted to run the contrib recipe automatically.
**Accepting it** will:

- Register the bundle in `config/bundles.php`
- Create a default configuration file at `config/packages/buggregator_profiler.yaml`
- Add buggregator service to Docker `compose.yaml` so you can run Buggregator server locally

If you choose **not** to run the recipe automatically, you can refer to 
the [symfony/recipes-contrib repository](https://github.com/symfony/recipes-contrib) for the recipe contents 
and apply the configuration manually (see the [Manual Configuration](#manual-configuration) section below).

### Manual Configuration

If you are not using Flex or opted out of the recipe, register the bundle manually:

```php
// config/bundles.php
return [
    // ...
    Velpl\BuggregatorProfilerBundle\BuggregatorProfilerBundle::class => ['all' => true],
];
```

Then create the configuration file:

```yaml
# config/packages/buggregator_profiler.yaml
buggregator_profiler:
    enabled: false
    application_name: 'Symfony App'
    profiler_url: 'http://127.0.0.1:8000'
```

Set environment variables to configure the bundle (skips ones for which you want default values):
```dotenv
BUGGREGATOR_PROFILER_PORT=
BUGGREGATOR_PROFILER_URL=
BUGGREGATOR_BUNDLE_PROFILER_URL=${BUGGREGATOR_PROFILER_URL}:${BUGGREGATOR_PROFILER_PORT}
BUGGREGATOR_BUNDLE_SMTP_PORT=
BUGGREGATOR_BUNDLE_VARDUMPER_PORT=
BUGGREGATOR_BUNDLE_MONOLOG_PORT=
BUGGREGATOR_BUNDLE_CONTAINER_NAME=
BUGGREGATOR_BUNDLE_IMAGE_TAG=
```

It's highly recommended to set `BUGGREGATOR_BUNDLE_IMAGE_TAG` to a specific version 
of the Buggregator server image to avoid unexpected behavior.
You can find the available versions on 
[Buggregator server versions on GitHub](https://github.com/buggregator/server/pkgs/container/server/versions).

And finally add the Buggregator service to your Docker `compose.yaml`:

```yaml
  buggregator-profiler:
    image: ghcr.io/buggregator/server:${BUGGREGATOR_BUNDLE_IMAGE_TAG:-latest}
    container_name: ${BUGGREGATOR_BUNDLE_CONTAINER_NAME:-buggregator-profiler}
    ports:
      - "${BUGGREGATOR_PROFILER_PORT}:8000"
      - "${BUGGREGATOR_BUNDLE_SMTP_PORT}:1025"
      - "${BUGGREGATOR_BUNDLE_VARDUMPER_PORT}:9912"
      - "${BUGGREGATOR_BUNDLE_MONOLOG_PORT}:9913"
    restart: unless-stopped
```

## Configuration Reference

| Option             | Type    | Default                   | Description                                                      |
|--------------------|---------|---------------------------|------------------------------------------------------------------|
| `enabled`          | bool    | `false`                   | Whether profiling is active. Set to `true` to start sending data.|
| `application_name` | string  | `'Symfony App'`           | A label used to identify your application in Buggregator.        |
| `profiler_url`     | string  | `'http://127.0.0.1:8000'` | The URL of your running Buggregator server instance.             |

> **Note:** The bundle is disabled by default. Set `enabled: true` (or control it via an environment variable) to activate profiling.

## License

MIT
