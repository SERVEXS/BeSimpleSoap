<?php

/*
* Deploy "axis_services/library-username-digest.aar" to Apache Axis2 to get
* this example to work.
*
* Using code from axis example:
* http://www.ibm.com/developerworks/java/library/j-jws4/index.html
*
* build.properties:
* server-policy=hash-policy-server.xml
*
* allows both text and digest!
*/

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\BookInformation;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\AxisInterop\TestCase;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;

class WsSecurityUserPassAxisInteropTest extends TestCase
{
    private array $options = [
        'soap_version' => \SOAP_1_2,
        'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'classmap' => [
            'getBook' => getBook::class,
            'getBookResponse' => getBookResponse::class,
            'getBooksByType' => getBooksByType::class,
            'getBooksByTypeResponse' => getBooksByTypeResponse::class,
            'addBook' => addBook::class,
            'addBookResponse' => addBookResponse::class,
            'BookInformation' => BookInformation::class,
        ],
        'proxy_host' => false,
    ];

    public function testUserPassText(): void
    {
        $sc = new BeSimpleSoapClient(__DIR__ . '/Fixtures/WsSecurityUserPass.wsdl', $this->options);

        $wssFilter = new BeSimpleWsSecurityFilter(true, 600);
        $wssFilter->addUserData('libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_TEXT);

        $soapKernel = $sc->getSoapKernel();
        $soapKernel->registerFilter($wssFilter);

        $gb = new getBook();
        $gb->isbn = '0061020052';
        $result = $sc->getBook($gb);
        $this->assertInstanceOf(BookInformation::class, $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }

    public function testUserPassDigest(): void
    {
        $sc = new BeSimpleSoapClient(__DIR__ . '/Fixtures/WsSecurityUserPass.wsdl', $this->options);

        $wssFilter = new BeSimpleWsSecurityFilter(true, 600);
        $wssFilter->addUserData('libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_DIGEST);

        $soapKernel = $sc->getSoapKernel();
        $soapKernel->registerFilter($wssFilter);

        $gb = new getBook();
        $gb->isbn = '0061020052';
        $result = $sc->getBook($gb);
        $this->assertInstanceOf(BookInformation::class, $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }
}
