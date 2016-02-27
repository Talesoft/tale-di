<?php

namespace Tale\Test\Http;

use Tale\Di\ContainerInterface;
use Tale\Di\ContainerTrait;


class App implements ContainerInterface
{
    use ContainerTrait;
}

class Config {}

class Service
{

    private $_config;

    public function __construct(Config $config)
    {

        $this->_config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}

class Cache extends Service
{
}

class Renderer extends Service
{

    private $_cache;

    public function setCache(Cache $cache)
    {

        $this->_cache = $cache;
        return $this;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->_cache;
    }

    public function render()
    {

        return get_class($this);
    }
}

class AwesomeRenderer extends Renderer
{}





class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testQueue()
    {

        $app = new App();
        $app->register(Config::class);
        $app->register(Cache::class);
        $app->register(AwesomeRenderer::class);

        $renderer = $app->get(Renderer::class);
        $this->assertInstanceOf(AwesomeRenderer::class, $renderer);
        $this->assertEquals(AwesomeRenderer::class, $renderer->render());
        $this->assertInstanceOf(Config::class, $renderer->getConfig());

        $cache = $app->get(Cache::class);
        $this->assertInstanceOf(Cache::class, $cache);
        $this->assertInstanceOf(Config::class, $cache->getConfig());
    }
}