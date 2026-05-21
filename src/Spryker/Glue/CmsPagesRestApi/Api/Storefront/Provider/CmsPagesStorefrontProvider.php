<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CmsPagesRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CmsPagesStorefrontResource;
use Generated\Shared\Transfer\CmsPageStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Glue\CmsPagesRestApi\Api\Storefront\Exception\CmsPagesExceptionFactory;
use Spryker\Glue\CmsPagesRestApi\Api\Storefront\Reader\CmsPageReaderInterface;

class CmsPagesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_UUID = 'uuid';

    protected const string QUERY_PARAM_SEARCH = 'q';

    /**
     * @uses \Spryker\Shared\ContentProduct\ContentProductConfig::TWIG_FUNCTION_NAME
     */
    protected const string CONTENT_PRODUCT_ABSTRACT_LIST_TWIG_FUNCTION_NAME = 'content_product_abstract_list';

    public function __construct(
        protected CmsPageReaderInterface $cmsPageReader,
        protected CmsPagesExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        $uuid = (string)$this->findUriVariable(static::URI_VAR_UUID);

        if ($uuid === '') {
            throw $this->exceptionFactory->createCmsPageNotFoundException();
        }

        $cmsPageStorageTransfer = $this->cmsPageReader->findCmsPageByUuid(
            $uuid,
            $this->getLocale()->getLocaleNameOrFail(),
            (string)$this->findStoreName(),
        );

        if ($cmsPageStorageTransfer === null) {
            throw $this->exceptionFactory->createCmsPageNotFoundException();
        }

        return $this->buildResource($cmsPageStorageTransfer);
    }

    /**
     * @return array<\Generated\Api\Storefront\CmsPagesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $searchString = (string)$this->getRequest()->query->get(static::QUERY_PARAM_SEARCH, '');

        $limit = $this->getPaginationLimit();
        $offset = $this->getPaginationOffset();

        ['transfers' => $transfers, 'totalCount' => $totalCount] = $this->cmsPageReader->searchCmsPages(
            $searchString,
            $this->getLocale()->getLocaleNameOrFail(),
            (string)$this->findStoreName(),
            $this->buildSearchPaginationRequestParams($limit),
        );

        $resources = [];
        foreach ($transfers as $cmsPageStorageTransfer) {
            $resources[] = $this->buildResource($cmsPageStorageTransfer);
        }

        if ($resources !== []) {
            // Consumed by Spryker\ApiPlatform\EventSubscriber\PaginationLinksResponseSubscriber
            // to emit JSON:API top-level pagination links.
            $resources[0]->pagination = $this->calculatePagination($offset, $limit, $totalCount);
        }

        return $resources;
    }

    protected function buildResource(CmsPageStorageTransfer $cmsPageStorageTransfer): CmsPagesStorefrontResource
    {
        $resource = new CmsPagesStorefrontResource();
        $resource->uuid = $cmsPageStorageTransfer->getUuid();
        $resource->name = $cmsPageStorageTransfer->getName();
        $resource->url = $cmsPageStorageTransfer->getUrl();
        $resource->validTo = $cmsPageStorageTransfer->getValidTo();
        $resource->isSearchable = $cmsPageStorageTransfer->getIsSearchable();
        $resource->contentProductAbstractListKeys = $this->extractContentProductAbstractListKeys($cmsPageStorageTransfer);

        return $resource;
    }

    /**
     * The legacy `ContentProductAbstractListByCmsPageResourceRelationshipPlugin` reads the keys for
     * the `content_product_abstract_list` twig function from `contentWidgetParameterMap`. The same
     * keys feed the API Platform `content-product-abstract-lists` include via the array
     * `uriVariableMappings`.
     *
     * @return array<int, string>
     */
    protected function extractContentProductAbstractListKeys(CmsPageStorageTransfer $cmsPageStorageTransfer): array
    {
        $contentWidgetParameterMap = $cmsPageStorageTransfer->getContentWidgetParameterMap();
        $keys = $contentWidgetParameterMap[static::CONTENT_PRODUCT_ABSTRACT_LIST_TWIG_FUNCTION_NAME] ?? [];

        return is_array($keys) ? array_values($keys) : [];
    }
}
