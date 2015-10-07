<?php

namespace Bamboo\Templates;

/**
 * Helps to scan a directory to find templates and their current version
 * @package Showcase\Core\Templates
 */
class TemplateFinder
{

    /** @var TemplateEngine */
    private $templateEngine;

    /** @var array[TemplateFile] The templates found when scanning a directory */
    private $templates;

    /**
     * Constructor
     *
*@param TemplateEngine $templateEngine
     */
    public function __construct($templateEngine)
    {
        $this->templateEngine = $templateEngine;
        $this->templates = array();
    }

    /**
     * Recursively scan a directory to find templates in there
     * @param $dir
     */
    public function scanDirectory($dir)
    {
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->scanDirectory($path);
            } else if (false !== strpos($item, '.template.php')) {
                $this->addTemplateFile($path);
            }
        }
    }

    /**
     * Get all outdated templates we found
     * @return array
     */
    public function getAllTemplates()
    {
        return $this->templates;
    }

    /**
     * Get all outdated templates we found
     * @return array
     */
    public function getOutdatedTemplates()
    {
        $out = array();
        foreach ($this->templates as $k => $t) {
            /** @var TemplateFile $t */
            if ($t->isOutdated()) $out[$k] = $t;
        }
        ksort($out);
        return $out;
    }

    /**
     * Get the number of templates we found
     * @return int
     */
    public function getTemplateCount()
    {
        return count($this->templates);
    }

    /**
     * Add a file to the array of found templates
     * @param string $file_path
     */
    private function addTemplateFile($file_path)
    {
        $current_path = $this->templateEngine->getTemplateFilePath(pathinfo($file_path, PATHINFO_DIRNAME), basename($file_path), 'templates');
        $t = new TemplateFile($file_path, $current_path);

        $this->templates[$t->getName()] = $t;
    }
}