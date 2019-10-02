<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 26/09/2019
 * Time: 16:20
 */

namespace TntSearch\Controller;


use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Model\LangQuery;
use TntSearch\TntSearch;

class SearchController extends BaseAdminController
{
    /**
     * @return \Thelia\Core\HttpFoundation\Response
     * @throws \TeamTNT\TNTSearch\Exceptions\IndexNotFoundException
     */
    public function searchAction()
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

        return $this->render('tntSearch/search', [
            'brands' => implode(",",  array_unique($brands)),
            'categories' => implode(",", array_unique($categories)),
            'contents' => implode(",", array_unique($contents)),
            'folders' => implode(",", array_unique($folders)),
            'products' => implode(",", array_unique($products)),
            'orders' => implode(",", $orders['ids']),
            'customers' => implode(",", $customers['ids']),
        ]);
    }
}