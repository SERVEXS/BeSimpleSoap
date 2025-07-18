<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\SoapKernel as CommonSoapKernel;
use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;

/**
 * SoapKernel for Client.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class SoapKernel extends CommonSoapKernel
{
    public function filterRequest(CommonSoapRequest $request): void
    {
        $request->setAttachments($this->attachments);
        $this->attachments = [];

        parent::filterRequest($request);
    }

    public function filterResponse(CommonSoapResponse $response): void
    {
        parent::filterResponse($response);

        $this->attachments = $response->getAttachments();
    }
}
