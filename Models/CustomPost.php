<?php

namespace Bamboo\Models;

/**
 * A wrapper to augment the WP_Post object.
 */
class CustomPost
{

    /** @var int The custom post ID. */
    public $ID;

    /** @var \WP_Post The actual post object. */
    public $post;

    /**
     * Constructor
     *
     * @param \WP_Post|int $customPost
     * @param boolean     $loadPost If we supply an int as the first argument, shall we load the post object?
     */
    public function __construct($customPost, $loadPost = true)
    {
        if ($customPost instanceof \WP_Post)
        {
            $this->ID = absint($customPost->ID);
            $this->post = $customPost;
        }
        else
        {
            $this->ID = absint($customPost);
            $this->post = null;
            if ($loadPost)
            {
                $this->getPostObject();
            }
        }
    }

    /**
     * __isset function.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        $meta_key = static::$META_PREFIX . $key;

        return metadata_exists('post', $this->ID, $meta_key);
    }

    /**
     * __get function.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $meta_key = static::$META_PREFIX . $key;
        $value = get_post_meta($this->ID, $meta_key, true);

        return $value ? $value : $this->getDefaultMetaValue($key);
    }

    /**
     * Get the default value for a metadata key
     */
    protected function getDefaultMetaValue($key)
    {
        return null;
    }

    /**
     * Get the post data.
     *
     * @return object
     */
    public function getPostObject()
    {
        if ($this->post == null)
        {
            $this->post = get_post($this->ID);
        }

        return $this->post;
    }
}