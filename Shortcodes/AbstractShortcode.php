<?php

namespace Bamboo\Shortcodes;

/**
 * A shortcode base class
 */
abstract class AbstractShortcode
{
    /** @var string The shortcode name */
    protected $name;

    /**
     * Constructor
     *
     * @param string $name The shortcode name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->register_hooks();
    }

    /**
     * Hook into WP
     */
    protected function register_hooks()
    {
        add_shortcode($this->name, array(&$this, 'do_shortcode'));
    }

    /**
     * @return array An associative array with all the default parameter values
     */
    protected function get_default_param_values()
    {
        return array();
    }

    /**
     * @return array A list of parameter keys that are required
     */
    protected function get_required_params()
    {
        return array();
    }

    /**
     * Actually process the shortcode (output stuff, ...)
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
    protected abstract function process_shortcode($params, $content);

    /**
     * The WordPress hook that gets called when the shortcode is detected. This basically checks parameters, provides default values, and calls the child
     * class' process_shortcode function.
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
    public function do_shortcode($params = array(), $content = null)
    {
        // default parameters
        $params = shortcode_atts($this->get_default_param_values(), $params);

        // Check required parameters
        foreach ($this->get_required_params() as $key)
        {
            if ( !isset($params[$key]) || empty($params[$key]))
            {
                return sprintf(__('The shortcode <code>[%1$s]</code> is missing required parameter: %2$s', 'cuar'),
                    $this->name, $key);
            }
        }

        // Run the shortcode
        return $this->process_shortcode($params, $content);
    }
}