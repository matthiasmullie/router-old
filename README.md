# Router

## Example usage

    // init router
    use MatthiasMullie\Router;
    $router = new Router\Router(__DIR__.'/tests/example/routes.xml');

    // parse url & get path
    $route = $router->route($_SERVER['REQUEST_URI']);
    $controller = $router->getController();
    $action = $router->getAction();
    include "/$controller/$action.php";

    // build url
    $url = $router->getUrl($controller, $action);
    echo "<a href='$url'>Link</a>";

## License
Ogone is [MIT](http://opensource.org/licenses/MIT) licensed.
