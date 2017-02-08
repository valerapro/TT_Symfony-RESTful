<?php

namespace Tests\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TagControllerTest extends WebTestCase
{

    /**
     * Base rules for test methods
     *
     * @param $client
     * @param $crawler
     */
    public function basicRulesTest($client, $crawler){
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
     * GET /api/v1/tags
     * Info: get all tags
     */
    public function testGetTags()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/tags');

        $this->basicRulesTest($client, $crawler);
    }

    /**
     * POST /api/v1/tags
     * Info: add new tag
     */
    public function testPostTags()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/v1/tags', ['title' => 'Test_'.rand()]);

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete new tag
         */
        $resultRequest = $client->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('DELETE', '/api/v1/tags/' . $resultRequestJson['id']);

    }

    /**
     * GET /api/v1/tags/{id}
     * Info: get tag by id
     */
    public function testGetTagBy()
    {
        /**
         * Create temp tag
         */
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('POST', '/api/v1/tags', ['title' => 'Test_'.rand()]);
        $resultRequest = $clientPrep->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/tags/' . $resultRequestJson['id']);

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete new tag
         */
        $clientPrep->request('DELETE', '/api/v1/tags/' . $resultRequestJson['id']);
    }

    /**
     * PUT /api/v1/tags/{id}/titles/{title}
     * Info: edit tag title
     */
    public function testPutTag()
    {
        /**
         * Create temp tag
         */
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('POST', '/api/v1/tags', ['title' => 'Test_'.rand()]);
        $resultRequest = $clientPrep->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        /**
         * Testing
         */
        $client = static::createClient();
        $url = '/api/v1/tags/' . $resultRequestJson['id'] . '/titles/' . 'Test_'.rand();
        $crawler = $client->request('PUT', $url);

        $this->basicRulesTest($client, $crawler);

        /**
         * Delete temp tag
         */
        $clientPrep->request('DELETE', '/api/v1/tags/' . $resultRequestJson['id']);
    }

    /**
     * GET /api/v1/tags/{tagId}/photos
     * Info: get all photos by tagId
     */
    public function testGetTagPhotoBy()
    {
        /**
         * Create temp tag
         */
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('POST', '/api/v1/tags', ['title' => 'Test_'.rand()]);
        $resultRequest = $clientPrep->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/tags/' . $resultRequestJson['id'] . '/photos');

        $this->basicRulesTest($client, $crawler);
        /**
         * Delete temp tag
         */
        $clientPrep->request('DELETE', '/api/v1/tags/' . $resultRequestJson['id']);
    }

    /**
     * DELETE /api/v1/tags/{id}
     * Info: delete tag
     */
    public function testDelTag()
    {
        /**
         * Create temp tag
         */
        $clientPrep = static::createClient();
        $crawlerPred = $clientPrep->request('POST', '/api/v1/tags', ['title' => 'Test_'.rand()]);
        $resultRequest = $clientPrep->getResponse()->getContent();
        $resultRequestJson = json_decode($resultRequest, true);
        /**
         * Testing
         */
        $client = static::createClient();
        $crawler = $client->request('DELETE', '/api/v1/tags/' . $resultRequestJson['id']);

        $this->basicRulesTest($client, $crawler);
    }

}
