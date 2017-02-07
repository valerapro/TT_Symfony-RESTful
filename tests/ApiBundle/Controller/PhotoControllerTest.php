<?php

namespace Tests\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoControllerTest extends WebTestCase
{
    /**
     * GET /api/v1/photos
     * Info: get list all photo with no pagination
     */
    public function testGetPhotos()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/photos');

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
     * POST /api/v1/photos
     * Info: add new photo. In optional tags id for new photo
     */
    public function testNewPhoto()
    {
        $testImagePath = __DIR__ . '/../../../web/images/' ;
        $testImage = 'test.jpg' ;
        $testImageFullPath = $testImagePath . $testImage;

        if (!file_exists($testImageFullPath)) {
            var_dump('Test image is not reachable');
            var_dump($testImageFullPath);
        }
        $newName = rand(). ".jpg";
        $newTestImage = $testImagePath . $newName;
        exec("cp $testImageFullPath $newTestImage");
        $photo = new UploadedFile(
            $newTestImage,
            'test.jpg',
            'image/jpeg',
            123
        );

        $client = static::createClient();
        $client->request('POST', '/api/v1/photos',
            ['tags' =>  ''],
            ['file' => $photo]
            );

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



}
