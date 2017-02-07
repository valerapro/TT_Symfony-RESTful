<?php

namespace ApiBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;


class FilesManager
{
    public static $ALLOWED_MIME_TYPES = [
        "image/png",
        "image/jpeg",
        "image/jpg",
    ];

    private $fileDir;

    public function __construct($fileDir)
    {
        $this->fileDir = $fileDir;
    }

    /**
     * @return array
     */
    public static function getALLOWEDMIMETYPES()
    {
        $allowedTypes = '';
        foreach (self::$ALLOWED_MIME_TYPES as $item) {
            $allowedTypes .= $item . ', ';
        }
        return $allowedTypes;
    }
    /**
     * Validate file is it image
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function fileValidate(UploadedFile $file)
    {
        $fileType = false;
        foreach (self::$ALLOWED_MIME_TYPES as $value){
            if($value === $file->getMimeType()){
                $fileType = true;
            }
        }
        return $fileType;
    }

    /**
     * Load image on server and rename it
     *
     * @param UploadedFile $file
     * @return array
     */
    public function uploadFile(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->getClientOriginalExtension();
        $file->move($this->fileDir, $fileName);
        return $fileName;
    }

    /**
     * Delete file in serer
     *
     * @param $fileName
     * @return mixed
     */
    public function removeFile($fileName)
    {
        $removeFile = false;
        if (file_exists($this->fileDir.$fileName)) {
            unlink($this->fileDir.$fileName);
            $removeFile = true;
        }

        if (file_exists($this->fileDir.$fileName)) {
            $removeFile = false;
        }
        return $removeFile;
    }
}
