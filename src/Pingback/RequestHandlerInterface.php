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

/**
 * Performs HTTP requests given a set of params
 *
 * @package Pingback
 **/
interface RequestHandlerInterface
{
    /**
     * Performs a GET request returning the HTTP headers and contents
     *
     * @param string $url the url to get contents from
     * @param array headers list of custom headers that must be sent in the request
     *
     * @return array the headers and contents for the request
     **/
    public function get($url, $headers = array());

    /**
     * Performs a POST request returning the HTTP headers, and contents
     *
     * @param string $url the url to get contents from
     * @param string $content the content that must be sent as the request body
     * @param array headers list of custom headers that must be sent in the request
     *
     * @return array the headers and contents for the request
     **/
    public function post($url, $content = '', $headers = array());
}
