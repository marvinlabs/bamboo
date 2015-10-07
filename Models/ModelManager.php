<?php

namespace Bamboo\Models;

/**
 * Class ModelManager
 * @package Bamboo\Models
 *
 *          A class which handles all hooks to declare our models, flush associated rewrite rules, etc.
 *
 *          Model classes are supposed to implement the following static methods:
 *
 *          - registerWordPressTypes    -required- (called on 'init' hook)
 *          - registerCustomFields      -optional- (called on 'cmb2_init' hook)
 */
class ModelManager
{
    /** @var array[string] all model classes we are using */
    private $modelClasses;

    /**
     * Constructor
     *
     * @param array [string] $modelClasses
     */
    function __construct($modelClasses)
    {
        $this->modelClasses = $modelClasses;
        $this->registerHooks();
    }

    /**
     * Register all WordPress hooks to let the magic happen
     */
    protected function registerHooks()
    {
        add_action('init', array(&$this, 'registerWordPressTypes'));
        add_action('cmb2_init', array(&$this, 'registerCustomFields'));
    }

    /**
     * Registers all WordPress types (custom posts and taxonomies) for all model classes
     */
    public function registerWordPressTypes()
    {
        foreach ($this->modelClasses as $mc)
        {
            /** @noinspection PhpUndefinedMethodInspection */
            $mc::registerWordPressTypes();
        }
    }

    /**
     * Registers all WordPress types (custom posts and taxonomies) for all model classes
     */
    public function registerCustomFields()
    {
        foreach ($this->modelClasses as $mc)
        {
            if (method_exists($mc, 'registerCustomFields'))
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $mc::registerCustomFields();
            }
        }
    }
}