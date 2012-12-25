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

use Pingback\RequestHandler;
use Pingback\Exception;

/**
 * Implements the Pingback client for performing pingback requests
 *
 * This library is Pingback 1.0 compliant: take a look at the specification at
 * http://www.hixie.ch/specs/pingback/pingback
 *
 * @package Pingback
 **/
class Client
{
    /**
     * The User-agent sended to the server to identify this library
     *
     * @var string
     **/
    private $agentString = 'Pingback-PHP 0.9';

    /**
     * Initializes the pingback client
     *
     * @return void
     **/
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        // TODO: Avoid use of xmlrpc calls by using an external request handler
        $phpExtensions = get_loaded_extensions();
        $xmlrpcLoaded = in_array('xmlrpc', $phpExtensions);

        if (!$xmlrpcLoaded) {
            throw new Exception\XmlRPCExtensionNotLoaded('xmlrpc extension not loaded');
        }

        $this->handler = $requestHandler;
    }

    /**
     * Performs the pingback.ping XMLRPC request from the given source url to
     * the given target url
     *
     * Example:
     *     $requestHandler = new Pingback\RequestHandler();
     *     $client = new Pingback\Client($requestHandler);
     *     $client->ping($sourceUrl, $targetUrl);
     *
     * @param string $sourceUrl the referecee url
     * @param string $targetUrl the referenced url
     *
     * @return void
     **/
    public function ping($sourceUrl, $targetUrl)
    {
        $server = $this->discoverXmlRPCServer($targetUrl);

        $serverResponse = $this->performServerRequest(
            array(
                'source_url'    => $sourceUrl,
                'target_url'    => $targetUrl,
                'xmlrpc_server' => $server,
            )
        );

        $this->handleResponse($serverResponse);
    }

    /**
     * Discovers the XmlRPC server url given a sourceURL
     *
     * @return string the XmlRPC server url
     **/
    public function discoverXmlRPCServer($sourceUrl)
    {
        // TODO: Make use of an external request handler
        list($headers, $document) = $this->getSourceUrlContent($sourceUrl);

        preg_match('@<link rel="pingback" href="([^>]*)" /?>@i', $document, $matches);

        if (!array_key_exists(1, $matches)) {
            throw new Exception\NotAvailableXmlRPCServer(
                'Unable to find the target XMLRPC server'
            );
        }

        return $matches[1];
    }

    /**
     * Performs the XMLRPC request given an array that must contain:
     *  'source_url'    the original url that references the target url
     *  'target_url'    the refered url
     *  'xmlrpc_server' XMLRPC server urlt
     *
     * @param array $params required params to perform the request
     * @return string the response
     **/
    public function performServerRequest($params)
    {
        $context = $this->prepareContext(
            $params['source_url'],
            $params['target_url']
        );

        // TODO: Make use of an external request handler
        return file_get_contents($params['xmlrpc_server'], false, $context);
    }

    /**
     * Prepares the XMLRPC request given two urls
     *
     * @return Context
     **/
    private function prepareContext($sourceUrl, $targetUrl)
    {
        $parse = parse_url($targetUrl);
        $targetBaseDomain = $parse['host'];

        $request = '<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
<methodName>pingback.ping</methodName>
<params>
 <param>
  <value>
   <string>'.$sourceUrl.'</string>
  </value>
 </param>
 <param>
  <value>
   <string>'.$targetUrl.'</string>
  </value>
 </param>
</params>
</methodCall>';

        $context = stream_context_create(
            array(
                'http' =>
                array(
                    'method' => "POST",
                    'header' =>
                        "Content-Type: text/xml\r\n"
                        ."User-Agent: {$this->agentString}\r\n"
                        ."Host: $targetBaseDomain\r\n",
                    'content' => $request
                )
            )
        );

        return $context;
    }

    /**
     * Handles the server response.
     * If something goes wrong raises and specialized exception
     *
     * @param string $serverResponse the string returned by the server
     *
     * @return void
     * @throws Pingback\Exception If some error in request is raised
     **/
    public function handleResponse($serverResponse)
    {
        // TODO: Avoid usage of php-xmlrpc extensions functions
        $response = xmlrpc_decode($serverResponse);
        if (is_array($response) && xmlrpc_is_fault($response)) {
            switch ($response['faultCode']) {
                case 16:
                    throw new Exception\SourceURINotValid(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
                case 17:
                    throw new Exception\TargetUriNotInSourceUri(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
                case 32:
                case 33:
                    throw new Exception\TargetURINotValid(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
                case 48:
                    throw new Exception\PingAlreadyRegistered(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
                case 50:
                    throw new Exception\ErrorFromUpstreamServer(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
                default:
                    throw new Exception\Response(
                        $response['faultString'],
                        $response['faultCode']
                    );
                    break;
            }
        }
    }

    /**
     * Retrieves the sourceUrl HTTP headers and content
     *
     * @return array the http headers and html content
     **/
    public function getSourceUrlContent($sourceUrl)
    {
        $headers = array();
        $content = file_get_contents($sourceUrl);

        return array($headers, $content);
    }
}
