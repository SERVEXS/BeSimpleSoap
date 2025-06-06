<?php

require '../../../../../vendor/autoload.php';

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\AttachmentRequest;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\base64Binary;
use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;

$options = [
    'soap_version' => \SOAP_1_1,
    'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_MTOM,
    'cache_wsdl' => \WSDL_CACHE_NONE,
    'classmap' => [
        'base64Binary' => base64Binary::class,
        'AttachmentType' => AttachmentRequest::class,
    ],
];

class MTOMServer
{
    public function attachment(Fixtures\AttachmentRequest $attachment)
    {
        $b64 = $attachment->binaryData;

        file_put_contents(__DIR__ . '/' . $attachment->fileName, $b64->_);

        return 'File saved succesfully.';
    }
}

$ss = new BeSimpleSoapServer(__DIR__ . '/Fixtures/MTOM.wsdl', $options);
$ss->setClass('Mtom');
$ss->handle();
