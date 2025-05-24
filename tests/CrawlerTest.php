<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Tests;

use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Crawler;
use Radowoj\Crawla\Link\Collection;
use Radowoj\Crawla\Link\CollectionInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CrawlerTest extends TestCase
{
    private const TEST_BASE_URL = 'https://example.com';
    private const TEST_LINK_SELECTOR = 'a.test-link';

    private HttpClientInterface $httpClient;
    private Crawler $crawler;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);

        $this->crawler = new Crawler(
            self::TEST_BASE_URL,
            $this->httpClient
        );
    }

    public function testGetLinkSelector(): void
    {
        $this->assertSame('a', $this->crawler->getLinkSelector());
    }

    public function testSetLinkSelector(): void
    {
        $this->crawler->setLinkSelector(self::TEST_LINK_SELECTOR);
        $this->assertSame(self::TEST_LINK_SELECTOR, $this->crawler->getLinkSelector());
    }

    public function testSetVisited(): void
    {
        $newCollection = new Collection();
        $result = $this->crawler->setVisited($newCollection);

        $this->assertSame($this->crawler, $result);
        $this->assertSame($newCollection, $this->crawler->getVisited());
    }

    public function testSetQueued(): void
    {
        $newCollection = new Collection();
        $result = $this->crawler->setQueued($newCollection);

        $this->assertSame($this->crawler, $result);
        $this->assertSame($newCollection, $this->crawler->getQueued());
    }

    public function testGetVisited(): void
    {
        $this->assertInstanceOf(CollectionInterface::class, $this->crawler->getVisited());
    }

    public function testGetQueued(): void
    {
        $this->assertInstanceOf(CollectionInterface::class, $this->crawler->getQueued());
    }

    public function testGetTooDeep(): void
    {
        $this->assertInstanceOf(CollectionInterface::class, $this->crawler->getTooDeep());
    }

    public function testSetUrlValidatorCallback(): void
    {
        $callback = function (string $url) {
            return true;
        };

        $result = $this->crawler->setUrlValidatorCallback($callback);

        $this->assertSame($this->crawler, $result);
    }

    public function testSetPageVisitedCallback(): void
    {
        $callback = function ($domCrawler) {
            // Do nothing
        };

        $result = $this->crawler->setPageVisitedCallback($callback);

        $this->assertSame($this->crawler, $result);
    }

    public function testCrawl(): void
    {
        // Set a URL validator that only allows the base URL
        // This prevents the crawler from processing queued URLs
        $this->crawler->setUrlValidatorCallback(function ($url) {
            return $url === self::TEST_BASE_URL;
        });

        // Mock HTTP response
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body><a href="https://example.com/page1">Page 1</a></body></html>');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', self::TEST_BASE_URL)
            ->willReturn($response);

        // Execute the method
        $this->crawler->crawl();

        // Verify that the base URL was visited
        $visitedUrls = $this->crawler->getVisited()->all();
        $this->assertContains(self::TEST_BASE_URL, $visitedUrls);

        // Verify that no URLs were queued (due to our validator)
        $queuedUrls = $this->crawler->getQueued()->all();
        $this->assertEmpty($queuedUrls);
    }

    public function testCrawlWithMaxDepth(): void
    {
        // Create a new crawler with a deep link already in the queue
        $crawler = new Crawler(
            self::TEST_BASE_URL,
            $this->httpClient
        );

        // Add a link with depth 2 to the queue
        $crawler->getQueued()->appendUrlsAtDepth([self::TEST_BASE_URL], 2);

        // Execute the method with max depth of 1
        $crawler->crawl(1);

        // Verify that the link was moved to the tooDeep collection
        $tooDeepUrls = $crawler->getTooDeep()->all();
        $this->assertContains(self::TEST_BASE_URL, $tooDeepUrls);

        // Verify that the link is not in the visited collection
        $visitedUrls = $crawler->getVisited()->all();
        $this->assertNotContains(self::TEST_BASE_URL, $visitedUrls);
    }

    public function testCrawlWithNonSuccessfulResponse(): void
    {
        // Mock HTTP response with non-200 status code
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', self::TEST_BASE_URL)
            ->willReturn($response);

        // Execute the method
        $this->crawler->crawl();

        // Verify that the link was not added to the visited collection
        $visitedUrls = $this->crawler->getVisited()->all();
        $this->assertEmpty($visitedUrls);
    }

    public function testCrawlWithPageVisitedCallback(): void
    {
        // Set a URL validator that only allows the base URL
        // This prevents the crawler from processing queued URLs
        $this->crawler->setUrlValidatorCallback(function ($url) {
            return $url === self::TEST_BASE_URL;
        });

        // Set up a page visited callback
        $callbackCalled = false;
        $callback = function ($domCrawler) use (&$callbackCalled) {
            $callbackCalled = true;
            $this->assertInstanceOf(DomCrawler::class, $domCrawler);
        };

        $this->crawler->setPageVisitedCallback($callback);

        // Mock HTTP response
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body><a href="https://example.com/page1">Page 1</a></body></html>');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', self::TEST_BASE_URL)
            ->willReturn($response);

        // Execute the method
        $this->crawler->crawl();

        // Verify that the callback was called
        $this->assertTrue($callbackCalled);

        // Verify that the base URL was visited
        $visitedUrls = $this->crawler->getVisited()->all();
        $this->assertContains(self::TEST_BASE_URL, $visitedUrls);
    }

    public function testCrawlWithUrlValidatorCallback(): void
    {
        // Set up a URL validator callback that only allows the base URL and URLs containing 'page1'
        $this->crawler->setUrlValidatorCallback(function ($url) {
            if ($url === self::TEST_BASE_URL) {
                return true;
            }
            return strpos($url, 'page1') !== false;
        });

        // Mock HTTP responses
        $baseResponse = $this->createMock(ResponseInterface::class);
        $baseResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $baseResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body><a href="https://example.com/page1">Page 1</a><a href="https://example.com/page2">Page 2</a></body></html>');

        $page1Response = $this->createMock(ResponseInterface::class);
        $page1Response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404); // Return 404 to prevent further crawling

        // Set up the httpClient to return different responses for different URLs
        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function($method, $url) use ($baseResponse, $page1Response) {
                if ($url === self::TEST_BASE_URL) {
                    return $baseResponse;
                } else if ($url === 'https://example.com/page1') {
                    return $page1Response;
                }
                $this->fail("Unexpected URL: $url");
            });

        // Execute the crawl method
        $this->crawler->crawl();

        // Verify that the base URL was visited
        $visitedUrls = $this->crawler->getVisited()->all();
        $this->assertContains(self::TEST_BASE_URL, $visitedUrls);

        // Verify that page1 URL was queued but not visited (due to 404)
        // and page2 was not queued (filtered out by the callback)
        $queuedUrls = $this->crawler->getQueued()->all();
        $this->assertEmpty($queuedUrls); // All URLs should have been processed
        $this->assertNotContains('https://example.com/page2', $visitedUrls);
    }

    public function testUrlFilteringByBaseUrl(): void
    {
        // Mock HTTP responses
        $baseResponse = $this->createMock(ResponseInterface::class);
        $baseResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $baseResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body>
                <a href="https://example.com/page1">Page 1</a>
                <a href="https://another-domain.com">External Link</a>
            </body></html>');

        $page1Response = $this->createMock(ResponseInterface::class);
        $page1Response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404); // Return 404 to prevent further crawling

        // Set up the httpClient to return different responses for different URLs
        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function($method, $url) use ($baseResponse, $page1Response) {
                if ($url === self::TEST_BASE_URL) {
                    return $baseResponse;
                } else if ($url === 'https://example.com/page1') {
                    return $page1Response;
                }
                $this->fail("Unexpected URL: $url");
            });

        // Execute the crawl method
        $this->crawler->crawl();

        // Verify that the base URL was visited
        $visitedUrls = $this->crawler->getVisited()->all();
        $this->assertContains(self::TEST_BASE_URL, $visitedUrls);

        // Verify that only URLs within the base domain were processed
        // The external domain URL should not have been queued at all
        $this->assertNotContains('https://another-domain.com', $visitedUrls);
    }
}
