<?php

namespace Tests\Unit;

use Covaleski\Helpers\Request;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Helpers\Request
 */
#[CoversMethod(Request::class, 'getHeaderLine')]
#[CoversMethod(Request::class, 'getHeaderOptions')]
#[CoversMethod(Request::class, 'getHeaderValues')]
#[CoversMethod(Request::class, 'compareHeaderValues')]
#[CoversMethod(Request::class, 'getQualityValue')]
final class RequestTest extends TestCase
{
    /**
     * Test if can get header lines.
     */
    public function testGetsHeaderLines(): void
    {
        $line = Request::getHeaderLine('Authorization');
        $this->assertSame('Basic YWxhZGRpbjpvcGVuc2VzYW1l', $line);
        $line = Request::getHeaderLine('AuThOrIzAtIoN');
        $this->assertSame('Basic YWxhZGRpbjpvcGVuc2VzYW1l', $line);
        $line = Request::getHeaderLine('User-Agent');
        $this->assertSame('Chrome/51.0.2704.103 Safari/537.36', $line);
        $line = Request::getHeaderLine('UsEr_agent');
        $this->assertSame('Chrome/51.0.2704.103 Safari/537.36', $line);
    }

    /**
     * Test if can get and sort user preferences such as language.
     */
    public function testGetsHeaderOptions(): void
    {
        $options = Request::getHeaderOptions('Accept-Charset');
        $this->assertSame(['utf-8', 'iso-8859-1', '*'], $options);
        $options = Request::getHeaderOptions('Accept_CHARseT');
        $this->assertSame(['utf-8', 'iso-8859-1', '*'], $options);
        $options = Request::getHeaderOptions('Accept-Language');
        $this->assertSame(['fr-CH', 'fr', '*'], $options);
        $options = Request::getHeaderOptions('accept_LANGUAGE');
        $this->assertSame(['fr-CH', 'fr', '*'], $options);
    }

    /**
     * Test if can get header comma-separated values.
     */
    public function testGetsHeaderValues(): void
    {
        $values = Request::getHeaderValues('X-Forwarded-For');
        $this->assertSame(['203.0.113.195', '70.41.3.18'], $values);
        $values = Request::getHeaderValues('X_FOrwaRDED_for');
        $this->assertSame(['203.0.113.195', '70.41.3.18'], $values);
        $values = Request::getHeaderValues('Accept-Charset');
        $this->assertSame(['utf-8', '*;q=0.1', 'iso-8859-1;q=0.5'], $values);
        $values = Request::getHeaderValues('ACCEPT_charSeT');
        $this->assertSame(['utf-8', '*;q=0.1', 'iso-8859-1;q=0.5'], $values);
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 'utf-8, *;q=0.1, iso-8859-1;q=0.5';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "fr;q=0.9,   *; q = 0.5\n, fr-CH";
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic YWxhZGRpbjpvcGVuc2VzYW1l';
        $_SERVER['HTTP_USER_AGENT'] = " \nChrome/51.0.2704.103 Safari/537.36 ";
        $_SERVER['HTTP_REFERER'] = 'https://foo.bar.org';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "  203.0.113.195, \n\n70.41.3.18";
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        unset(
            $_SERVER['HTTP_ACCEPT_CHARSET'],
            $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['HTTP_REFERER'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
        );
    }
}
