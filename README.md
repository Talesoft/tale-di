
[![Packagist](https://img.shields.io/packagist/v/talesoft/tale-di.svg?style=for-the-badge)](https://packagist.org/packages/talesoft/tale-di)
[![License](https://img.shields.io/github/license/Talesoft/tale-di.svg?style=for-the-badge)](https://github.com/Talesoft/tale-di/blob/master/LICENSE.md)
[![CI](https://img.shields.io/travis/Talesoft/tale-di.svg?style=for-the-badge)](https://travis-ci.org/Talesoft/tale-di)
[![Coverage](https://img.shields.io/codeclimate/coverage/Talesoft/tale-di.svg?style=for-the-badge)](https://codeclimate.com/github/Talesoft/tale-di)

Tale DI
=======

What is Tale DI?
------------------

Tale DI is a lightweight implementation of the PSR-11
Dependency Injection spec will full auto-wiring support.

The API might still change here and there.

Installation
------------

```bash
composer req talesoft/tale-di
```

Usage
-----

### ContainerBuilder

`use Tale\Di\ContainerBuilder;`

Using the ContainerBuilder, you can auto-wire a fully working
DI container including full PSR-6 cache support.

The container returned will be a `Tale\Di\Container` which is 
explained below.

```php
$cachePool = new RedisCachePool();

$builder = new ContainerBuilder($cachePool);

$builder->add(ViewRenderer::class);

$builder->add(ControllerDispatcher::class);

$builder->addInstance(new PDO(...));

$container = $builder->build();

$pdo = $container->get(PDO::class);
```

### Service Locators

```
use Tale\Di\ServiceLocator\FileServiceLocator;
use Tale\Di\ServiceLocator\DirectoryServiceLocator;
use Tale\Di\ServiceLocator\GlobServiceLocator;
```

If you don't want to add every single file manually,
you can also use one of the three service Locators
that come with Tale DI.

```php
$builder->addLocator(
    new FileServiceLocator('src/Classes/MyClass.php')
);

$builder->addLocator(
    new DirectoryServiceLocator('../src')
);

$builder->addLocator(
    new GlobServiceLocator('../src/{Controller,Model}/**/*.php')
);

$container = $builder->build();
```

### Injections and philosophy

Tale DI, by design, only allows constructor injections. There
are no optional dependencies that are not covered in a constructor
and there are no `XyzAware` interfaces and no possibility to do it.

This avoids a lot of magic and defensive null checks all over your
code. If this is not what you like, Tale DI might not be what 
you're looking for. I suggest you give it a try anyways.

Injections happen simply by class name or an interface it implements:

```php
class OrderProvider
{
    public function __construct(OrderRepository $repository)
}

$orderProvider = $container->get(OrderProvider::class);
```

It doesn't matter if you ever added the dependency to the container,
it will auto-wire any external (or internal) dependency that has a 
readable type (and even that can be handled). This is possible
because Tale DI works solely based on PHPs existing class mechanisms,
interfaces and reflection.

```php
interface ViewRendererInterface
{
}

class ViewRenderer implements ViewRendererInterface
{
}

//...

$renderer = $container->get(ViewRendererInterface::class);
// $renderer is instanceof ViewRenderer
```

Optional dependencies work as expected and should be defaulted
to default/null implementations so no defensive null checks are
required.

```php
class AvatarGenerator
{
    /**
     * @var CacheInterface
     */
    private $cache;
    
    public function __construct(CacheInterface $cache = null)
    {
        $this->cache = $cache ?? new RuntimeCache();
    }
}
```

### Iterables and Arrays of interfaces

Tale DI has a notion of **Tags** specified through
plain PHP interfaces. You can inject by using interfaces:

```php
class EntityManager
{
    public function __construct(DbalInterface $dbal)
}
```

and through proper type-hinting in the doc-comment you can
even inject all instances of a specific interface:

```php
class Importer
{
    /**
     * @param iterable<\App\Service\Importer\WorkerInterface>
     */
    public function __construct(iterable $workers)
    {
        foreach ($workers as $worker) {
            $this->initializeWorker($worker);
        }
    }
    
    /**
     * @param array<\App\Service\Importer\WorkerInterface>
     */
    public function __construct(array $workers)
    {
        $this->workers = array_filter($workers, fn($worker) => $worker->canImport());
    }
}
```

This will inject all known dependencies of type WorkerInterface
into the `workers` argument.

### Parameters

Tale DI can't only inject classes and instances, you can specify
fixed parameters that are injected based on their names. Notice
they should be serializable to make use of the caching mechanisms.

```php
$builder->setParameter('someParameter', 'some value');

//Any class with a parameter 'someParameter' will get 'some value' injected as a string
```

The second parameter allows you to reduce the parameter
to be used on a specific class or interface only.

This also allows creating instances of external types
without requiring a specific factory for it:

```php
class UserManager
{
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo; // Fully working PDO instance!
    }
}

$builder->setParameters([
    // These are the parameter names PDO internally uses in its constructor!
    'dsn' => 'mysql:host=localhost',
    'username' => 'root', 
    'passwd' => '', 
    'options' => [PDO::ATTR_ERRMODE => PDO_ERRMODE_EXCEPTION]
], PDO::class);

$builder->add(UserManager::class);
$container = $builder->build();

$userManager = $container->get(UserManager::class);
// PDO will be fully wired in UserManager
```

### Manually construct container

The base setup of the container is pretty simple. Most of the stuff is
used for the auto-wiring mechanism, but you can also completely avoid
the `ContainerBuilder` and use containers directly.

Tale DI brings three base containers with it that you can use for their
specific purposes.

#### Container

`use Tale\Container;`

The Tale Container and the heart of the DI system is a container
that resolves values through specific `Tale\Di\DependencyInterface`
instances. This is how it works:

```php
$dependencies = [
    //Value Dependency is just a value. Can be any kind of value.
    'test value' => new ValueDependency('some value'),
    
    //A reference dependency references another value in the container
    'reference' => new ReferenceDependency('test value'),
    
    //A callback dependency only gets resolved when it's requested
    'factory' => new CallbackDependency(function (ContainerInterface $container) {
        return new Something($container->get(SomethingElse::class));
    }),
    
    //Same as CallbackDependency, but will cache the result between each ->get() call
    'lazy factory' => new PersistentCallbackDependency(function () {
        return (new SomeHeavyWorker())->getResult();
    })
];

$container = new Container($dependencies);

$container->get('test value'); //"some value"
$container->get('reference'); //"some value"
// etc.
```

You can always define own dependency types and how they are resolved
by implementing `Tale\Di\DependencyInterface`:

```php
final class PdoDependency implements DependencyInterface
{
    private $pdo;
    
    public function __construct()
    {
        $this->pdo = new PDO(...);
    }
    
    public function get(ContainerInterface $container)
    {
        return $this->pdo;
    }
}

$container = new Container(['pdo' => new PdoDependency()]);
$pdo = $container->get('pdo');

$stmt = $pdo->prepare(...);
```

#### ArrayContainer

`use Tale\Di\Container\ArrayContainer;`

Mostly useful for testing, this is a really basic implementation
of PSR-11 that just maps fixed names to values

```php
$container = new ArrayContainer([
    SomeClass::class => new SomeClass(),
    'test key' => 15
]);

$container->get(SomeClass::class); //SomeClass instance
$container->get('test key'); //15
```

#### NullContainer

`use Tale\Di\Container\NullContainer;`

A Null Container that always returns false when calling `->has()` and
always throws a `NotFoundException` when calling `->get()`.

This is mostly useful as a default container for classes that want
to decorate containers as an optional dependency and require a 
default implementation to avoid defensive null checks.

```php
final class AdapterFactory
{
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?? new NullContainer();
    }
    
    public function createAdapter(): AdapterInterface
    {
        $adapter = null;
        if ($this->container->has(SomeAdapter::class)) {
            $adapter = $this->container->get(SomeAdapter::class);
        } else if ($this->container->has(SomeOtherAdapter::class)) {
            $adapter = $this->container->get(SomeOtherAdapter::class);
        } else {
            $adapter = new SomeDefaultAdapter();
        }
        return $adapter;
    }
}
```

### Type Information

`use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;`

Notice this will probably end up in an own library at some point.

```php
$typeInfoFactory = new PersistentTypeInfoFactory();

$typeInfo = $typeInfoFactory->get('array<int>');

$typeInfo->isGeneric() //true

$typeInfo->getGenericType()->getName() //array

$typeInfo->getGenericTypeParameters()[0]->getName() //int
```


### Parameter Reader

`use Tale\Di\ParameterReader\DocCommentParameterReader;`

A parameter reader that also takes into account
doc comment `@param`-annotations

```php
$typeInfoFactory = new PersistentTypeInfoFactory();

$paramReader = new DocCommentParameterReader($typeInfoFactory);

$reflClass = new ReflectionClass(SomeClass::class);

$params = $paramReader->read($reflClass->getMethod('__construct');

var_dump($params);
```
