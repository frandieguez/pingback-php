<?php
/**
 * This file is part of the Pingback package.
 *
 * (c)  Fran Dieguez <fran.dieguez@mabishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/
namespace Pingback;

use Pingback\RequestHandlerInterface;

/**
 * Performs HTTP requests given a set of params
 *
 * @package Pingback
 **/
class RequestHandler implements RequestHandlerInterface
{
    /**
     * {@inheritdoc}
     **/
    public function get($url, $headers = array())
    {
        $headers = implode('\r\n', $headers);
        $context = stream_context_create(
            array(
                'http' =>
                    array(
                        'method' => "GET",
                        'header' => $headers,
                    )
            )
        );

        $content = file_get_contents($url, false, $context);
        $headers = $http_response_header;

        return array($headers, $content);
    }

    /**
     * {@inheritdoc}
     **/
    public function post($url, $content = '', $headers = array())
    {
        $headers = implode("\r\n", $headers);
        // var_dump($headers, $content);die();
        $context = stream_context_create(
            array(
                'http' =>
                    array(
                        'method'  => "POST",
                        'header'  => $headers,
                        'content' => $content,
                    )
            )
        );
        $content = file_get_contents($url, false, $context);
        $headers = $http_response_header;

        return array($headers, $content);
    }
}
