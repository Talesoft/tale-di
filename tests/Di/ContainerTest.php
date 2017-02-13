<?php

namespace Tale\Test\Di;

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

    /**
     * @return Config
     */
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

    /**
     * @return Cache
     */
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

    private $app;

    public function setApp(App $app)
    {

        $this->app = $app;
    }

    public function getApp()
    {

        return $this->app;
    }
}





class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testQueue()
    {

        $app = new App();
        $app->registerSelf();
        $app->register(Config::class);
        $app->register(Cache::class);
        $app->register(AwesomeRenderer::class);

        $renderer = $app->get(Renderer::class);
        $this->assertInstanceOf(AwesomeRenderer::class, $renderer);
        $this->assertEquals(AwesomeRenderer::class, $renderer->render());
        $this->assertInstanceOf(Config::class, $renderer->getConfig());
        $this->assertInstanceOf(App::class, $renderer->getApp());

        $cache = $app->get(Cache::class);
        $this->assertInstanceOf(Cache::class, $cache);
        $this->assertInstanceOf(Config::class, $cache->getConfig());

        $app = unserialize(serialize($app));

        $renderer = $app->get(Renderer::class);
        $this->assertInstanceOf(AwesomeRenderer::class, $renderer);
        $this->assertEquals(AwesomeRenderer::class, $renderer->render());
        $this->assertInstanceOf(Config::class, $renderer->getConfig());
        $this->assertInstanceOf(App::class, $renderer->getApp());

        $cache = $app->get(Cache::class);
        $this->assertInstanceOf(Cache::class, $cache);
        $this->assertInstanceOf(Config::class, $cache->getConfig());
    }
}