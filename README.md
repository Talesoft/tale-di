
# Tale Di
**A Tale Framework Component**

# What is Tale Di?

A PSR-11 compatible DI Container. Quick, small and easy to use.

Tale DI can automatically inject constructor arguments and `set*`-style setter values to instances.
The DI container will manage these dependencies and lazily wire them together.

A really strict pattern ist enforced for consistency reasons, ease of use and beauty. If you stick to it, this library can be really handy.

# Installation

Install via Composer

```bash
composer require talesoft/tale-di
```


# Basic usage

Throw some classes into the container, get fully wired instances out. No keys are used, only class names. No other types than objects can be wired by the DI.

```php
use Tale\Di\Container;

$container = (new Container)
    ->registerSelf() //Registers the container itself
    ->register(MyCache::class)
    ->register(MyDatabase::class)
    ->register(MyController::class)
    ->register(MyForm::class)
    ->registerInstance($someObject); //Assume it's of class CustomObject
    
$container->get(Container::class); //The container
$container->get(MyCache::class); //MyCache instance
$container->get(MyController::class); //MyController instance
$container->get(MyForm::class); //MyForm instance
$container->get(CustomObject::class); //Same as $someObject
```


# Auto-wiring

Tale DI scans all classes it registers and automatically wires dependencies for it. You can inject dependencies in two ways

### Constructor Injection

Assume `MyDatabase` explicitly requires `MyCache`

```php

class MyDatabase
{

    private $cache;
    
    public function __construct(MyCache $cache)
    {
        
        $this->cache = $cache;
    }
}
```

Nothing else required. When using

```php
$container->get(MyDatabase::class);
```

you'll always receive the same `MyDatabase`-instance and a fixed instance of `MyCache` will be fed to the constructor out of the DI container.

If the dependency doesn't exist, a `NotFoundException` will be thrown (at `register` already), unless you've given it a default value `MyCache $cache = null`.


### Setter Injection

Tale DI scans all methods that start with `set`. If they fit the following criteria

- They start with `set`, whatever comes after it doesn't matter
- They have exactly one argument
- The single argument has a class typehint
- The single argument doesn't have a default value/isn't optional

the DI container will try to fill them automatically.

```php

class MyDatabase
{

    private $cache;
    
    public function setCache(MyCache $cache)
    {
        
        $this->cache = $cache;
    }
}
```

If the dependency was not found in the container, it is ignored (Meaning, all setter injections are optional)


# Inheritance

A derived class can be found be getting its base class or interface.

```php

class JsonCache implements CacheInterface {}

$container
    ->register(JsonCache::class);
    
$container->get(CacheInterface::class); //JsonCache instance
```

This makes it possible to easily provide other implementations for services already stored early in the container.

If you have both, a `Cache`-instance and a `JsonCache`-instance where `JsonCache` derived from `Cache`, you either wanted it (e.g. overwriting an existing service)
or you probably have a structural error (e.g. better use interfaces)


# Passing configuration to classes

In symfony DI you can easily inject option arrays into classes (`%options_array%`). This is not possible in Tale DI.

Configuration values with Tale DI are supposed to be full classes (e.g. `AppConfig`, `DatabaseConfig`, `CacheConfig`)


# Caching

Caching in Tale DI is pretty straight-forward. The whole container is serializable and will only store structural information, but not object instances.
On deserialization, no register, analyze or wire is needed anymore, just receiving instances directly.


```php

$app = (new Container)
    ->register(A::class)
    ->register(B::class)
    ->register(C::class)
    ->register(D::class)

//Serialize, e.g. into cache
file_put_contents(__DIR__.'/container', serialize($app));


//At later use/page call, get container from cache
$app = unserialize(file_get_contents(__DIR__.'/container'));

$app->get(C::class); //C-instance, fully wired
```


# Persistence

Dependencies can be registered unpersistent which will lead to have a new instance created whenever you call `get` on the class name. To make a class unpersistent, use the second parameter of `register`.
This can act as some kind of factory inside the container.

```php

$app = (new Container)
    ->register(UpdateEvent::class, false);
    
$app->get(UpdateEvent::class); //UpdateEvent instance(1)
$app->get(UpdateEvent::class); //UpdateEvent instance(2)
$app->get(UpdateEvent::class); //UpdateEvent instance(3)
```

When registering existing object instances (through `registerInstance` or `registerSelf`), they are always persistent.


# Usage Example

```php
use Tale\Di\ContainerInterface;
use Tale\Di\ContainerTrait;

//Use in any class as a drop-in
class App extends SomeBaseAppImNotAllowedToTouch implements ContainerInterface
{
    use ContainerTrait;
}

//Could also be:
//use Tale\Di\Container;
//class App extends Container {}

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

class AwesomeRenderer extends Renderer
{
}


$app = (new App)
    ->register(Config::class);
    ->register(Cache::class);
    ->register(AwesomeRenderer::class);

$app->get(Renderer::class)->render(); //"AwesomeRenderer"
$app->get(Renderer::class)->getConfig(); //Config instance
$app->get(Renderer::class)->getCache(); //Cache instance
```
