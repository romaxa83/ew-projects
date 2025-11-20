<?php

namespace App\Helpers;

use PNGMetadata\PNGMetadata;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageExifData
{
    private const PNG = 'png';

    private $metaData;

    private $useTestData;

    public function __construct(UploadedFile $file, bool $useTestData = false)
    {
        // используется для тестирования
        $this->useTestData = $useTestData;

        $this->exifData($file);
    }

    private function isPng(UploadedFile $file)
    {
        $ext = explode('/', $file->getMimeType());
        return last($ext) == self::PNG;
    }

    public function exifData($file)
    {
        try{
            if($this->isPng($file)){
                $this->useTestData
                    ? $this->metaData = $this->dataForTest()
                    : $this->metaData = PNGMetadata::extract($file);

            } else {
                $this->useTestData
                    ? $this->metaData = $this->dataForTest()
                    : $this->metaData = exif_read_data($file);
            }
        } catch (\Exception $exception) {
            \Log::notice('NOT FETCH METADATA file ');
            \Log::notice($exception->getMessage());
            $this->metaData = null;
        }
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function getLat()
    {
        if($this->metaData){
            if(isset($this->metaData["GPSLatitude"]) && isset($this->metaData['GPSLatitudeRef'])){
                return $this->getGps($this->metaData["GPSLatitude"], $this->metaData['GPSLatitudeRef']);
            }
        }

        return null;
    }

    public function getLon()
    {
        if($this->metaData){
            if(isset($this->metaData["GPSLongitude"]) && isset($this->metaData['GPSLongitudeRef'])){
                return $this->getGps($this->metaData["GPSLongitude"], $this->metaData['GPSLongitudeRef']);
            }
        }

        return null;
    }

    public function getDateCreatePhoto()
    {
        if($this->metaData){
            if(isset($this->metaData["DateTimeOriginal"])){
                return $this->metaData["DateTimeOriginal"];
            }
        }

        return null;
    }

    private function getGps($exifCoord, $hemi) {

        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

    }

    private function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 0)
            return 0;
        if (count($parts) == 1)
            return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }

    private function dataForTest()
    {
        return array (
            'FileName' => 'phpUiDyO3',
            'FileDateTime' => 1613463467,
            'FileSize' => 3623135,
            'FileType' => 2,
            'MimeType' => 'image/jpeg',
            'SectionsFound' => 'ANY_TAG, IFD0, THUMBNAIL, EXIF, GPS, INTEROP',
            'COMPUTED' =>
                array (
                    'html' => 'width="4128" height="3096"',
                    'Height' => 3096,
                    'Width' => 4128,
                    'IsColor' => 1,
                    'ByteOrderMotorola' => 0,
                    'ApertureFNumber' => 'f/1.9',
                    'UserComment' => NULL,
                    'UserCommentEncoding' => 'UNDEFINED',
                    'Thumbnail.FileType' => 2,
                    'Thumbnail.MimeType' => 'image/jpeg',
                    'Thumbnail.Height' => 384,
                    'Thumbnail.Width' => 512,
                ),
            'ImageWidth' => 4128,
            'ImageLength' => 3096,
            'Make' => 'samsung',
            'Model' => 'SM-J600F',
            'Orientation' => 6,
            'XResolution' => '72/1',
            'YResolution' => '72/1',
            'ResolutionUnit' => 2,
            'Software' => 'J600FXXS7BTB5',
            'DateTime' => '2021:02:16 10:17:19',
            'YCbCrPositioning' => 1,
            'Exif_IFD_Pointer' => 238,
            'GPS_IFD_Pointer' => 974,
            'THUMBNAIL' =>
                array (
                    'ImageWidth' => 512,
                    'ImageLength' => 384,
                    'Compression' => 6,
                    'Orientation' => 6,
                    'XResolution' => '72/1',
                    'YResolution' => '72/1',
                    'ResolutionUnit' => 2,
                    'JPEGInterchangeFormat' => 1310,
                    'JPEGInterchangeFormatLength' => 11856,
                ),
            'ExposureTime' => '1/50',
            'FNumber' => '19/10',
            'ExposureProgram' => 2,
            'ISOSpeedRatings' => 100,
            'ExifVersion' => '0220',
            'DateTimeOriginal' => '2021:02:16 10:17:19',
            'DateTimeDigitized' => '2021:02:16 10:17:19',
            'ComponentsConfiguration' => '' . "\0" . '',
            'ShutterSpeedValue' => '564/100',
            'ApertureValue' => '185/100',
            'BrightnessValue' => '209/100',
            'ExposureBiasValue' => '0/10',
            'MaxApertureValue' => '185/100',
            'MeteringMode' => 2,
            'Flash' => 0,
            'FocalLength' => '360/100',
            'MakerNote' => '',
            'UserComment' => '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '' . "\0" . '',
            'SubSecTime' => '0468',
            'SubSecTimeOriginal' => '0468',
            'SubSecTimeDigitized' => '0468',
            'FlashPixVersion' => '0100',
            'ColorSpace' => 1,
            'ExifImageWidth' => 4128,
            'ExifImageLength' => 3096,
            'InteroperabilityOffset' => 944,
            'SceneType' => '' . "\0" . '' . "\0" . '' . "\0" . '',
            'ExposureMode' => 0,
            'WhiteBalance' => 0,
            'DigitalZoomRatio' => '0/0',
            'FocalLengthIn35mmFilm' => 27,
            'SceneCaptureType' => 0,
            'Contrast' => 0,
            'Saturation' => 0,
            'Sharpness' => 0,
            'ImageUniqueID' => 'Y13LLLA00NM Y13LLMK01NA',
            'GPSVersion' => '' . "\0" . '' . "\0" . '',
            'GPSLatitudeRef' => 'N',
            'GPSLatitude' =>
                array (
                    0 => '46/1',
                    1 => '38/1',
                    2 => '14/1',
                ),
            'GPSLongitudeRef' => 'E',
            'GPSLongitude' =>
                array (
                    0 => '32/1',
                    1 => '36/1',
                    2 => '45/1',
                ),
            'GPSAltitudeRef' => '' . "\0" . '',
            'GPSAltitude' => '75/1',
            'GPSTimeStamp' =>
                array (
                    0 => '8/1',
                    1 => '17/1',
                    2 => '17/1',
                ),
            'GPSDateStamp' => '2021:02:16',
            'InterOperabilityIndex' => 'R98',
            'InterOperabilityVersion' => '0100',
        );
    }
}
