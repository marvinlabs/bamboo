<?php
/**
 * Created by PhpStorm.
 * User: vprat
 * Date: 21/05/2015
 * Time: 09:36
 */

namespace Bamboo\Assets;

/**
 * Interface AssetsRegistrationDelegate
 * @package Bamboo\Assets
 *
 *          This will be used at the right time by the asset manager.
 */
interface AssetsRegistrationDelegate
{

    /**
     * Perform registration of all assets which may be required by your plugin.
     *
     * @param AssetsManager $assetManager The asset manager to do the heavy lifting
     */
    function registerAssets($assetManager);

}