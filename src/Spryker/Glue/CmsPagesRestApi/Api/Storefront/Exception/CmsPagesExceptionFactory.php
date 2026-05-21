<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CmsPagesRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\CmsPagesRestApi\CmsPagesRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class CmsPagesExceptionFactory
{
    public function createCmsPageNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CmsPagesRestApiConfig::RESPONSE_CODE_CMS_PAGE_NOT_FOUND,
            CmsPagesRestApiConfig::RESPONSE_DETAIL_CMS_PAGE_NOT_FOUND,
        );
    }
}
