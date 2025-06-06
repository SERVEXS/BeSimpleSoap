<?php

require '../../../../../vendor/autoload.php';

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;
use BeSimple\SoapServer\WsSecurityFilter as BeSimpleWsSecurityFilter;

$options = [
    'soap_version' => \SOAP_1_1,
    'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'cache_wsdl' => \WSDL_CACHE_NONE,
    'classmap' => [
        'getBook' => getBook::class,
        'getBookResponse' => getBookResponse::class,
        'getBooksByType' => getBooksByType::class,
        'getBooksByTypeResponse' => getBooksByTypeResponse::class,
        'addBook' => addBook::class,
        'addBookResponse' => addBookResponse::class,
        'BookInformation' => BookInformation::class,
    ],
];

class Auth
{
    public static function usernamePasswordCallback($user)
    {
        if ($user == 'libuser') {
            return 'books';
        }

        return null;
    }
}

class WsSecurityUserPassServer
{
    public function getBook(getBook $gb)
    {
        $bi = new BookInformation();
        $bi->isbn = $gb->isbn;
        $bi->title = 'title';
        $bi->author = 'author';
        $bi->type = 'scifi';

        $br = new getBookResponse();
        $br->getBookReturn = $bi;

        return $br;
    }

    public function addBook(addBook $ab)
    {
        $abr = new addBookResponse();
        $abr->addBookReturn = true;

        return $abr;
    }
}

$ss = new BeSimpleSoapServer(__DIR__ . '/Fixtures/WsSecurityUserPass.wsdl', $options);

$wssFilter = new BeSimpleWsSecurityFilter();
$wssFilter->setUsernamePasswordCallback(['Auth', 'usernamePasswordCallback']);

$soapKernel = $ss->getSoapKernel();
$soapKernel->registerFilter($wssFilter);

$ss->setClass('WsSecurityUserPassServer');
$ss->handle();
