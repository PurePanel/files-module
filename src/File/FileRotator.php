<?php namespace Anomaly\FilesModule\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileRotator
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\FilesModule\File
 */
class FileRotator
{

    /**
     * Rotate the uploaded file appropriately.
     *
     * @param UploadedFile $file
     * @return UploadedFile
     */
    public function rotate(UploadedFile $file)
    {
        if (!exif_imagetype($file->getRealPath()) || !$exif = exif_read_data($file->getRealPath())) {
            return $file;
        }

        if (($orientation = array_get($exif, 'Orientation')) && $orientation > 1) {
            $file = $this->orientate($file, $orientation);
        }

        return $file;
    }

    /**
     * Orientate the image.
     *
     * @param UploadedFile $file
     * @param              $orientation
     * @return UploadedFile
     */
    protected function orientate(UploadedFile $file, $orientation)
    {
        $image = imagecreatefromjpeg($file->getRealPath());

        switch ($orientation) {
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }

        imagejpeg($image, $file->getRealPath(), 90);

        return new UploadedFile(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $file->getSize()
        );
    }
}
