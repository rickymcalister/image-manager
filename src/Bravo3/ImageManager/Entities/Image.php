<?php
namespace Bravo3\ImageManager\Entities;

use Bravo3\ImageManager\Enum\ImageFormat;
use Bravo3\ImageManager\Exceptions\BadImageException;
use Bravo3\ImageManager\Exceptions\ImageManagerException;
use Bravo3\ImageManager\Exceptions\IoException;
use Bravo3\ImageManager\Traits\FriendTrait;
use Intervention\Image\Image as InterventionImage;

class Image
{
    use FriendTrait;

    protected $__friends = ['Bravo3\ImageManager\Services\ImageManager'];

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $raw_content_type = null;

    /**
     * @var boolean
     */
    protected $persistent = false;

    function __construct($key = null)
    {
        $this->key = $key;
    }


    /**
     * Flush image data from memory
     */
    public function flush($collect_garbage = true)
    {
        $this->data = null;

        if ($collect_garbage) {
            gc_collect_cycles();
        }
    }

    /**
     * Check the data data for the image type
     *
     * If unknown or no data data is present, null will be returned
     *
     * @return ImageFormat|null
     */
    public function getDataFormat()
    {
        if (!$this->data || strlen($this->data) < 5) {
            return null;
        }

        // JPEG: FF D8
        if ($this->data{0} == 0xff && $this->data{1} == 0xd8) {
            return ImageFormat::JPEG();
        }

        // PNG: 89 50 4E 47
        if ($this->data{0} == 0xff && substr($this->data, 1, 3) == 'PNG') {
            return ImageFormat::PNG();
        }

        // GIF87a: 47 49 46 38 37 61
        // GIF89a: 47 49 46 38 39 61
        if (substr($this->data, 0, 6) == 'GIF87a' || substr($this->data, 0, 6) == 'GIF89a') {
            return ImageFormat::GIF();
        }

        return null;
    }

    /**
     * Set the remote key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Get the remote key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Check if the image is known to exist on the remote
     *
     * @return boolean
     */
    public function isPersistent()
    {
        return $this->persistent;
    }

    /**
     * Check if the image data has been loaded
     *
     * @return bool
     */
    public function isHydrated()
    {
        return $this->data !== null;
    }

    /**
     * Set image data
     *
     * @param string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data       = $data;
        $this->persistent = false;
        return $this;
    }

    /**
     * Get image data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }


}
