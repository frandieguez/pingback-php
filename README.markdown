Pingback-PHP
============

What  is this!
--------------

Library for performing Pingback requests in a simple way (Pingback 1.0 compliant).

Pingback-PHP is Pingback 1.0 standard specification compliant. Please refer to its
[original webpage](http://www.hixie.ch/specs/pingback/pingback) to get deeper information about how it works.

A quick example:

To inform [this article](http://www.mabishu.com/blog/2012/12/14/object-calisthenics-write-better-object-oriented-code/) in that your site has referenced it from [your site post](http://www.mabishu.com/blog/2012/12/14/get-better-performance-and-life-from-your-ssd-in-linux-based-systems/).
With Pingback-PHP you can make it like this:

        // Prepare the Pingback client
        $requestHandler = new Pingback\RequestHandler();
        $client = new Pingback\Client($requestHandler);

        // Perform the pingback call
        try {

            $client->ping(
                "http://www.mabishu.com/blog/2012/12/14/get-better-performance-and-life-from-your-ssd-in-linux-based-systems/",
                "http://www.mabishu.com/blog/2012/12/14/object-calisthenics-write-better-object-oriented-code/";
            );

        } catch (Pingback\Exception $e) {
          printf("Exception raised with code (%d) : %s\n", $e->getCode(), $e->getMessage());
        }

Exception-aware
---------------
Pingback-PHP raises different exceptions if some error happens in the target server
or between client-server communication. So take care of this raised exceptions in your code.


Install it!
-----------

1. Just put in one of your include_path folders, and make sure to use an
PSR-0-compatible autoloader.

Dependencies
------------
This library only depends on PHP 5.3, you have to use namespaces and some other goodies of 5.3 version.

Please __don't ask for PHP < 5.3 support__, you shouldn't use PHP 5.2.


Test it!
--------
Help us to mantain this library updated. Run our unit tests with phpunit to
give us feedback about what doesn't work.

For running tests:

1. Install in your system PHPUnit: http://pear.php.net/package/PHPUnit/redirected
4. And simply run phpunit from the root of projct: phpunit

Build Status
------------
[<img src="https://secure.travis-ci.org/frandieguez/pingback-php.png"/>](http://travis-ci.org/frandieguez/pingback-php)

And... what else?
-----------------
If you find a bug or want to suggest a new video service, please let us know in [a ticket](http://github.com/frandieguez/pingback-php/issues).

Thanks!!
