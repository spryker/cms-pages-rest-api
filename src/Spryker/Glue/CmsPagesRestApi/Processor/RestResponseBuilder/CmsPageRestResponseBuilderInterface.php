<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CmsPagesRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\RestCmsPagesAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface CmsPageRestResponseBuilderInterface
{
    /**
     * @param string $cmsPageUuid
     * @param \Generated\Shared\Transfer\RestCmsPagesAttributesTransfer $restCmsPagesAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createCmsPageRestResponse(string $cmsPageUuid, RestCmsPagesAttributesTransfer $restCmsPagesAttributesTransfer): RestResponseInterface;

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createCmsPageNotFoundErrorRestResponse(): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\CmsPageStorageTransfer[] $cmsPageStorageTransfers
     * @param int $totalPagesFound
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createCmsPageCollectionRestResponse(array $cmsPageStorageTransfers, int $totalPagesFound): RestResponseInterface;
}
