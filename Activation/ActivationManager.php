<?php

namespace Bamboo\Activation;

/**
 * Class ActivationManager
 *
 * @since 6.0.0
 */
class ActivationManager
{
    /** @var string */
    private static $PLUGIN_SLUG = null;

    /** @var ActivationDelegate */
    private static $DELEGATE = null;

    /**
     * Initializes the Activation manager class. This function must be called in your main plugin file as soon as possible.
     *
     * @param string             $mainPluginFile Pass __FILE__ from the main plugin file
     * @param string             $pluginSlug     The plugin slug (used for the options name)
     * @param ActivationDelegate $delegate       A delegate which will handle all deactivation and activation logic
     */
    public static function setup($mainPluginFile, $pluginSlug, $delegate)
    {
        self::$PLUGIN_SLUG = $pluginSlug;
        self::$DELEGATE = $delegate;

        register_activation_hook($mainPluginFile, array('\\Bamboo\\Activation\\ActivationManager', 'onActivate'));
        register_deactivation_hook($mainPluginFile, array('\\Bamboo\\Activation\\ActivationManager', 'onDeactivate'));
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Register the WP hooks to execute the deferred actions
     */
    public function registerHooks()
    {
        add_action('admin_init', array(&$this, 'runDeferredActions'), 5);
    }

    /**
     * Run all the deferred actions. This in fact executes the hook named after the action ID:
     * 'cuar/core/activation/run-deferred-action?action_id=' . $action_id
     */
    public function runDeferredActions()
    {
        $actions = $this->getDeferredActions(true);
        foreach ($actions as $action_id => $action)
        {
            do_action(self::$PLUGIN_SLUG . '/activation/execute-deferred-action?action_id=' . $action_id);
        }
        self::resetDeferredActions();

        do_action(self::$PLUGIN_SLUG . '/activation/after-deferred-actions');
    }

    /**
     * The activation callback for the plugin
     */
    public static function onActivate()
    {
        if (self::$DELEGATE != null)
        {
            self::$DELEGATE->onActivate();
        }
    }

    /**
     * The deactivation callback for the plugin
     */
    public static function onDeactivate()
    {
        if (self::$DELEGATE != null)
        {
            self::$DELEGATE->onDeactivate();
        }

        // Reset our list of scheduled actions
        self::resetDeferredActions();
    }

    /**
     * Schedule an action to be executed once at next page load
     *
     * @param string $action_id
     * @param int    $priority
     */
    public static function scheduleDeferredAction($action_id, $priority)
    {
        $actions = self::getDeferredActions();
        $actions[$action_id] = array(
            'id'       => $action_id,
            'priority' => $priority
        );
        self::saveDeferredActions($actions);
    }

    /**
     * Get the list of actions to be executed next time on admin_init
     *
     * @param bool $sort Sort the actions by priority or not?
     *
     * @return array the list of actions
     */
    protected static function getDeferredActions($sort = false)
    {
        $actions = get_option(self::$PLUGIN_SLUG . '_pending_activation_actions', array());
        if ($sort)
        {
            uasort($actions, array('\\Bamboo\\Activation\\ActivationManager', 'sort_actions_by_priority'));
        }

        return $actions;
    }

    /**
     * Save the list of actions to be executed next time on admin_init
     *
     * @param array $actions the list of actions
     */
    protected static function saveDeferredActions($actions)
    {
        update_option(self::$PLUGIN_SLUG . '_pending_activation_actions', $actions);
    }

    /**
     * Clear the list of actions to be executed next time on admin_init
     */
    protected static function resetDeferredActions()
    {
        delete_option(self::$PLUGIN_SLUG . '_pending_activation_actions');
    }

    /**
     * Callback for the uasort function that sorts the messages according to their priority
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    public static function sort_actions_by_priority($a, $b)
    {
        if (isset($a['priority']) && isset($b['priority']))
        {
            if ($a['priority'] == $b['priority'])
            {
                return 0;
            }
            else if ($a['priority'] < $b['priority'])
            {
                return -1;
            }
            else return 1;
        }
        else if (isset($a['priority']))
        {
            return 1;
        }
        else if (isset($b['priority']))
        {
            return -1;
        }

        return 0;
    }
} 