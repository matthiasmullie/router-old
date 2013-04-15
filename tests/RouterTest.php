<?php
require_once __DIR__.'/../Route.php';
require_once __DIR__.'/../Router.php';
require_once __DIR__.'/../Exception.php';
require_once 'PHPUnit/Framework/TestCase.php';

use MatthiasMullie\Router;

/**
 * Router test case.
 */
class Tests extends PHPUnit_Framework_TestCase
{
    private $router;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->router = new Router\Router(__DIR__.'/example/routes.xml');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->router = null;
        parent::tearDown();
    }

    /**
     * Test url processing rules.
     *
     * @test
     * @dataProvider dataProvider
     */
    public function getUrl($url, $controller, $action, $slugs)
    {
        $result = $this->router->route($url);

        $this->assertEquals($result->getController(), $controller);
        $this->assertEquals($result->getAction(), $action);
        $this->assertEquals($result->getSlugs(), $slugs);
    }

    /**
     * Test url building rules.
     *
     * @test
     * @dataProvider dataProvider
     */
    public function createUrl($url, $controller, $action, $slugs)
    {
        $result = $this->router->getUrl($controller, $action, $slugs);

        $this->assertEquals($result, $url);
    }

    /**
     * Test cases [input, expected controller, expected action, expected slugs]
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array('/blog/detail/this-is-a-test/', 'blog', 'detail', array('this-is-a-test')),
            array('/blog/browse/', 'blog', 'browse', array()),
            array('/blog/', 'blog', 'index', array()),
            array('/', 'core', 'index', array()),
        );
    }
}
