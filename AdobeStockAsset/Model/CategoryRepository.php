<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockAsset\Model;

use Magento\AdobeStockAsset\Model\ResourceModel\Category as ResourceModel;
use Magento\AdobeStockAsset\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\AdobeStockAsset\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\AdobeStockAssetApi\Api\CategoryRepositoryInterface;
use Magento\AdobeStockAssetApi\Api\Data\CategoryInterface;
use Magento\AdobeStockAssetApi\Api\Data\CategorySearchResultsInterface;
use Magento\AdobeStockAssetApi\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CategoryRepository
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var CategoryFactory
     */
    private $factory;

    /**
     * @var CategoryCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CategorySearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * CategoryRepository constructor.
     *
     * @param ResourceModel                         $resource
     * @param CategoryCollectionFactory             $collectionFactory
     * @param CategoryFactory                       $factory
     * @param JoinProcessorInterface                $joinProcessor
     * @param CollectionProcessorInterface          $collectionProcessor
     * @param CategorySearchResultsInterfaceFactory $searchResultFactory
     */
    public function __construct(
        ResourceModel $resource,
        CategoryCollectionFactory $collectionFactory,
        CategoryFactory $factory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        CategorySearchResultsInterfaceFactory $searchResultFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CategoryInterface $item): CategoryInterface
    {
        $this->resource->save($item);

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function delete(CategoryInterface $item): void
    {
        $this->resource->delete($item);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : CategorySearchResultsInterface
    {
        /** @var CategoryCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process(
            $collection,
            CategoryInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CategorySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id) : CategoryInterface
    {
        $item = $this->factory->create();
        $this->resource->load($item, $id);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $item;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): void
    {
        $this->delete($this->getById($id));
    }
}
