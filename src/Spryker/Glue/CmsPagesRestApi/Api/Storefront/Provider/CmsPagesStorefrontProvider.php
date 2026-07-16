<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CmsPagesRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CmsPages\CmsPagesPaginationStorefrontObject;
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

    /**
     * @uses \Spryker\Shared\ContentBanner\ContentBannerConfig::TWIG_FUNCTION_NAME
     */
    protected const string CONTENT_BANNER_TWIG_FUNCTION_NAME = 'content_banner';

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
            $resources[0]->pagination = CmsPagesPaginationStorefrontObject::fromArray($this->calculatePagination($offset, $limit, $totalCount));
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
        $resource->contentProductAbstractListKeys = $this->extractContentKeys($cmsPageStorageTransfer, static::CONTENT_PRODUCT_ABSTRACT_LIST_TWIG_FUNCTION_NAME);
        $resource->contentBannerKeys = $this->extractContentKeys($cmsPageStorageTransfer, static::CONTENT_BANNER_TWIG_FUNCTION_NAME);

        return $resource;
    }

    /**
     * @return array<int, string>
     */
    protected function extractContentKeys(CmsPageStorageTransfer $cmsPageStorageTransfer, string $twigFunctionName): array
    {
        $contentWidgetParameterMap = $cmsPageStorageTransfer->getContentWidgetParameterMap();
        $keys = $contentWidgetParameterMap[$twigFunctionName] ?? [];

        return is_array($keys) ? array_values($keys) : [];
    }
}
