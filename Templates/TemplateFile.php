<?php

namespace Bamboo\Templates;

/**
 * Information about a template file
 * @package Showcase\Core\Templates
 */
class TemplateFile
{
    /** @var string Regular expression to find the template version within the template file */
    private static $VERSION_REGEX = '/Template version:\s*(\d+\.\d+.\d+)/';

    private $name = '';
    private $originalPath = '';
    private $originalVersion = '';
    private $currentPath = '';
    private $currentVersion = '';
    private $isOutdated = false;

    /**
     * Constructor
     * @param string $originalPath The path of the template file as provided by the plugin
     * @param string $currentPath The path of the template file as overridden by the user
     */
    public function __construct($originalPath, $currentPath)
    {
        $this->originalPath = $originalPath;
        $this->currentPath = $currentPath;

        $this->readInformation();
    }

    /**
     * Read all the information about the template file
     */
    private function readInformation()
    {
        // Get original version
        $originalVersion = $this->getTemplateVersion($this->originalPath);
        $this->setOriginalVersion($originalVersion);

        // Get overloaded version number
        $currentVersion = empty($this->currentPath) ? '' : $this->getTemplateVersion($this->currentPath);
        $this->setCurrentVersion($currentVersion);

        // Outdated?
        if (empty($currentVersion) && !empty($originalVersion) && !empty($this->currentPath)) {
            $this->setOutdated(true);
        } else if (empty($currentVersion)) {
            $this->setOutdated(false);
        } else {
            $this->setOutdated(version_compare($originalVersion, $currentVersion, '!='));
        }

        // Add template to our list
        $this->setName(basename($this->originalPath));
    }

    /**
     * Extract the version number from a template file
     * @param $filePath
     * @return string
     */
    private function getTemplateVersion($filePath)
    {
        $input = file_get_contents($filePath, null, null, null, 256);
        if (preg_match(self::$VERSION_REGEX, $input, $matches) > 0) {
            return $matches[1];
        }
        return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * @param string $currentPath
     */
    public function setCurrentPath($currentPath)
    {
        $this->currentPath = $currentPath;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @param string $currentVersion
     */
    public function setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $currentVersion;
    }

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    /**
     * @param string $filePath
     */
    public function setOriginalPath($filePath)
    {
        $this->originalPath = $filePath;
    }

    /**
     * @return boolean
     */
    public function isOutdated()
    {
        return $this->isOutdated;
    }

    /**
     * @param boolean $isOutdated
     */
    public function setOutdated($isOutdated)
    {
        $this->isOutdated = $isOutdated;
    }

    /**
     * @return string
     */
    public function getOriginalVersion()
    {
        return $this->originalVersion;
    }

    /**
     * @param string $originalVersion
     */
    public function setOriginalVersion($originalVersion)
    {
        $this->originalVersion = $originalVersion;
    }
}