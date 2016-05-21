
# Tale Di
**A Tale Framework Component**

# What is Tale Di?

Dependency Injection.

Tale DI can automatically inject constructor arguments and `setXxx`-style setter values to instances.
The DI container will manage these dependencies and lazily wire them together.

A really strict pattern ist enforced, but if you stick to it, this library can be really handy.

# Installation

Install via Composer

```bash
composer require "talesoft/tale-di:*"
composer install
```

# Usage

```php
use Tale\Di\ContainerInterface;
use Tale\Di\ContainerTrait;


class App implements ContainerInterface
{
    use ContainerTrait;
}

class Config {}

class Service
{

    private $config;

    public function __construct(Config $config)
    {

        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}

class Cache extends Service
{
}

class Renderer extends Service
{

    private $cache;

    public function setCache(Cache $cache)
    {

        $this->cache = $cache;
        return $this;
    }

    public function getCache()
    {
        return $this->cache;
    }

    public function render()
    {

        return get_class($this);
    }
}


$app = new App();
$app->register(Config::class);
$app->register(Cache::class);
$app->register(AwesomeRenderer::class);

var_dump($app->get(Renderer::class)->render()); //"AwesomeRenderer", Cache and Config are auto-wired and available
```
