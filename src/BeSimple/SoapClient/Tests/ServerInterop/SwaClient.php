<?php

require '../../../../../vendor/autoload.php';

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\downloadFile;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\downloadFileResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\uploadFile;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\uploadFileResponse;
use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;

$options = [
    'soap_version' => \SOAP_1_1,
    'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_SWA,
    'cache_wsdl' => \WSDL_CACHE_NONE,
    'trace' => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
    'classmap' => [
        'downloadFile' => downloadFile::class,
        'downloadFileResponse' => downloadFileResponse::class,
        'uploadFile' => uploadFile::class,
        'uploadFileResponse' => uploadFileResponse::class,
    ],
];

$sc = new BeSimpleSoapClient(__DIR__ . '/Fixtures/SwA.wsdl', $options);

try {
    $upload = new uploadFile();
    $upload->name = 'upload.txt';
    $upload->data = 'This is a test. :)';
    $result = $sc->uploadFile($upload);

    var_dump($result);

    $download = new downloadFile();
    $download->name = 'upload.txt';
    var_dump($sc->downloadFile($download));
} catch (Exception $e) {
    var_dump($e);
}

// var_dump(
//     $sc->__getLastRequestHeaders(),
//     $sc->__getLastRequest(),
//     $sc->__getLastResponseHeaders(),
//     $sc->__getLastResponse()
// );
