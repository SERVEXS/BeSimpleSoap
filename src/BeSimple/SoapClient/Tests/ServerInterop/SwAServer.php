<?php

require '../../../../../vendor/autoload.php';

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\downloadFile;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\downloadFileResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\uploadFile;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\uploadFileResponse;
use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;

$options = [
    'soap_version' => \SOAP_1_1,
    'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_SWA,
    'cache_wsdl' => \WSDL_CACHE_NONE,
    'classmap' => [
        'downloadFile' => downloadFile::class,
        'downloadFileResponse' => downloadFileResponse::class,
        'uploadFile' => uploadFile::class,
        'uploadFileResponse' => uploadFileResponse::class,
    ],
];

class SwAServer
{
    public function uploadFile(uploadFile $uploadFile)
    {
        file_put_contents(__DIR__ . '/' . $uploadFile->name, $uploadFile->data);

        $ufr = new uploadFileResponse();
        $ufr->return = 'File saved succesfully.';

        return $ufr;
    }

    public function downloadFile(downloadFile $downloadFile)
    {
        $dfr = new downloadFileResponse();
        $dfr->data = file_get_contents(__DIR__ . '/' . $downloadFile->name);

        return $dfr;
    }
}

$ss = new BeSimpleSoapServer(__DIR__ . '/Fixtures/SwA.wsdl', $options);
$ss->setClass('SwA');
$ss->handle();
