<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace TntSearch;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\Yaml\Yaml;
use Thelia\Model\LangQuery;
use Thelia\Module\BaseModule;

class TntSearch extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'tntsearch';

    public function postActivation(ConnectionInterface $con = null)
    {


        $langs = LangQuery::create()->filterByActive(1)->find();

        $tnt = self::getTntSearch();

        $indexer = $tnt->createIndex('customer.index');
        $indexer->query('SELECT id, ref, firstname, lastname, email FROM customer;');

        $indexer->run();

        $indexer = $tnt->createIndex('order.index');
        $indexer->query('SELECT o.id as id, 
                                o.ref as ref, 
                                c.ref as customer_ref, 
                                c.firstname as firstname, 
                                c.lastname as lastname, 
                                c.email as email, 
                                o.invoice_ref as invoice_ref, 
                                o.transaction_ref as transaction_ref, 
                                o.delivery_ref as delivery_ref 
                                FROM `order` as o LEFT JOIN customer as c ON o.customer_id = c.id;');
        $indexer->run();

        foreach ($langs as $lang){

            $indexer = $tnt->createIndex('product_'.$lang->getLocale().'.index');
            $indexer->query('SELECT p.id as id, 
                                p.ref as ref, 
                                pi.title as title, 
                                pi.chapo as chapo, 
                                pi.description as description, 
                                pi.postscriptum as postscriptum
                                FROM product as p LEFT JOIN product_i18n as pi ON p.id = pi.id 
                                WHERE pi.locale=\''.$lang->getLocale().'\';');
            $indexer->run();

            $indexer = $tnt->createIndex('category_'.$lang->getLocale().'.index');
            $indexer->query('SELECT c.id as id, 
                                ci.title as title, 
                                ci.chapo as chapo, 
                                ci.description as description, 
                                ci.postscriptum as postscriptum
                                FROM category as c LEFT JOIN category_i18n as ci ON c.id = ci.id
                                WHERE ci.locale=\''.$lang->getLocale().'\';');
            $indexer->run();

            $indexer = $tnt->createIndex('content_'.$lang->getLocale().'.index');
            $indexer->query('SELECT c.id as id, 
                                ci.title as title, 
                                ci.chapo as chapo, 
                                ci.description as description, 
                                ci.postscriptum as postscriptum
                                FROM content as c LEFT JOIN content_i18n as ci ON c.id = ci.id
                                WHERE ci.locale=\''.$lang->getLocale().'\';');
            $indexer->run();

            $indexer = $tnt->createIndex('folder_'.$lang->getLocale().'.index');
            $indexer->query('SELECT f.id as id, 
                                fi18n.title as title, 
                                fi18n.chapo as chapo, 
                                fi18n.description as description, 
                                fi18n.postscriptum as postscriptum
                                FROM folder as f LEFT JOIN folder_i18n as fi18n ON f.id = fi18n.id
                                WHERE fi18n.locale=\''.$lang->getLocale().'\';');
            $indexer->run();

            $indexer = $tnt->createIndex('brand_'.$lang->getLocale().'.index');
            $indexer->query('SELECT b.id as id,  
                                bi.title as title, 
                                bi.chapo as chapo, 
                                bi.description as description, 
                                bi.postscriptum as postscriptum
                                FROM brand as b LEFT JOIN brand_i18n as bi ON b.id = bi.id
                                WHERE bi.locale=\''.$lang->getLocale().'\';');
            $indexer->run();
        }
    }

    protected static function getEnvParameters()
    {
        $parameters = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'SYMFONY_')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
            }
        }
        return $parameters;
    }

    public static function getTntSearch()
    {
        $configFile = THELIA_CONF_DIR . "database.yml";

        $propelParameters = Yaml::parse(file_get_contents($configFile))['database']['connection'];

        $driver = $propelParameters['driver'];
        $user = $propelParameters['user'];
        $password = $propelParameters['password'];

        $explodeDns = explode(';', $propelParameters['dsn']);
        $arrayDns = [];
        foreach ($explodeDns as $param){
            $arrayDns[explode('=', $param)[0]] = explode('=', $param)[1];
        }
        $host = $arrayDns['mysql:host'];
        $database = $arrayDns['dbname'];

        if (!file_exists(THELIA_MODULE_DIR. "TntSearch" .DS. "Indexes")){
            mkdir(THELIA_MODULE_DIR. "TntSearch" .DS. "Indexes");
        }

        $config = [
            'driver' => $driver,
            'host' => $host,
            'database' => $database,
            'username' => $user,
            'password' => $password,
            'storage'  => THELIA_MODULE_DIR. "TntSearch" .DS. "Indexes",
        ];

        $tnt = new \TeamTNT\TNTSearch\TNTSearch();
        $tnt->loadConfig($config);

        return $tnt;
    }
}