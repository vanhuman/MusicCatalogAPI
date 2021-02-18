<?php

namespace Handlers;

use Helpers\Constants;
use PDO;
use Psr\Container\ContainerInterface;

class CleanupHandler
{
    /* @var PDO $db */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('databaseConnection')->getDatabase();
    }

    public function cleanupImages(bool $testRun): array
    {
        $albumsImageLocation = $_SERVER['DOCUMENT_ROOT'] . Constants::$IMAGE_LOCATION;
        $albumsImageBackupLocation = $_SERVER['DOCUMENT_ROOT'] . Constants::$IMAGE_LOCATION . '/backup-cleanup-images/';
        if (!is_dir($albumsImageBackupLocation)) {
            mkdir($albumsImageBackupLocation);
        }
        $albumsImageThumbLocation = $_SERVER['DOCUMENT_ROOT'] . Constants::$IMAGE_THUMB_LOCATION;
        $albumsImageThumbBackupLocation = $_SERVER['DOCUMENT_ROOT'] . Constants::$IMAGE_THUMB_LOCATION . '/backup-cleanup-images/';
        if (!is_dir($albumsImageThumbBackupLocation)) {
            mkdir($albumsImageThumbBackupLocation);
        }

        $query = 'SELECT id, image, image_local, image_thumb, image_thumb_local FROM album WHERE image_local > "" OR image_thumb_local > ""';
        $result = $this->db->query($query);
        $albums = $result->fetchAll();

        $albumsImage = array_map(function($album) {
            return $album['image_local'];
        }, $albums);
        $albumsImageThumb = array_map(function($album) {
            return $album['image_thumb_local'];
        }, $albums);
        $object = [
            'query' => $query,
            'images' => $this->moveOrphanImages($albumsImage, $albumsImageLocation, $albumsImageBackupLocation, $testRun),
            'thumbs' => $this->moveOrphanImages($albumsImageThumb, $albumsImageThumbLocation, $albumsImageThumbBackupLocation, $testRun),
        ];

        return $object;
    }

    private function moveOrphanImages(array $albums, string $imageLocation, string $imageBackupLocation, bool $testRun): array
    {
        $movedImages = [];
        if ($handle = opendir($imageLocation)) {
            while (false !== ($entry = readdir($handle))) {
                if (!is_dir($imageLocation . $entry) && $entry != '.' && $entry != '..' && array_search($entry, $albums) === false) {
                    $movedImages[] = $entry;
                    if (!$testRun) {
                        rename(
                            $imageLocation . $entry,
                            $imageBackupLocation . $entry
                        );
                    }
                }
            }
            closedir($handle);
        }
        return $movedImages;
    }
}
