<?php

namespace Bamboo\Assets;

/**
 * Class AssetManager
 * @package Bamboo\Assets
 *
 *          Manage styles, scripts, etc.
 */
class AssetsManager
{
    /** @var array The manifest file */
    private $manifest = null;

    /** @var array The assets which are required and will be included in the footer */
    private $requiredAssets = array();

    /** @var array[AssetsRegistrationDelegate] */
    private $registrationDelegates = array();

    /** @var string The version of the plugin */
    private $defaultAssetVersion = '';

    /**
     * Constructor
     *
     * @param string|null $manifestPath                        Path to an asset manifest file which specifies file hashes or version number
     * @param string      $defaultAssetVersion                 The default version number which will be used as a version number for the asset URL to bust
     *                                                         cache if manifest is not used
     */
    public function __construct($manifestPath = null, $defaultAssetVersion = '1')
    {
        $this->defaultAssetVersion = $defaultAssetVersion;

        if ($manifestPath != null)
        {
            $this->loadAssetsManifest($manifestPath);
        }

        $this->registerHooks();
    }

    /**
     * Hook into WordPress
     */
    protected function registerHooks()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array(&$this, 'registerAssets'));
            add_action('admin_footer', array(&$this, 'printFooter'));
            add_action('admin_head', array(&$this, 'printHeader'));
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'registerAssets'));
            add_action('wp_footer', array(&$this, 'printFooter'));
            add_action('wp_header', array(&$this, 'printHeader'));
        }
    }

    /**
     * @param AssetsRegistrationDelegate $delegate The delegate which will register the assets
     */
    public function addRegistrationDelegate($delegate)
    {
        $this->registrationDelegates[] = $delegate;
    }

    /**
     * Register a style
     *
     * @param string $handle       The style identifier when you'll call requireStyle later on
     * @param string $url          The URL to the style
     * @param string $manifestId   The manifest key to look for. If you leave this empty, we will look for the handle in the manifest.
     * @param array  $dependencies Dependencies to other scripts/styles
     * @param string $forceVersion A version number to use (if empty, we will try to get the version from the manifest or fallback to the plugin version number)
     * @param string $media        CSS medias targeted
     */
    public function registerStyle($handle, $url, $manifestId = '', $dependencies = array(), $forceVersion = '', $media = 'all')
    {
        $version = $this->getAssetVersion($handle, $manifestId, $forceVersion);
        wp_enqueue_style($handle, $url, $dependencies, $version, $media);
    }


    /**
     * Register a style
     *
     * @param string $handle       The style identifier when you'll call requireStyle later on
     * @param string $url          The URL to the style
     * @param string $manifestId   The manifest key to look for. If you leave this empty, we will look for the handle in the manifest.
     * @param array  $dependencies Dependencies to other scripts/styles
     * @param string $forceVersion A version number to use (if empty, we will try to get the version from the manifest or fallback to the plugin version number)
     * @param bool   $inFooter     Whether this should be printed in the page footer or header
     */
    public function registerScript($handle, $url, $manifestId = '', $dependencies = array(), $forceVersion = '', $inFooter = true)
    {
        $version = $this->getAssetVersion($handle, $manifestId, $forceVersion);
        wp_enqueue_script($handle, $url, $dependencies, $version, $inFooter);
    }

    /**
     * Tell the system that we will require a script to be enqueued in the page footer.
     *
     * @param string $handle   The script handle
     * @param bool   $inFooter Whether this should be printed in the page footer or header
     */
    public function requireScript($handle, $inFooter = true)
    {
        $this->requiredAssets[$handle] = array(
            'type'     => 'script',
            'location' => ($inFooter ? 'footer' : 'header')
        );
    }

    /**
     * Tell the system that we will require a script to be enqueued in the page footer.
     *
     * @param string  $handle   The script handle
     * @param boolean $inFooter Whether this should be printed in the page footer or header
     */
    public function requireStyle($handle, $inFooter = false)
    {
        $this->requiredAssets[$handle] = array(
            'type'     => 'style',
            'location' => ($inFooter ? 'footer' : 'header')
        );
    }

    /**
     * Enqueue required scripts and styles. Note that the theme may embed those styles and scripts if it wants to. In which case it may declare theme support
     * for this feature.
     *
     * This function is not meant to be called directly, this is attached to the proper WordPress hook.
     */
    public function registerAssets()
    {
        /** @var AssetsRegistrationDelegate $d */
        foreach ($this->registrationDelegates as $d)
        {
            $d->registerAssets($this);
        }
    }

    /**
     * Prints the scripts which have been required to be printed
     */
    public function printHeader()
    {
        $this->printAssets('header');
    }

    /**
     * Prints the scripts which have been required to be printed
     */
    public function printFooter()
    {
        $this->printAssets('footer');
    }

    /**
     * Read the manifest file that can be found at the root of the theme's asset folder.
     *
     * @return object|null null if the file does not exist or an object representing the manifest data
     */
    protected function loadAssetsManifest($manifestPath)
    {
        if (file_exists($manifestPath))
        {
            return json_decode(file_get_contents($manifestPath), true);
        }

        return null;
    }

    /**
     * Print the assets we required
     *
     * @param string $location The location where we are printing the assets (header|footer)
     */
    protected function printAssets($location)
    {
        if ( !empty($this->requiredAssets))
        {
            foreach ($this->requiredAssets as $handle => $desc)
            {
                if ($desc['location'] != $location) continue;

                if ($desc['type'] == 'style')
                {
                    wp_print_styles($handle);
                }
                else if ($desc['type'] == 'script')
                {
                    wp_print_scripts($handle);
                }
            }
        }
    }

    /**
     * Get the version of an asset
     *
     * @param string $handle       The asset identifier when you'll call requireStyle later on
     * @param string $manifestId   The manifest key to look for. If you leave this empty, we will look for the handle in the manifest.
     * @param string $forceVersion A version number to use (if empty, we will try to get the version from the manifest or fallback to the plugin version number)
     *
     * @return string A version number
     */
    protected function getAssetVersion($handle, $manifestId, $forceVersion)
    {
        if ( !empty($forceVersion))
        {
            return $forceVersion;
        }
        else if ($this->manifest != null)
        {
            if ( !empty($manifestId) && isset($this->manifest[$manifestId]))
            {
                return $this->manifest[$manifestId];
            }
            else if (isset($this->manifest[$handle]))
            {
                return $this->manifest[$handle];
            }
        }

        return $this->defaultAssetVersion;
    }

}