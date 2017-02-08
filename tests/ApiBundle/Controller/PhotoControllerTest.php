<?php

namespace Tests\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoControllerTest extends WebTestCase
{

    /**
     * Base rules for test methods
     *
     * @param $client
     * @param $crawler
     */
    public function basicRulesTest($client, $crawler)
    {
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert that the response status code is 200
        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Create temp tag
     *
     * @return integer
     */
    public function createTag()
    {
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('POST', '/api/v1/tags', ['title' => 'Test_' . rand()]);
        $resultRequest = $clientPrep->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);

        return $resultRequestJson['id'];
    }

    /**
     * Delete tag
     *
     * @param $tagId
     */
    public function delTag($tagId)
    {
        $clientDelTag = static::createClient();
        $crawler = $clientDelTag->request('DELETE', '/api/v1/tags/' . $tagId);
    }

    /**
     * Create image on server
     *
     * @return string
     */
    public function preparingPhoto()
    {
        $testImagePath = __DIR__ . '/../../../web/images/';
        $testImage = 'test.jpg';
        $testImageFullPath = $testImagePath . $testImage;

        if (!file_exists($testImageFullPath)) {
            var_dump('Test image is not reachable');
            var_dump($testImageFullPath);
        }
        $newName = rand() . ".jpg";
        $newTestImage = $testImagePath . $newName;
        exec("cp $testImageFullPath $newTestImage");

        return [
            'path' => $testImagePath,
            'name' => $newName,
        ];
    }

    /**
     * Create photo in DB with tags
     *
     * @param $tempImage
     * @param $tags
     * @return integer
     */
    public function createPhoto($tempImage, $tags)
    {

        $photo = new UploadedFile(
            $tempImage['path'] . $tempImage['name'],
            $tempImage['name'],
            'image/jpeg',
            123
        );
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/v1/photos',
            ['tags' => $tags],
            ['file' => $photo]
        );

        $resultRequest = $client->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);

        return $resultRequestJson['id'];
    }

    /**
     * Delete photo in DB and file on server
     *
     * @param $photoId
     */
    public function delPhoto($photoId)
    {
        $clientDelPhoto = static::createClient();
        $clientDelPhoto->request('DELETE', '/api/v1/photos/' . $photoId);
    }

    /**
     * Delete photo in DB and file on server
     *
     * @param $photoId int
     * @param $tagId int
     */
    public function delPhotoTag($photoId, $tagId)
    {
        $clientPrep = static::createClient();
        $clientPrep->request('DELETE', '/api/v1/photos/' . $photoId . '/tags/' . $tagId);
    }

    /**
     * GET /api/v1/photos
     * Info: get list all photo with no pagination
     */
    public function testGetPhotos()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/photos');

        $this->basicRulesTest($client, $crawler);
    }

    /**
     * POST /api/v1/photos
     * Info: add new photo. In optional tags id for new photo
     */
    public function testNewPhoto()
    {
        /**
         * Generate temp tags.
         * Separated by comma
         */
        $tags = $this->createTag() . ',' . $this->createTag();
        /**
         * Image with path
         */
        $tempImage = $this->preparingPhoto();
        /**
         * Testing
         */
        $photo = new UploadedFile(
            $tempImage['path'] . $tempImage['name'],
            $tempImage['name'],
            'image/jpeg',
            123
        );
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/v1/photos',
            ['tags' => $tags],
            ['file' => $photo]
        );
        $this->basicRulesTest($client, $crawler);

        /**
         * Delete PhotoTag
         */
        $resultRequest = $client->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        $photoId = $resultRequestJson['id'];

        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Delete photo
         */
        $this->delPhoto($photoId);
    }

    /**
     * DELETE /api/v1/photos/{photoId}
     * Info: delete photo
     */
    public function testDelPhoto()
    {
        $tags = $this->createTag() . ',' . $this->createTag(); //Generate temp tags.
        $tempImage = $this->preparingPhoto(); // Image with path
        $photoId = $this->createPhoto($tempImage, $tags); //Create photo
        /**
         * Delete PhotoTag
         */
        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('DELETE', '/api/v1/photos/' . $photoId);

        $this->basicRulesTest($client, $crawler);
    }

    /**
     * GET /api/v1/photos/{id}
     * Info: get photo by id
     */
    public function testGetPhotoBy()
    {
        $tags = $this->createTag() . ',' . $this->createTag(); // Generate temp tags.
        $tempImage = $this->preparingPhoto(); // Image with path
        $photoId = $this->createPhoto($tempImage, $tags); //Create photo

        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/photos/' . $photoId);

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete PhotoTag
         */
        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Delete photo
         */
        $this->delPhoto($photoId);
    }


    /**
     * GET /api/v1/photos/paginated/{limit}/{page}
     * Info: get all photo with pagination
     */
    public function testPhotoPagination()
    {
        $tags = $this->createTag() . ',' . $this->createTag(); // Generate temp tags.
        $tempImage = $this->preparingPhoto(); // Image with path
        $photoId = $this->createPhoto($tempImage, $tags); //Create photo

        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/photos/paginated/2/1');

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete PhotoTag
         */
        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Delete photo
         */
        $this->delPhoto($photoId);
    }

    /**
     * POST /api/v1/photos/{photoId}/addtags
     * Info: add tags for photo
     */
    public function testPhotosAddtags()
    {
        $tags = $this->createTag() . ',' . $this->createTag(); // Generate temp tags.
        $tempImage = $this->preparingPhoto(); // Image with path
        $photoId = $this->createPhoto($tempImage, $tags); //Create photo

        $newTags = $this->createTag() . ',' . $this->createTag(); // Generate temp tags.
        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/v1/photos/' . $photoId . '/addtags', ['tags' => $newTags]);

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete PhotoTag
         */
        $allTags = $tags . ',' . $newTags;
        $tagsArr = explode(',', $allTags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Delete photo
         */
        $this->delPhoto($photoId);
    }


    /**
     * DELETE /api/v1/photos/{photoId}/tags/{tagId}
     * Info: delete tag from photo
     */
    public function testPhotosDeleteTags()
    {
        $tags = $this->createTag() . ',' . $this->createTag(); // Generate temp tags.
        $tempImage = $this->preparingPhoto(); // Image with path
        $photoId = $this->createPhoto($tempImage, $tags); //Create photo

        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('DELETE', '/api/v1/photos/' . $photoId . '/tags/' . $tags);

        $this->basicRulesTest($client, $crawler);
        /**
         * Delete PhotoTag
         */
        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value) {
            $this->delPhotoTag($photoId, $value); // Delete PhotoTag
            $this->delTag($value); //Del tags
        }
        /**
         * Delete photo
         */
        $this->delPhoto($photoId);
    }


}
