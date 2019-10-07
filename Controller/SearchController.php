<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 26/09/2019
 * Time: 16:20
 */

namespace TntSearch\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Thelia;
use Thelia\Model\Brand;
use Thelia\Model\Category;
use Thelia\Model\Content;
use Thelia\Model\Folder;
use Thelia\Model\LangQuery;
use TntSearch\TntSearch;

class SearchController extends BaseAdminController
{
    /**
     * @return \Thelia\Core\HttpFoundation\Response
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function searchAdminAction()
    {
        $term = $this->getRequest()->get('search_term');

        $tnt = TntSearch::getTntSearch();

        $langs = LangQuery::create()->filterByActive(1)->find();

        $tnt->selectIndex('customer.index');
        $customers = $tnt->search($term);

        $tnt->selectIndex('order.index');
        $orders = $tnt->search($term);
        $products = $categories = $folders = $contents = $brands = [];

        foreach ($langs as $lang){
            $tnt->selectIndex('product_'.$lang->getLocale().'.index');
            $products += $tnt->search($term)['ids'];

            $tnt->selectIndex('category_'.$lang->getLocale().'.index');
            $categories += $tnt->search($term)['ids'];

            $tnt->selectIndex('folder_'.$lang->getLocale().'.index');
            $folders += $tnt->search($term)['ids'];

            $tnt->selectIndex('content_'.$lang->getLocale().'.index');
            $contents += $tnt->search($term)['ids'];

            $tnt->selectIndex('brand_'.$lang->getLocale().'.index');
            $brands += $tnt->search($term)['ids'];
        }
        if (version_compare(Thelia::THELIA_VERSION, '2.4.0-alpha2', 'lt')){
            return $this->render('tntSearch/search2_3_4', $this->getSearchResult(
                $brands,
                $categories,
                $contents,
                $folders,
                $products,
                $orders,
                $customers
            ));
        }

        return $this->render('tntSearch/search', $this->getSearchResult(
            $brands,
            $categories,
            $contents,
            $folders,
            $products,
            $orders,
            $customers
        ));

    }

    protected function getSearchResult($brands, $categories, $contents, $folders, $products, $orders, $customers)
    {
        return ['brands' => implode(",",  array_unique($brands)),
                'categories' => implode(",", array_unique($categories)),
                'contents' => implode(",", array_unique($contents)),
                'folders' => implode(",", array_unique($folders)),
                'products' => implode(",", array_unique($products)),
                'orders' => implode(",", $orders['ids']),
                'customers' => implode(",", $customers['ids'])];
    }
}