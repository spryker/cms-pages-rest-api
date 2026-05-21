<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CmsPagesRestApi\Api\Storefront\Reader;

use Generated\Shared\Transfer\CmsPageStorageTransfer;

interface CmsPageReaderInterface
{
    /**
     * Specification:
     * - Resolves a single CMS page by UUID for the given locale and store.
     * - Returns null when no page matches.
     */
    public function findCmsPageByUuid(string $uuid, string $localeName, string $storeName): ?CmsPageStorageTransfer;

    /**
     * Specification:
     * - Searches CMS pages by free-text query.
     * - Passes `$requestParameters` straight to the search client; the caller builds them via
     *   {@see \Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider::buildSearchPaginationRequestParams()}.
     * - Loads matching CMS page storage records for the given locale and store.
     * - Returns the loaded transfers together with the search engine's total result count.
     *
     * @param array<string, mixed> $requestParameters
     *
     * @return array{transfers: array<int, \Generated\Shared\Transfer\CmsPageStorageTransfer>, totalCount: int}
     */
    public function searchCmsPages(
        string $searchString,
        string $localeName,
        string $storeName,
        array $requestParameters,
    ): array;
}
