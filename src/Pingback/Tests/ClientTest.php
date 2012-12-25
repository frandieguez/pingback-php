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
                        'headers' => array(
                            'X-Pingback: http://www.example.com/xmlrpc.php'
                        ),
                        'content' => ''
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
                        'headers' => array(),
                        'content' => '<body><head><link rel="pingback" href="http://www.example.com/xmlrpc.php" />'
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
                        'headers' => array(),
                        'content' => '<body><head></head></body>'
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
}
