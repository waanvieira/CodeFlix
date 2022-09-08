<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Image;

trait UploadTrait
{
    protected abstract function uploadDir();

    protected function checkDirectory($fullPath)
    {
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777);
        }
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
    public function uploadFiles(array $files, $obj)
    {
        foreach ($files as $file) {
            $this->uploadFile($file, $obj);
        }
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
        return asset("uploads/{$file}");
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
}
