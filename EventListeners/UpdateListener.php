<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 27/09/2019
 * Time: 14:35
 */

namespace TntSearch\EventListeners;



use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Model\Brand;
use Thelia\Model\Category;
use Thelia\Model\Content;
use Thelia\Model\Customer;
use Thelia\Model\Event\BrandEvent;
use Thelia\Model\Event\CategoryEvent;
use Thelia\Model\Event\ContentEvent;
use Thelia\Model\Event\CustomerEvent;
use Thelia\Model\Event\FolderEvent;
use Thelia\Model\Event\OrderEvent;
use Thelia\Model\Event\ProductEvent;
use Thelia\Model\Folder;
use Thelia\Model\LangQuery;
use Thelia\Model\Order;
use Thelia\Model\Product;
use TntSearch\TntSearch;

class UpdateListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CustomerEvent::POST_SAVE => 'createCustomerIndex',
            CustomerEvent::POST_UPDATE => 'updateCustomerIndex',
            CustomerEvent::PRE_DELETE => 'deleteCustomerIndex',

            OrderEvent::POST_SAVE => 'createOrderIndex',
            OrderEvent::POST_UPDATE => 'updateOrderIndex',
            OrderEvent::PRE_DELETE => 'deleteOrderIndex',

            ProductEvent::POST_SAVE => 'createProductIndex',
            ProductEvent::POST_UPDATE => 'updateProductIndex',
            ProductEvent::PRE_DELETE => 'deleteProductIndex',

            CategoryEvent::POST_SAVE => 'createCategoryIndex',
            CategoryEvent::POST_UPDATE => 'updateCategoryIndex',
            CategoryEvent::PRE_DELETE => 'deleteCategoryIndex',

            ContentEvent::POST_SAVE => 'createContentIndex',
            ContentEvent::POST_UPDATE => 'updateContentIndex',
            ContentEvent::PRE_DELETE => 'deleteContentIndex',

            FolderEvent::POST_SAVE => 'createFolderIndex',
            FolderEvent::POST_UPDATE => 'updateFolderIndex',
            FolderEvent::PRE_DELETE => 'deleteFolderIndex',

            BrandEvent::POST_SAVE => 'createBrandIndex',
            BrandEvent::POST_UPDATE => 'updateBrandIndex',
            BrandEvent::PRE_DELETE => 'deleteBrandIndex',
        ];
    }


    /**
     * @param CustomerEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateCustomerIndex(CustomerEvent $event)
    {
        $customer = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("customer.index");

        $index = $tnt->getIndex();

        $index->update($customer->getId(),$this->getCustomerData($customer));
    }
    /**
     * @param CustomerEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createCustomerIndex(CustomerEvent $event)
    {
        $customer = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("customer.index");

        $index = $tnt->getIndex();

        $index->insert($this->getCustomerData($customer));
    }
    /**
     * @param CustomerEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteCustomerIndex(CustomerEvent $event)
    {
        $customer = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("customer.index");

        $index = $tnt->getIndex();

        $index->delete($customer->getId());
    }


    /**
     * @param OrderEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateOrderIndex(OrderEvent $event)
    {
        $order = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("order.index");

        $index = $tnt->getIndex();

        $index->update($order->getId(), $this->getOrderData($order));
    }
    /**
     * @param OrderEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createOrderIndex(OrderEvent $event)
    {
        $order = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("order.index");

        $index = $tnt->getIndex();

        $index->insert($this->getOrderData($order));
    }
    /**
     * @param OrderEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteOrderIndex(OrderEvent $event)
    {
        $order = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("order.index");

        $index = $tnt->getIndex();

        $index->delete($order->getId());
    }


    /**
     * @param ProductEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateProductIndex(ProductEvent $event)
    {
        $product = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("product_".$product->getLocale().".index");

        $index = $tnt->getIndex();
        $index->update($product->getId(), $this->getProductData($product));
    }
    /**
     * @param ProductEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createProductIndex(ProductEvent $event)
    {
        $product = $event->getModel();
        $langs = LangQuery::create()->filterByByDefault(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("product_".$lang->getLocale().".index");
            $product->setLocale($lang->getLocale());
            $index = $tnt->getIndex();
            $index->insert($this->getProductData($product));
        }
    }
    /**
     * @param ProductEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteProductIndex(ProductEvent $event)
    {
        $product = $event->getModel();
        $langs = LangQuery::create()->filterByActive(1)->find();

        $tnt = TntSearch::getTntSearch();
        foreach ($langs as $lang){
            $tnt->selectIndex("product_".$lang->getLocale().".index");

            $index = $tnt->getIndex();

            $index->delete($product->getId());
        }
    }


    /**
     * @param CategoryEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateCategoryIndex(CategoryEvent $event)
    {
        $category = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("category_".$category->getLocale().".index");

        $index = $tnt->getIndex();
        $index->update($category->getId(), $this->getCategoryData($category));
    }
    /**
     * @param CategoryEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createCategoryIndex(CategoryEvent $event)
    {
        $category = $event->getModel();
        $langs = LangQuery::create()->filterByByDefault(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("category_".$lang->getLocale().".index");
            $index = $tnt->getIndex();
            $category->setLocale($lang->getLocale());
            $index->insert($this->getCategoryData($category));
        }
    }
    /**
     * @param CategoryEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteCategoryIndex(CategoryEvent $event)
    {

        $category = $event->getModel();
        $langs = LangQuery::create()->filterByActive(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("category_".$lang->getLocale().".index");

            $index = $tnt->getIndex();

            $index->delete($category->getId());
        }
    }


    /**
     * @param FolderEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateFolderIndex(FolderEvent $event)
    {
        $folder = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("folder_".$folder->getLocale().".index");

        $index = $tnt->getIndex();
        $index->update($folder->getId(), $this->getFolderData($folder));
    }
    /**
     * @param FolderEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createFolderIndex(FolderEvent $event)
    {
        $folder = $event->getModel();
        $langs = LangQuery::create()->filterByByDefault(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("folder_".$lang->getLocale().".index");
            $index = $tnt->getIndex();
            $folder->setLocale($lang->getLocale());
            $index->insert($this->getFolderData($folder));
        }
    }
    /**
     * @param FolderEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteFolderIndex(FolderEvent $event)
    {

        $folder = $event->getModel();
        $langs = LangQuery::create()->filterByActive(1)->find();
        $tnt = TntSearch::getTntSearch();
        foreach ($langs as $lang){
            $tnt->selectIndex("folder_".$lang->getLocale().".index");

            $index = $tnt->getIndex();

            $index->delete($folder->getId());
        }
    }


    /**
     * @param ContentEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateContentIndex(ContentEvent $event)
    {
        $content = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("content_".$content->getLocale().".index");

        $index = $tnt->getIndex();
        $index->update($content->getId(), $this->getContentData($content));
    }
    /**
     * @param ContentEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createContentIndex(ContentEvent $event)
    {
        $content = $event->getModel();
        $langs = LangQuery::create()->filterByByDefault(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("content_".$lang->getLocale().".index");
            $index = $tnt->getIndex();
            $content->setLocale($lang->getLocale());
            $index->insert($this->getContentData($content));
        }
    }
    /**
     * @param ContentEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteContentIndex(ContentEvent $event)
    {

        $content = $event->getModel();
        $langs = LangQuery::create()->filterByActive(1)->find();
        $tnt = TntSearch::getTntSearch();
        foreach ($langs as $lang){
            $tnt->selectIndex("content_".$lang->getLocale().".index");

            $index = $tnt->getIndex();

            $index->delete($content->getId());
        }
    }


    /**
     * @param BrandEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function updateBrandIndex(BrandEvent $event)
    {
        $brand = $event->getModel();

        $tnt = TntSearch::getTntSearch();
        $tnt->selectIndex("brand_".$brand->getLocale().".index");

        $index = $tnt->getIndex();
        $index->update($brand->getId(), $this->getBrandData($brand));
    }
    /**
     * @param BrandEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function createBrandIndex(BrandEvent $event)
    {
        $brand = $event->getModel();
        $langs = LangQuery::create()->filterByByDefault(1)->find();
        $tnt = TntSearch::getTntSearch();

        foreach ($langs as $lang){
            $tnt->selectIndex("brand_".$lang->getLocale().".index");
            $index = $tnt->getIndex();
            $brand->setLocale($lang->getLocale());
            $index->insert($this->getBrandData($brand));
        }
    }
    /**
     * @param BrandEvent $event
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function deleteBrandIndex(BrandEvent $event)
    {

        $brand = $event->getModel();
        $langs = LangQuery::create()->filterByActive(1)->find();
        $tnt = TntSearch::getTntSearch();
        foreach ($langs as $lang){
            $tnt->selectIndex("brand_".$lang->getLocale().".index");

            $index = $tnt->getIndex();

            $index->delete($brand->getId());
        }
    }

    protected function getCustomerData(Customer $customer)
    {
        return [
            'id' => $customer->getId(),
            'ref' => $customer->getRef(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'email' => $customer->getEmail()
        ];
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getOrderData(Order $order)
    {
        $customer = $order->getCustomer();
        return [
            'id' => $order->getId(),
            'ref' => $order->getRef(),
            'customer_ref' => $customer->getRef(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'email' => $customer->getEmail(),
            'invoice_ref' => $order->getInvoiceRef(),
            'transaction_ref' => $order->getTransactionRef(),
            'delivery_ref' => $order->getDeliveryRef()
        ];
    }

    protected function getProductData(Product $product)
    {
        return [
            'id' => $product->getId(),
            'ref' => $product->getRef(),
            'title' => $product->getTitle(),
            'chapo' => $product->getChapo(),
            'description' => $product->getDescription(),
            'postscriptum' => $product->getPostscriptum()
        ];
    }

    protected function getCategoryData(Category $category)
    {
        return [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'chapo' => $category->getChapo(),
            'description' => $category->getDescription(),
            'postscriptum' => $category->getPostscriptum()
        ];
    }

    protected function getFolderData(Folder $folder)
    {
        return [
            'id' => $folder->getId(),
            'title' => $folder->getTitle(),
            'chapo' => $folder->getChapo(),
            'description' => $folder->getDescription(),
            'postscriptum' => $folder->getPostscriptum()
        ];
    }

    protected function getContentData(Content $content)
    {
        return [
            'id' => $content->getId(),
            'title' => $content->getTitle(),
            'chapo' => $content->getChapo(),
            'description' => $content->getDescription(),
            'postscriptum' => $content->getPostscriptum()
        ];
    }

    protected function getBrandData(Brand $brand)
    {
        return [
            'id' => $brand->getId(),
            'title' => $brand->getTitle(),
            'chapo' => $brand->getChapo(),
            'description' => $brand->getDescription(),
            'postscriptum' => $brand->getPostscriptum()
        ];
    }
}