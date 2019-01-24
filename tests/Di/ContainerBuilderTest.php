<?php declare(strict_types=1);

namespace Tale\Test\Di;

use PHPUnit\Framework\TestCase;
use Tale\Cache\Pool\NullPool;
use Tale\Di\ClassLocator\GlobClassLocator;
use Tale\Di\ContainerBuilder;
use Tale\Test\Di\TestApp\App;

/**
 * @coversDefaultClass \Tale\Di\ContainerBuilder
 */
class ContainerBuilderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::process
     * @group test
     */
    public function test(): void
    {
//        ini_set('xdebug.var_display_max_depth', '5');
//        $locator = new GlobClassLocator(__DIR__ . '/TestApp/{*.php,**/*.php}');
//        $builder = new ContainerBuilder(new NullPool());
//        $builder->addLocator($locator);
//        $builder->setParameter('someInterface', new \DateTimeImmutable('+2 weeks'), App::class);
//        $container = $builder->build();
//        ob_start();
//        \PDO::
//        $app = $container->get(App::class);
//        var_dump($app);
//        file_put_contents(__DIR__.'/test.txt', ob_get_clean());
    }
}