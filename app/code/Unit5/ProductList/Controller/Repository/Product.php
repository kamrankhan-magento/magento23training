<?php

namespace Unit5\ProductList\Controller\Repository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Product extends Action {
	
	/**
	 * @var ProductRepositoryInterface
	 */
	private $productRepository;
	
	/**
	 * @var SearchCriteriaBuilder
	 */
	private $searchCriteriaBuilder;
	
	/**
	 * Product constructor.
	 * @param Context $context
	 * @param ProductRepositoryInterface $productRepository
	 * @param SearchCriteriaBuilder $searchCriteriaBuilder
	 * @param FilterBuilder $filterBuilder
	 * @param SortOrderBuilder $sortOrderBuilder
	 */
	public function __construct(Context $context, 
			ProductRepositoryInterface $productRepository, 
			SearchCriteriaBuilder $searchCriteriaBuilder,
			\Magento\Framework\Api\FilterBuilder $filterBuilder,
			\Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder) {
		parent::__construct ( $context );
		$this->productRepository = $productRepository;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->filterBuilder = $filterBuilder;
		$this->sortOrderBuilder = $sortOrderBuilder;
	}
	
	public function execute() {
		$this->getResponse ()->setHeader ( 'Content-Type', 'text/plain' );
		$products = $this->getProductsFromRepository ();
		foreach ( $products as $product ) {
			$this->outputProduct ( $product );
		}
	}
	
	/**
	 * @return ProductInterface[]
	 */
	private function getProductsFromRepository() {
		$this->setProductTypeFilter();
		$this->setProductNameFilter();
		$this->setProductPaging();
		$criteria = $this->searchCriteriaBuilder->create ();
		$products = $this->productRepository->getList ( $criteria );
		return $products->getItems ();
	}
	
	private function setProductPaging() 	{
		$sortOrder = $this->sortOrderBuilder
			->setField('entity_id')
			->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC)
			->create();
		$this->searchCriteriaBuilder->addSortOrder($sortOrder);
		$this->searchCriteriaBuilder->setPageSize(6);
		$this->searchCriteriaBuilder->setCurrentPage(1);
	}
	
	private function setProductTypeFilter(){
		$configProductFilter = $this->filterBuilder
			->setField('type_id')
			->setValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
			->setConditionType('eq')
			->create();
		$this->searchCriteriaBuilder->addFilters([$configProductFilter]);
		
	}
	
	private function setProductNameFilter()	{
		$nameFilter[] = $this->filterBuilder
		->setField('name')
		->setValue('M%')
		->setConditionType('like')
		->create();
		$this->searchCriteriaBuilder->addFilters($nameFilter);
	}
	
	
	private function outputProduct(ProductInterface $product) {
		$this->getResponse ()->appendBody ( sprintf ( "%s - %s (%d)\n", $product->getName (), $product->getSku (), $product->getId () ) );
	}
	
}