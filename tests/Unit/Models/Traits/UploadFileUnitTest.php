<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Stubs\Models\UploadFilesStub;

class UploadFileUnitTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        $this->obj->id = 123;
    }

    /**
     * @group UploadFile
     *
     * @return void
     */
    public function testUploadFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file, $this->obj);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    /**
     * @group UploadFile
     *
     * @return void
     */
    public function testUploadFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->obj->uploadFiles([$file1, $file2], $this->obj);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    /**
     * @group UploadFile
     *
     * @return void
     */
    public function testDeleteFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");
        $res = $this->obj->deleteFile('12345799.mp4');
        $this->assertFalse($res);
    }

    /**
     * @group UploadFile
     *
     * @return void
     */
    public function testDeleteFiles()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->obj->uploadFiles([$file, $file2], $this->obj);
        $this->obj->deleteFiles([$file->hashName(), $file2]);
        \Storage::assertMissing("1/{$file->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }
}
