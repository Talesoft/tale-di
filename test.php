<?php

namespace Some\App;

use Tale\Di\ContainerInterface;
use Tale\Di\ContainerTrait;

include 'vendor/autoload.php';

class App implements ContainerInterface
{
    use ContainerTrait;
}

class Config {}

class Service
{

    public function __construct(Config $config)
    {

        var_dump(get_class($this).' initialized with '.get_class($config));
    }
}

class Cache extends Service
{
}

class Renderer extends Service
{

    public function setCache(Cache $cache)
    {

        var_dump('Set '.get_class($this).'-Cache to '.get_class($cache));

        return $this;
    }

    public function render()
    {

        var_dump('Render from '.get_class($this));
    }
}

class AwesomeRenderer extends Renderer
{}


$app = new App();
$app->register(Config::class);
$app->register(Cache::class);
$app->register(AwesomeRenderer::class);

$app->get(Renderer::class)->render();

$cache = $app->get(Cache::class);
var_dump(get_class($cache));
