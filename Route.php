<?php
namespace MatthiasMullie\Router;

/**
 * Processed route information: controller/action/slugs.
 *
 * @author Matthias Mullie <router@mullie.eu>
 */
class Route
{
    /**
     * Controller name determined by routing.
     *
     * @var string
     */
    protected $controller;

    /**
     * Action name determined by routing.
     *
     * @var string
     */
    protected $action;

    /**
     * Array of all slugs in url.
     *
     * @var array
     */
    protected $slugs = array();

    /**
     * The url.
     *
     * @var string
     */
    protected $url;

    /**
     * Construct a route.
     */
    public function __construct()
    {
        /*
         * Nothing here, this is just a very simple object.
         * The juicy stuff is in Router.
         */
    }

    /**
     * Set the controller.
     *
     * @param  string $controller The controller name.
     * @return Route
     */
    public function setController($controller)
    {
        $this->controller = (string) $controller;

        return $this;
    }

    /**
     * Set the action.
     *
     * @param  string $action The action name.
     * @return Route
     */
    public function setAction($action)
    {
        $this->action = (string) $action;

        return $this;
    }

    /**
     * Set a slug.
     *
     * @param int|string $slug The variable-name, either a string for a value's name in routing
     *        or an int for the position in the url.
     * @param  string $value The value tied to the slug.
     * @return Route
     */
    public function setSlug($slug, $value)
    {
        $slug = is_int($slug) ? $slug : (string) $slug;

        $this->slugs[$slug] = (string) $value;

        return $this;
    }

    /**
     * Set multiple slugs.
     *
     * @param array $slugs A key-value array where the keys are the slug names and the
     *        values are (obviously) the corresponding values.
     * @return Route
     */
    public function setSlugs(array $slugs)
    {
        $this->slugs = array_merge($this->slugs, $slugs);

        return $this;
    }

    /**
     * Set the url.
     *
     * @param  string $url The url.
     * @return Route
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;

        return $this;
    }

    /**
     * Fetch the controller.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Fetch the action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Fetch a slug.
     *
     * @param int|string $slug The variable-name, either a string for a value's name in routing
     *        or an int for the position in the url.
     * @return string
     */
    public function getSlug($slug)
    {
        return isset($this->slugs[$slug]) ? $this->slugs[$slug] : null;
    }

    /**
     * Fetch all slugs.
     *
     * @return array
     */
    public function getSlugs()
    {
        return $this->slugs;
    }

    /**
     * Fetch the url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
