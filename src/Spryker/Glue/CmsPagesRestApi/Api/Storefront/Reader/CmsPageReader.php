<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CmsPagesRestApi\Api\Storefront\Reader;

use Generated\Shared\Transfer\CmsPageStorageTransfer;
use Spryker\Client\CmsPageSearch\CmsPageSearchClientInterface;
use Spryker\Client\CmsStorage\CmsStorageClientInterface;

class CmsPageReader implements CmsPageReaderInterface
{
    /**
     * @uses \Spryker\Client\CmsPageSearch\Plugin\Elasticsearch\ResultFormatter\RawCmsPageSearchResultFormatterPlugin::NAME
     */
    protected const string SEARCH_RESULT_CMS_PAGES = 'cms_pages';

    /**
     * @uses \Spryker\Client\CmsPageSearch\Plugin\Elasticsearch\ResultFormatter\PaginatedCmsPageResultFormatterPlugin::NAME
     */
    protected const string SEARCH_RESULT_PAGINATION = 'pagination';

    protected const string ID_CMS_PAGE = 'id_cms_page';

    public function __construct(
        protected CmsStorageClientInterface $cmsStorageClient,
        protected CmsPageSearchClientInterface $cmsPageSearchClient,
    ) {
    }

    public function findCmsPageByUuid(string $uuid, string $localeName, string $storeName): ?CmsPageStorageTransfer
    {
        $cmsPageStorageTransfers = $this->cmsStorageClient->getCmsPageStorageByUuids([$uuid], $localeName, $storeName);

        $cmsPageStorageTransfer = reset($cmsPageStorageTransfers);
        if (
            !$cmsPageStorageTransfer instanceof CmsPageStorageTransfer
            || $cmsPageStorageTransfer->getUuid() !== $uuid
        ) {
            return null;
        }

        return $cmsPageStorageTransfer;
    }

    /**
     * {@inheritDoc}
     */
    public function searchCmsPages(
        string $searchString,
        string $localeName,
        string $storeName,
        array $requestParameters,
    ): array {
        $searchResult = $this->cmsPageSearchClient->search($searchString, $requestParameters);

        $cmsPages = $searchResult[static::SEARCH_RESULT_CMS_PAGES] ?? [];
        if ($cmsPages === []) {
            return ['transfers' => [], 'totalCount' => 0];
        }

        $totalCount = (int)$searchResult[static::SEARCH_RESULT_PAGINATION]->getNumFound();

        $cmsPageStorageTransfers = $this->cmsStorageClient->getCmsPageStorageByIds(
            $this->extractCmsPageIds($cmsPages),
            $localeName,
            $storeName,
        );

        return [
            'transfers' => array_values($cmsPageStorageTransfers),
            'totalCount' => $totalCount,
        ];
    }

    /**
     * @param array<array<string, mixed>> $cmsPages
     *
     * @return array<int, int>
     */
    protected function extractCmsPageIds(array $cmsPages): array
    {
        $cmsPageIds = [];
        foreach ($cmsPages as $cmsPage) {
            $cmsPageIds[] = (int)$cmsPage[static::ID_CMS_PAGE];
        }

        return $cmsPageIds;
    }
}
