<?php

namespace Tests\Cookie;

use WPWCore\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Cookie;
use WPWhales\Http\Response;
use WPWhales\Testing\TestResponse;

class CookieTest extends TestCase
{
    public function test_CookiesAreCreatedWithProperOptions()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('foo', 'bar');
        $c = $cookie->make('color', 'blue', 10, '/path', '/domain', true, false, false, 'lax');
        $this->assertSame('blue', $c->getValue());
        $this->assertFalse($c->isHttpOnly());
        $this->assertTrue($c->isSecure());
        $this->assertSame('/domain', $c->getDomain());
        $this->assertSame('/path', $c->getPath());
        $this->assertSame('lax', $c->getSameSite());

        $c2 = $cookie->forever('color', 'blue', '/path', '/domain', true, false, false, 'strict');
        $this->assertSame('blue', $c2->getValue());
        $this->assertFalse($c2->isHttpOnly());
        $this->assertTrue($c2->isSecure());
        $this->assertSame('/domain', $c2->getDomain());
        $this->assertSame('/path', $c2->getPath());
        $this->assertSame('strict', $c2->getSameSite());

        $c3 = $cookie->forget('color');
        $this->assertNull($c3->getValue());
        $this->assertTrue($c3->getExpiresTime() < time());
    }

    public function test_CookiesAreCreatedWithProperOptionsUsingDefaultPathAndDomain()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('/path', '/domain', true, 'lax');
        $c = $cookie->make('color', 'blue');
        $this->assertSame('blue', $c->getValue());
        $this->assertTrue($c->isSecure());
        $this->assertSame('/domain', $c->getDomain());
        $this->assertSame('/path', $c->getPath());
        $this->assertSame('lax', $c->getSameSite());
    }

    public function test_CookiesCanSetSecureOptionUsingDefaultPathAndDomain()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('/path', '/domain', true, 'lax');
        $c = $cookie->make('color', 'blue', 10, null, null, false);
        $this->assertSame('blue', $c->getValue());
        $this->assertFalse($c->isSecure());
        $this->assertSame('/domain', $c->getDomain());
        $this->assertSame('/path', $c->getPath());
        $this->assertSame('lax', $c->getSameSite());
    }

    public function test_QueuedCookies()
    {
        $cookie = $this->getCreator();
        $this->assertEmpty($cookie->getQueuedCookies());
        $this->assertFalse($cookie->hasQueued('foo'));
        $cookie->queue($cookie->make('foo', 'bar'));
        $this->assertTrue($cookie->hasQueued('foo'));
        $this->assertInstanceOf(Cookie::class, $cookie->queued('foo'));
        $cookie->queue('qu', 'ux');
        $this->assertTrue($cookie->hasQueued('qu'));
        $this->assertInstanceOf(Cookie::class, $cookie->queued('qu'));
    }

    public function test_QueuedWithPath(): void
    {
        $cookieJar = $this->getCreator();
        $cookieOne = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieTwo = $cookieJar->make('foo', 'rab', 0, '/');
        $cookieJar->queue($cookieOne);
        $cookieJar->queue($cookieTwo);
        $this->assertEquals($cookieOne, $cookieJar->queued('foo', null, '/path'));
        $this->assertEquals($cookieTwo, $cookieJar->queued('foo', null, '/'));
    }

    public function test_QueuedWithoutPath(): void
    {
        $cookieJar = $this->getCreator();
        $cookieOne = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieTwo = $cookieJar->make('foo', 'rab', 0, '/');
        $cookieJar->queue($cookieOne);
        $cookieJar->queue($cookieTwo);
        $this->assertEquals($cookieTwo, $cookieJar->queued('foo'));
    }

    public function test_HasQueued(): void
    {
        $cookieJar = $this->getCreator();
        $cookie = $cookieJar->make('foo', 'bar');
        $cookieJar->queue($cookie);
        $this->assertTrue($cookieJar->hasQueued('foo'));
    }

    public function test_HasQueuedWithPath(): void
    {
        $cookieJar = $this->getCreator();
        $cookieOne = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieTwo = $cookieJar->make('foo', 'rab', 0, '/');
        $cookieJar->queue($cookieOne);
        $cookieJar->queue($cookieTwo);
        $this->assertTrue($cookieJar->hasQueued('foo', '/path'));
        $this->assertTrue($cookieJar->hasQueued('foo', '/'));
        $this->assertFalse($cookieJar->hasQueued('foo', '/wrongPath'));
    }

    public function test_Expire()
    {
        $cookieJar = $this->getCreator();
        $this->assertCount(0, $cookieJar->getQueuedCookies());

        $cookieJar->expire('foobar', '/path', '/domain');

        $cookie = $cookieJar->queued('foobar');
        $this->assertSame('foobar', $cookie->getName());
        $this->assertEquals(null, $cookie->getValue());
        $this->assertSame('/path', $cookie->getPath());
        $this->assertSame('/domain', $cookie->getDomain());
        $this->assertTrue($cookie->getExpiresTime() < time());
        $this->assertCount(1, $cookieJar->getQueuedCookies());
    }

    public function test_Unqueue()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->make('foo', 'bar'));
        $cookie->unqueue('foo');
        $this->assertEmpty($cookie->getQueuedCookies());
    }

    public function test_UnqueueWithPath(): void
    {
        $cookieJar = $this->getCreator();
        $cookieOne = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieTwo = $cookieJar->make('foo', 'rab', 0, '/');
        $cookieJar->queue($cookieOne);
        $cookieJar->queue($cookieTwo);
        $cookieJar->unqueue('foo', '/path');
        $this->assertEquals(['foo' => ['/' => $cookieTwo]], $this->getQueuedPropertyValue($cookieJar));
    }

    public function test_UnqueueOnlyCookieForName(): void
    {
        $cookieJar = $this->getCreator();
        $cookie = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieJar->queue($cookie);
        $cookieJar->unqueue('foo', '/path');
        $this->assertEmpty($this->getQueuedPropertyValue($cookieJar));
    }

    public function test_CookieJarIsMacroable()
    {
        $cookie = $this->getCreator();
        $cookie->macro('foo', function () {
            return 'bar';
        });
        $this->assertSame('bar', $cookie->foo());
    }

    public function test_QueueCookie(): void
    {
        $cookieJar = $this->getCreator();
        $cookie = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieJar->queue($cookie);
        $this->assertEquals(['foo' => ['/path' => $cookie]], $this->getQueuedPropertyValue($cookieJar));
    }

    public function test_QueueWithCreatingNewCookie(): void
    {
        $cookieJar = $this->getCreator();
        $cookieJar->queue('foo', 'bar', 0, '/path');
        $this->assertEquals(
            ['foo' => ['/path' => new Cookie('foo', 'bar', 0, '/path')]],
            $this->getQueuedPropertyValue($cookieJar)
        );
    }

    public function test_GetQueuedCookies(): void
    {
        $cookieJar = $this->getCreator();
        $cookieOne = $cookieJar->make('foo', 'bar', 0, '/path');
        $cookieTwo = $cookieJar->make('foo', 'rab', 0, '/');
        $cookieThree = $cookieJar->make('oof', 'bar', 0, '/path');
        $cookieJar->queue($cookieOne);
        $cookieJar->queue($cookieTwo);
        $cookieJar->queue($cookieThree);
        $this->assertEquals(
            [$cookieOne, $cookieTwo, $cookieThree],
            $cookieJar->getQueuedCookies()
        );
    }

    public function test_FlushQueuedCookies(): void
    {
        $cookieJar = $this->getCreator();
        $cookieJar->queue($cookieJar->make('foo', 'bar', 0, '/path'));
        $cookieJar->queue($cookieJar->make('foo', 'rab', 0, '/'));
        $this->assertCount(2, $cookieJar->getQueuedCookies());

        $cookieJar->flushQueuedCookies();
        $this->assertEmpty($cookieJar->getQueuedCookies());
    }

    public function test_AttachToResponseAndRemoveFromQueueAndAddedInSentCookies()
    {
        $response = new Response();
        $cookieJar = $this->getCreator();
        $cookieJar->queue("test_cookie", "test_value");
        $this->assertInstanceOf(Cookie::class, $cookieJar->queued("test_cookie"));
        $this->assertFalse(in_array("test_cookie", $cookieJar->sentCookies));
        $response = TestResponse::fromBaseResponse($cookieJar->attachCookiesInResponse($response));

        $response->assertCookie("test_cookie", "test_value", false);

        $this->assertFalse($cookieJar->queued("test_cookie", false));

        $this->assertTrue(in_array("test_cookie", $cookieJar->sentCookies));
    }

    public function test_SendHeadersMethodUnqueueTheCookieAndAddInSentCookies()
    {


        $cookieJar = $this->createPartialMock(CookieJar::class, ["setCookie"]);
        $cookieJar->queue("test_cookie", "test_value");
        $this->assertInstanceOf(Cookie::class, $cookieJar->queued("test_cookie"));
        $this->assertFalse(in_array("test_cookie", $cookieJar->sentCookies));
        $cookie = $cookieJar->queued("test_cookie");
        $name = $cookie->getName();
        $value = $cookie->getValue();
        $expire = $cookie->getExpiresTime();
        $path = $cookie->getPath();
        $domain = $cookie->getDomain();
        $secure = $cookie->isSecure();
        $httponly = $cookie->isHttpOnly();
        $samesite = $cookie->getSameSite();
        $cookieJar->expects($this->once())->method("setCookie")->with($name,$value,[
            "expires"  => $expire,
            "path"     => $path,
            "domain"   => $domain,
            "secure"   => $secure,
            "httponly" => $httponly,
            "samesite" => $samesite
        ]);
        $cookieJar->sendHeaders();

        $this->assertFalse($cookieJar->queued("test_cookie", false));

        $this->assertTrue(in_array("test_cookie", $cookieJar->sentCookies));
    }


    public function getCreator()
    {
        return new CookieJar;
    }

    private function getQueuedPropertyValue(CookieJar $cookieJar)
    {
        $property = (new ReflectionObject($cookieJar))->getProperty('queued');

        return $property->getValue($cookieJar);
    }
}