<?php

namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;

class VideoUploadTest extends BaseVideoTestCase
{

    /**
     *
     * @group Video
     * @return void
     */
    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4'),
            ]
        );

        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function () {
            throw new \Exception();
        });
        $hasError = false;

        try {
            Video::create(
                $this->data + [
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg')
                ]
            );
        } catch (\Exception $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        $thumbFile = UploadedFile::fake()->image('thumb.jpg');
        $videoFile = UploadedFile::fake()->create('video.mp4');
        $video->update($this->data + [
            'thumb_file' => $thumbFile,
            'video_file' => $videoFile,
        ]);
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->image('video.mp4');
        $video->update($this->data + [
            'video_file' => $newVideoFile,
        ]);
        \Storage::assertExists("{$video->id}/{$thumbFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertMissing("{$videoFile->hashName()}");
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function () {
            throw new \Exception();
        });
        $hasError = false;

        try {
            $video->update(
                $this->data + [
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg')
                ]
            );
        } catch (\Exception $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testFileUrlsWithGcsDriver()
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }
        $video = factory(Video::class)->create($fileFields);
        $baseUrl = config('filesystems.disks.gcs.storage_api_uri');
        \Config::set('filesystems.default', 'gcs');
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testFileUrlsIfNullWhenFieldsAreNull()
    {
        $video = factory(Video::class)->create();
        foreach (Video::$fileFields as $field) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertNull($fileUrl);
        }
    }
}
