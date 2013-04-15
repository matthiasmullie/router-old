<?php
namespace MatthiasMullie\Router;

/**
 * Extract information (controller/action/slugs) from a URL (according to an arbitrary XML
 * route schema) and build urls (according to that same schema) from controller/action/slugs.
 *
 * @author Matthias Mullie <router@mullie.eu>
 */
class Router
{
    /**
     * Path to the routing XML-file.
     *
     * @var string
     */
    protected $file;

    /**
     * XML-object of the routing schema.
     *
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * Construct router.
     *
     * @param string[optional] $file The path to the routing-xml file.
     */
    public function __construct($file = null)
    {
        if($file === null) $this->file = dirname(__FILE__) . '/router.xml';
        else $this->file = (string) $file;

        $this->xml = new \SimpleXMLElement($this->file, 0, true);
    }

    /**
     * Generate a valid url for a controller/action based on the routing.
     *
     * @param string $controller The controller to map to.
     * @param string $action The action to map to.
     * @param array[optional] $slugs  Additional paramaters.
     * @return string The url the controller/action/slugs map to.
     */
    public function getUrl($controller, $action, array $slugs = array())
    {
        /**
         * Parse the match to an url.
         *
         * @param \SimpleXMLElement $route The SimpleXMLElement node for this route.
         * @param string controller The controller to map to.
         * @param string $action The action to map to.
         * @param array $slugs Additional paramaters.
         * @return string The url the controller/action/slugs map to.
         */
        $parseMatch = function($route, $controller, $action, $slugs) {
            // replace controller/action backreferences (if any)
            $match = $route->attributes()->match;
            $match = str_replace($route->attributes()->controller, $controller, $match);
            $match = str_replace($route->attributes()->action, $action, $match);

            // show optional parts
            $match = str_replace(array('(', ')'), '', $match);

            // replace slugs
            foreach ($slugs as $variable => $slug) {
                $replaced = 0;

                // replace named parameters
                if(is_string($variable)) $match = str_replace(':' . $variable, $slug, $match, $replaced);

                // replace wildcard *
                elseif(mb_substr_count($match, '?')) $match = preg_replace('/\?/', $slug, $match, 1, $replaced);

                // replace wildcard ?
                elseif(mb_substr_count($match, '*')) $match = preg_replace('/\*/', '*/' . $slug . '/', $match, 1, $replaced);

                // slug could not be set = invalid route
                if(!$replaced) return;
            }

            // if we have not assigned all parameters in url, route is invalid
            if(mb_substr_count($match, ':') || mb_substr_count($match, '?')) return;

            /*
             * cleanup wildcard mess: to ensure that we replaced the values in
             * the exact position at the wildcards, we may have used exessive
             * slashes and left asterisks
             */
            $match = str_replace('*', '', $match);
            $match = str_replace('//', '/', $match);

            return $match;
        };

        // possible matches for controller/action (in order of priority)
        $xpaths = array('/routes/route[@controller="' . $controller . '" and @action="' . $action . '"]',
            '/routes/route[@controller="' . $controller . '" and starts-with(@action, ":")]',
            '/routes/route[starts-with(@controller, ":") and @action="' . $action . '"]',
            '/routes/route[starts-with(@controller, ":") and starts-with(@action, ":")]');
        foreach ($xpaths as $xpath) {
            // find nodes with this exact controller & action
            foreach ($this->xml->xpath($xpath) as $route) {
                // parse match to url
                $url = $parseMatch($route, $controller, $action, $slugs);
                if($url) return $url;
            }
        }

        throw new Exception('No routes found for controller "' . $controller . '" with action "' . $action . ($slugs ? '" (slugs: "' . implode('", "', $slugs) . '")' : '') . '.');
    }

    /**
     * Parse a request url based on routing schema.
     *
     * @param  string $url The url to parse.
     * @return Route  The matching route object.
     */
    public function route($url)
    {
        /**
         * Parse the match to a valid route.
         *
         * @param \SimpleXMLElement $route The SimpleXMLElement node for this route.
         * @param string $url The url to parse.
         * @return Route The matching route object.
         */
        $parseMatch = function($route, $url) {
            $controller = $route->attributes()->controller;
            $action = $route->attributes()->action;

            // build regex based on match
            $regex = $route->attributes()->match;
            $regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
            $regex = str_replace('*', '(?:.*)', $regex);
            $regex = preg_replace('/\?[^:]/', '(?:[^/]*)', $regex);
            $regex = preg_replace('/:([a-z]+)/i', '(?<\\1>[^/]*?)', $regex);

            // match url to regex'ed route
            if (preg_match_all('|^' . $regex . '$|', $url, $result, PREG_SET_ORDER)) {
                // replace backreferences in controller & action
                if(isset($result[0][mb_substr($controller, 1)])) $controller = $result[0][mb_substr($controller, 1)];
                if(isset($result[0][mb_substr($action, 1)])) $action = $result[0][mb_substr($action, 1)];

                // if controller/action aren't resolved = route is invalid
                if(substr($controller, 0, 1) == ':' || substr($action, 01) == ':') return;

                // fetch slugs
                $slugs = array_unique(array_merge($result[0], explode('/', trim($url, '/'))));

                // remove full url, controller & action from slugs
                array_shift($slugs);
                unset($slugs[substr($route->attributes()->controller, 1)]);
                unset($slugs[substr($route->attributes()->action, 1)]);
                foreach($slugs as $i => $slug) if(empty($slug)) unset($slugs[$i]);

                $route = new Route();
                $route->setUrl((string) $url)
                    ->setController($controller)
                    ->setAction($action)
                    ->setSlugs($slugs);

                return $route;
            }
        };

        // possible matches for controller/action (in order of priority)
        $xpaths = array('/routes/route[not(starts-with(@controller, ":")) and not(starts-with(@action, ":"))]',
            '/routes/route[not(starts-with(@controller, ":")) and starts-with(@action, ":")]',
            '/routes/route[starts-with(@controller, ":") and not(starts-with(@action, ":"))]',
            '/routes/route[starts-with(@controller, ":") and starts-with(@action, ":")]');
        foreach ($xpaths as $xpath) {
            // find nodes with this exact controller & action
            foreach ($this->xml->xpath($xpath) as $route) {
                // parse match to route
                $route = $parseMatch($route, $url);
                if($route) return $route;
            }
        }

        throw new Exception('No routes found for "' . $url . '".');
    }

    /**
     * Add a routing entry.
     *
     * @param string $controller The controller to route to.
     * @param string $action The action to route to.
     * @param string $match The match to route to.
     */
    public function addRoute($controller, $action, $match)
    {
        // append to xml
        $route = $this->xml->addChild('route');
        $route->addAttribute('match', (string) $match);
        $route->addAttribute('controller', (string) $controller);
        $route->addAttribute('action', (string) $action);

        // write to file
        if (!file_put_contents($this->file, $this->xml->asXML())) {
            // writing to file failed, remove new route from memory
            unset($this->xml->route[count($this->xml->route) - 1]);

            throw new Exception('Could not add route. "' . $this->file . '" is not writable.');
        }
    }
}
