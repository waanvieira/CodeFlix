<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Image;

trait UploadTrait
{
    protected abstract function uploadDir();

    public $oldFiles = [];

    public static function bootUploadFiles()
    {
        static::updating(function (Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            });
            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $filesFiltered);
        });
    }

    protected function checkDirectory($fullPath)
    {
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777);
        }
    }

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
    }

    /**
     * Method delete file
     *     
     * @param string $file
     * @return mix
     */
    public function deleteFile($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;
        $fileDelete = "{$this->uploadDir()}/{$fileName}";
        if (Storage::exists($fileDelete)) {
            return Storage::delete($fileDelete);
        }

        return false;
    }

    /**
     * Method delete file
     *     
     * @param array $file
     * @return mix
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * Return image
     *
     * @param string $img
     * @param integer $width
     * @param integer $height
     * @return mix
     */
    public function get($img, int $width = null, int $height = null)
    {
        $url = Storage::get($img);

        if (!$width && !$height) {
            $image = Image::cache(function ($image) use ($url) {
                $image->make($url);
            });
        } else {
            $image = Image::cache(function ($image) use ($url, $width, $height) {
                if ($width && $height) {
                    $image->make($url)->resize($width, $height);
                } else {
                    $image->make($url)
                        ->resize($width, $height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                }
            });
        }

        if (isset($image)) {
            return Response::make($image, 200, ['Content-Type' => 'image'])
                ->setMaxAge(864000)
                ->setPublic();
        }

        return false;
    }

    /**
     * Upload file
     *
     * @param  UploadedFile $file     
     * @param Object $obj
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
        // $this->handleUpload($file, $obj);
    }

    /**
     * Upload files
     *
     * @param  array $files     
     * @param Object $obj
     * @return void
     */
    public function uploadFiles(array $files, $obj = null)
    {
        foreach ($files as $file) {
            $this->uploadFile($file, $obj);
        }
    }

    public function getFile($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;
        return "{$this->uploadDir()}/{$fileName}";
    }

    // protected function uploadFile($uploadFile, $path)
    // {
    //     $fullPath = $this->pathNameStorage($path);
    //     $newFileName = md5(uniqid(now())) . strrchr($uploadFile->getClientOriginalName(), '.');
    //     $request['img'] = $uploadFile;
    //     $this->checkDirectory($fullPath);
    //     $imgResized = $this->resize($request);
    //     $imgResized->save($fullPath . '/' . $newFileName);
    //     return $this->pathSave($path) . '/' . $newFileName;
    // }

    /**
     * Method to resize and upload file to storage
     */
    public function resize($request)
    {
        $img = Image::make($request['img']);
        $width = isset($request['widht']) ? $$request['widht'] : 1000;
        $height = isset($request['height']) ? $request['height'] : null;
        return $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    /**
     * @param $path - File path for local storage
     * @return bool - Return if file exists
     */
    protected function checkFileExists($file, $path = null)
    {
        return asset("{$this->uploadDir()}/{$file}");
    }

    /**
     * Generate global path
     *
     * @param string $path
     * @return mix
     */
    protected function pathNameStorage($path)
    {
        $newPath = "app/public/uploads/$path";
        return storage_path($newPath);
    }

    public static function extractFiles(array &$attributes = [])
    {
        $files = [];
        foreach (self::$fileFields as $file) {
            if (isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile) {
                $files[] = $attributes[$file];
                $attributes[$file] = $attributes[$file]->hashName();
            }
        }
        return $files;
    }

    /**
     * Generate global save db
     *
     * @param string $path
     * @return mix
     */
    protected function pathSave($path)
    {
        return "uploads/$path";
    }

    protected function handleUpload($file, $obj)
    {
        if (isset($file)) {
            $request['file'] = $file->hashName();
            if ($obj->file()->first()) {
                $this->deleteFile($obj->file()->first()->file);
                $obj->file()->update(['file' => $request['file']]);
            } else {
                $obj->file()->create($request);
            }
        }
    }

    protected function getFileUrl($filename)
    {
        return \Storage::url($this->getFile($filename));
    }
}
