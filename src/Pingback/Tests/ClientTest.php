<?php
/*
 * This file is part of the Pingback package.
 *
 * (c) Fran Dieguez <fran.dieguez@mabishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pingback\Tests;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->requestHandler = $this->getMock('\Pingback\RequestHandler');
    }

    public function testDiscoverValidXmlRPCServerInHeaders()
    {
        // Prepare the mocked requestHandler object
        $requestHandler = $this->getMock('\Pingback\RequestHandler');

        $requestHandler->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'X-Pingback: http://www.example.com/xmlrpc.php'
                        ),
                        ''
                    )
                )
            );

        $client = new \Pingback\Client($requestHandler);

        $this->assertEquals(
            'http://www.example.com/xmlrpc.php',
            $client->discoverXmlRPCServer('http://www.example.com/some/url'),
            'Found valid XML-RPC server in headers'
        );
    }

    public function testDiscoverValidXmlRPCServerInContent()
    {
        // Prepare the mocked requestHandler object
        $requestHandler = $this->getMock('\Pingback\RequestHandler');

        $requestHandler->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValue(
                    array(
                        array(),
                        '<body><head><link rel="pingback" href="http://www.example.com/xmlrpc.php" />'
                    )
                )
            );

        $client = new \Pingback\Client($requestHandler);

        $this->assertEquals(
            'http://www.example.com/xmlrpc.php',
            $client->discoverXmlRPCServer('http://www.example.com/some/url'),
            'Found valid XML-RPC server in headers'
        );
    }

    /**
     * @expectedException Pingback\Exception\NotAvailableXmlRPCServer
     */
    public function testRaisesNotAvailabelXmlRPCServerInNotavailableServer()
    {
        // Prepare the mocked requestHandler object
        $this->requestHandler->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValue(
                    array(
                        array(),
                        '<body><head></head></body>'
                    )
                )
            );

        $client = new \Pingback\Client($this->requestHandler);

        $this->assertEquals(
            'http://www.example.com/xmlrpc.php',
            $client->discoverXmlRPCServer('http://www.example.com/some/url'),
            'Found valid XML-RPC server in headers'
        );
    }

    public function testperformServerRequest()
    {
        // Prepare the mocked requestHandler object
        $this->requestHandler->expects($this->once())
            ->method('post')
            ->with(
                'http://www.example.com/xmlrpc.php',
                "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<methodCall>\n"
                ."<methodName>pingback.ping</methodName>\n<params>\n <param>\n  "
                ."<value>\n   <string>http://original.url/with/path</string>\n  "
                ."</value>\n </param>\n <param>\n  <value>\n   "
                ."<string>http://target.url/with/path</string>\n  </value>\n "
                ."</param>\n</params>\n</methodCall>",
                array(
                    "Content-Type: text/xml",
                    "User-Agent: Pingback-PHP 0.9",
                    "Host: target.url"
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        array(),
                        '<body><head></head></body>'
                    )
                )
            );

        $client = new \Pingback\Client($this->requestHandler);

        $this->assertEquals(
            $client->performServerRequest(
                array(
                    'xmlrpc_server' => 'http://www.example.com/xmlrpc.php',
                    'source_url'    => 'http://original.url/with/path',
                    'target_url'    => 'http://target.url/with/path',
                )
            ),
            array(
                array(),
                '<body><head></head></body>'
            )
        );
    }


    public function testPing()
    {
        // Prepare the mocked requestHandler object
        $this->requestHandler->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'X-Pingback: http://www.example.com/xmlrpc.php'
                        ),
                        ''
                    )
                )
            );

        $this->requestHandler->expects($this->once())
            ->method('post')
            ->with(
                'http://www.example.com/xmlrpc.php',
                "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<methodCall>\n"
                ."<methodName>pingback.ping</methodName>\n<params>\n <param>\n  "
                ."<value>\n   <string>http://original.url/with/path</string>\n  "
                ."</value>\n </param>\n <param>\n  <value>\n   "
                ."<string>http://target.url/with/path</string>\n  </value>\n "
                ."</param>\n</params>\n</methodCall>",
                array(
                    "Content-Type: text/xml",
                    "User-Agent: Pingback-PHP 0.9",
                    "Host: target.url"
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        array(),
                        '<body><head></head></body>'
                    )
                )
            );

        $client = new \Pingback\Client($this->requestHandler);

        $this->assertEquals(
            $client->ping('http://original.url/with/path', 'http://target.url/with/path'),
            null
        );
    }

    public function testprepareRequestComponents()
    {
        $client = new \Pingback\Client($this->requestHandler);


        $this->assertEquals(
            $client->prepareRequestComponents(
                'http://original.url/with/path',
                'http://target.url/with/path'
            ),
            array(
                array(
                    "Content-Type: text/xml",
                    "User-Agent: Pingback-PHP 0.9",
                    "Host: target.url"
                ),
                "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<methodCall>\n"
                ."<methodName>pingback.ping</methodName>\n<params>\n <param>\n  "
                ."<value>\n   <string>http://original.url/with/path</string>\n  "
                ."</value>\n </param>\n <param>\n  <value>\n   "
                ."<string>http://target.url/with/path</string>\n  </value>\n "
                ."</param>\n</params>\n</methodCall>"
            )
        );
    }

    /**
     * @expectedException Pingback\Exception\Response
     */
    public function testHandleResponseWithResponseException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 0,
                'faultString' => 'Generic fault.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

        /**
     * @expectedException Pingback\Exception\SourceUriNotValid
     */
    public function testHandleResponseWithSourceUriNotValidException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 16,
                'faultString' => 'The source URI does not contain a link to the target URI.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

    /**
     * @expectedException Pingback\Exception\TargetUriNotInSourceUri
     */
    public function testHandleResponseWithTargetUriNotInSourceUriException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 17,
                'faultString' => 'The source URI does not contain a link to the target URI.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

    /**
     * @expectedException Pingback\Exception\TargetURINotValid
     */
    public function testHandleResponseWithTargetURINotValidException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 32,
                'faultString' => 'The specified target URI does not exists.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

    /**
     * @expectedException Pingback\Exception\PingAlreadyRegistered
     */
    public function testHandleResponseWithPingAlreadyRegisteredException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 48,
                'faultString' => 'The pingback has already been registered.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

    /**
     * @expectedException Pingback\Exception\ErrorFromUpstreamServer
     */
    public function testHandleResponseWithErrorFromUpstreamServerException()
    {
        $serverResponse = xmlrpc_encode(
            array(
                'faultCode'   => 50,
                'faultString' => 'The server could not communicate with an upstream server.',
            )
        );

        $client = new \Pingback\Client($this->requestHandler);
        $client->handleResponse($serverResponse);
    }

    // /**
    //  * @expectedException Pingback\Exception\TargetURINotValid
    //  */
    // public function testInitClientWithExtensionNotLoadedException()
    // {
    // }
}
