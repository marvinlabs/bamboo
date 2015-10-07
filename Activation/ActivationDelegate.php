<?php

namespace Bamboo\Activation;

/**
 * Place to implement the (de)activation logic specific to a plugin
 */
interface ActivationDelegate
{
    /**
     * Called when the plugin is activated. You should not do much work here. Instead, this is a place to
     * queue deferred actions that will be executed on next page refresh.
     */
    public function onActivate();

    /**
     * Called when the plugin is deactivated
     */
    public function onDeactivate();
}