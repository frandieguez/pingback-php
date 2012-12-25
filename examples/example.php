<?php
require __DIR__.'/../autoload.php.dist';

// Prepare the source and target urls
$siteOrigin =
    "http://www.mabishu.com/blog/2012/12/14/get-better-performance-"
    ."and-life-from-your-ssd-in-linux-based-systems/";

$siteTarget =
    "http://www.mabishu.com/blog/2012/12/14/object-calisthenics-write-"
    ."better-object-oriented-code/";

// Prepare the Pingback client
$requestHandler = new Pingback\RequestHandler();
$client = new Pingback\Client($requestHandler);

// Perform the pinbback call
try {
    $client->ping($siteOrigin, $siteTarget);
} catch (Pingback\Exception $e) {
    printf("Exception raised with code (%d) : %s\n", $e->getCode(), $e->getMessage());
}
