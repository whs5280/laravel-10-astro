<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class AstroTest extends TestCase
{
    /**
     * phpunit --filter testGatherCase
     * A basic unit test example.
     */
    public function testGatherCase(): void
    {
        $url = 'https://www.1212.com/luck/';

        // 绕过证书
        $client = new Client(['verify' => false]);

        $response = $client->get($url, [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
            'Accept-Encoding' => 'gzip, deflate, br'
        ]);

        $statusCode = (string) $response->getStatusCode();

        $this->assertTrue($statusCode === '200');
    }
}
