<?php

namespace Bamboo\Templates;

/**
 * Class TemplateEngine
 * @package Showcase\Core\Templates
 */
class TemplateEngine
{
    /** @var bool true to output debugging info */
    private $enableDebug = false;

    /** @var string The slug of the plugin (usually its folder name) */
    private $pluginSlug = 'define-me';

    /** @var array Default root folders for the template files */
    private $defaultRoots;

    /**
     * Constructor
     *
     * @param string $pluginSlug
     * @param bool   $enableDebug
     */
    function __construct($pluginSlug, $enableDebug = false)
    {
        $this->pluginSlug = $pluginSlug;
        $this->enableDebug = $enableDebug;

        $this->defaultRoots = array(
            untrailingslashit(WP_CONTENT_DIR) . '/' . $this->pluginSlug,
            untrailingslashit(get_stylesheet_directory()) . '/' . $this->pluginSlug,
            untrailingslashit(get_stylesheet_directory())
        );
    }


    /**
     * @param boolean $enableDebug
     */
    public function enableDebug($enableDebug = true)
    {
        $this->enableDebug = $enableDebug;
    }

    /**
     * Checks all templates overridden by the user to see if they need an update
     *
     * @param array [string => string] $dirsToScan directories to scan
     *
     * @return array An array containing all the outdated template files found
     */
    public function checkTemplates($dirsToScan)
    {
        $outdatedTemplates = array();

        foreach ($dirsToScan as $dir => $title)
        {
            $templateFinder = new TemplateFinder($this);
            $templateFinder->scanDirectory($dir);

            $tmp = $templateFinder->getOutdatedTemplates();
            if ( !empty($tmp))
            {
                $outdatedTemplates[$title] = $tmp;
            }

            unset($templateFinder);
        }

        return $outdatedTemplates;
    }

    /**
     * Takes a default template file as parameter. It will look in the theme's directory to see if the user has
     * customized the template. If so, it returns the path to the customized file. Else, it returns the default
     * passed as parameter.
     *
     * Order of preference is:
     * 1. user-directory/filename
     * 2. user-directory/fallback-filename
     * 3. default-directory/filename
     * 4. default-directory/fallback-filename
     *
     * @param string|array $templateRoots
     * @param string|array $fileNames
     * @param string       $relativePath
     *
     * @return string
     */
    public function getTemplateFilePath($templateRoots, $fileNames, $relativePath = '')
    {
        $enableDebug = $this->enableDebug && !is_admin();

        // Build the possible locations list
        if ( !is_array($templateRoots)) $templateRoots = array($templateRoots);

        $possibleLocations = array_merge($this->defaultRoots, $templateRoots);
        $possibleLocations = apply_filters($this->pluginSlug . '/ui/template-directories', $possibleLocations);

        // Handle cas when only a single filename is given
        if ( !is_array($fileNames)) $fileNames = array($fileNames);

        // Make sure we have trailing slashes
        if ( !empty($relativePath)) $relativePath = trailingslashit($relativePath);

        // For each location, try to look for a file from the stack
        foreach ($possibleLocations as $dir)
        {
            $dir = trailingslashit($dir);
            foreach ($fileNames as $filename)
            {
                $path = $dir . $relativePath . $filename;
                if (file_exists($path))
                {
                    if ($enableDebug) $this->printTemplateDebugInfo($fileNames, $possibleLocations, $filename, $dir . $relativePath);

                    return $path;
                }
            }
        }

        if ($enableDebug) $this->printTemplateDebugInfo($fileNames, $possibleLocations, null, $relativePath);

        return '';
    }

    /**
     * Output some debugging information about a template we have included (or tried to)
     *
     * @param array  $fileNames         The fileNames which were provided
     * @param array  $possibleLocations The locations we have been told to explore
     * @param string $filename          File that got chosen
     * @param string $path              The path where the file was found
     */
    private function printTemplateDebugInfo($fileNames, $possibleLocations, $filename = null, $path = '')
    {
        echo "\n<!-- " . strtoupper($this->pluginSlug) . " DEBUG - TEMPLATE REQUESTED \n";
        echo "       ## FOUND     : " . (($filename == null) ? 'NO' : 'YES') . "\n";

        if ( !empty($filename))
        {
            echo "       ## PICKED    : $filename \n";
        }

        echo "       ## FROM STACK: \n";
        foreach ($fileNames as $f)
        {
            echo "           - " . $f . "\n";
        }

        if ( !empty($path))
        {
            echo "       ## IN PATH   : $path \n";
        }

        echo "       ## FROM ROOTS: \n";
        foreach ($possibleLocations as $loc)
        {
            $to_remove = dirname(WP_CONTENT_DIR);
            $loc = str_replace($to_remove, '', $loc);
            echo "           - " . $loc . "\n";
        }

        echo "-->\n";
    }
}