<?php

namespace Medvinator\BxForce\Support;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ResponseSequence;
use RuntimeException;

/**
 * @method static PromiseInterface response($body = null, $status = 200, $headers = [])
 * @method static Factory fake($callback = null)
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest asJson()
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest attach(string $name, string $contents, string|null $filename = null, array $headers = [])
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest beforeSending(callable $callback)
 * @method static PendingRequest bodyFormat(string $format)
 * @method static PendingRequest contentType(string $contentType)
 * @method static PendingRequest retry(int $times, int $sleep = 0)
 * @method static PendingRequest stub(callable $callback)
 * @method static PendingRequest timeout(int $seconds)
 * @method static PendingRequest withBasicAuth(string $username, string $password)
 * @method static PendingRequest withCookies(array $cookies, string $domain)
 * @method static PendingRequest withDigestAuth(string $username, string $password)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest withOptions(array $options)
 * @method static PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method static Response delete(string $url, array $data = [])
 * @method static Response get(string $url, array $query = [])
 * @method static Response head(string $url, array $query = [])
 * @method static Response patch(string $url, array $data = [])
 * @method static Response post(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static Response send(string $method, string $url, array $options = [])
 * @method static ResponseSequence fakeSequence(string $urlPattern = '*')
 *
 * @see \Illuminate\Http\Client\Factory
 */
class Http
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = new Factory();
        return $instance->$method( ...$args );
    }
}
