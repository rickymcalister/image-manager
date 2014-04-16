<?php
namespace Bravo3\ImageManager\Services;

use Bravo3\Cache\PoolInterface;
use Bravo3\ImageManager\Entities\Image;
use Bravo3\ImageManager\Exceptions\ImageManagerException;
use Bravo3\ImageManager\Exceptions\IoException;
use Gaufrette\Filesystem;
use Intervention\Image\Image as InterventionImage;

/**
 * An image manager for cloud computing
 *
 * The concept behind this is to store your images on a cloud storage device (such as S3) and to create variations
 * either ahead of time or in real-time, keeping them accessible via a CDN through your cloud storage.
 *
 * It should also be possible NOT to have your image store publicly accessible and instead retrieve the binary image
 * data for alternative delivery.
 */
class ImageManager
{

    /**
     * A filesystem to store all images on
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * A high-speed caching pool to store meta-data on stored objects
     *
     * This cache pool is used to remember if objects exist without the need to hit the image pool. If you are using a
     * filesystem image pool then there is little use for a cache pool, however If you're using an S3 or similar, then
     * you should consider using something like Doctrine, PDO or Redis here.
     *
     * @var PoolInterface
     */
    protected $cache_pool;


    function __construct(Filesystem $filesystem, PoolInterface $cache_pool = null)
    {
        $this->filesystem = $filesystem;
        $this->cache_pool = $cache_pool;
    }


    /**
     * Push a local image/variation to the remote
     *
     * If it is not hydrated this function will throw an exception
     *
     * @param Image $image
     */
    public function push(Image $image)
    {

    }

    /**
     * Get an image/variation from the remote
     *
     * If this image is a variation that does not exist, an attempt will be made to retrieve the parent first
     * then create the variation. The variation should be optionally pushed if it's Image::isPersistent() function
     * returns false.
     *
     * @param Image $image
     * @return Image
     */
    public function pull(Image $image)
    {

        return $image;
    }

    /**
     * Delete all copies of the image and flush it from memory
     *
     * TODO: delete variations as well
     *
     * @param Image $image
     */
    public function delete(Image $image)
    {
        $this->deleteLocal($image);
        $this->deleteRemote($image);
        $image->flush();
    }

    public function deleteRemote(Image $image)
    {

    }

    public function deleteLocal(Image $image)
    {

    }

    /**
     * Save the image to the local filesystem
     *
     * @param Image  $image
     * @param string $filename
     * @param int    $quality
     * @throws ImageManagerException
     */
    public function save(Image $image, $filename, $quality = 90)
    {
        if (!$image->isHydrated()) {
            throw new ImageManagerException("Image is not hydrated");
        }

        $image->getImage()->save($filename, $quality);
    }

    /**
     * Create a new image from a filename and hydrate it
     *
     * @param string $filename
     * @param string $key
     * @return Image
     */
    public function load($filename, $key = null)
    {
        if (!is_readable($filename)) {
            throw new IoException("File not readable: ".$filename);
        }

        $image = new Image($key);
        $image->setImage(InterventionImage::make($filename));

        return $image;
    }

    /**
     * Check if an image exists on the remote
     *
     * This will check the cache pool if one exists, else it will talk to the remote filesystem to check if
     * the image exists.
     *
     * @param Image $image
     * @return boolean
     */
    public function exists(Image $image)
    {
        $key = $image->getKey();

        if (!$key) {
            return false;
        }

        return $this->filesystem->has($key);
    }


    /*
     * REQUIRE
     * -------
     *
     * - A facility to save a new image (from memory)
     * - A facility to delete an image, and all of it's variations
     * - A facility to check if a variation exists [and create it]
     * - A facility to manually create a variation
     * - A facility to retrieve an image/variation (to memory)
     * - A facility to retrieve a URL for an image/variation
     *
     * CONSIDER
     * --------
     *
     * - How do we tackle URL resolution?
     * - Forking Illuminate to provide stream support and manual format saving (instead of auto-detect format on extension)
     *
     */

}
 