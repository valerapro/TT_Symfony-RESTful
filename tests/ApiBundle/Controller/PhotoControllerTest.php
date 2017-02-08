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
        $clientPrep = static::createClient();
        $clientPrep->request('DELETE', '/api/v1/tags/' . $tagId);
    }

    /**
     * Create image on server
     *
     * @return string
     */
    public function createPhoto(){
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

        return $newTestImage;
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
        $newTestImage = $this->createPhoto();
        /**
         * Testing
         */
        $photo = new UploadedFile(
            $newTestImage,
            'test.jpg',
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
         * Del tags
         */
        $tagsArr = explode(',', $tags);
        foreach ($tagsArr as $value){
            $this->delTag($value);
        }
    }


}
