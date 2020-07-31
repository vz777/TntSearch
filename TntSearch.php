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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Thelia\Model\LangQuery;
use Thelia\Module\BaseModule;

class TntSearch extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'tntsearch';

    const INDEXES_DIR = THELIA_MODULE_DIR . "TntSearch" . DS . "Indexes";

    public function postActivation(ConnectionInterface $con = null)
    {
        if (!is_dir($this::INDEXES_DIR)) {
            $this::generateIndexes();
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
        foreach ($explodeDns as $param) {
            $value = explode('=', $param);
            $arrayDns[$value[0]] = $value[1];
        }
        $host = $arrayDns['mysql:host'];
        $database = $arrayDns['dbname'];

        if (!is_dir(self::INDEXES_DIR)) {
            $fs = new Filesystem();
            $fs->mkdir(self::INDEXES_DIR);
        }

        $config = [
            'driver' => $driver,
            'host' => $host,
            'database' => $database,
            'username' => $user,
            'password' => $password,
            'storage' => THELIA_MODULE_DIR . "TntSearch" . DS . "Indexes",
        ];

        $tnt = new \TeamTNT\TNTSearch\TNTSearch();
        $tnt->loadConfig($config);

        return $tnt;
    }

    public static function generateIndexes()
    {
        $langs = LangQuery::create()->filterByActive(1)->find();

        $tnt = self::getTntSearch();

        $indexer = $tnt->createIndex('customer.index');
        $indexer->query('SELECT id, ref, firstname, lastname, email FROM customer;');

        $indexer->run();

        $indexer = $tnt->createIndex('order.index');
        $indexer->query('SELECT o.id AS id,
                                o.ref AS ref,
                                c.ref AS customer_ref,
                                c.firstname AS firstname,
                                c.lastname AS lastname,
                                c.email AS email,
                                o.invoice_ref AS invoice_ref,
                                o.transaction_ref AS transaction_ref,
                                o.delivery_ref AS delivery_ref
                                FROM `order` AS o LEFT JOIN customer AS c ON o.customer_id = c.id;');
        $indexer->run();

        foreach ($langs as $lang) {

            $indexer = $tnt->createIndex('product_' . $lang->getLocale() . '.index');
            $indexer->query('SELECT p.id AS id, 
                                p.ref AS ref, 
                                pi.title AS title, 
                                pi.chapo AS chapo, 
                                pi.description AS description, 
                                pi.postscriptum AS postscriptum
                                FROM product AS p LEFT JOIN product_i18n AS pi ON p.id = pi.id 
                                WHERE pi.locale=\'' . $lang->getLocale() . '\';');
            $indexer->run();

            $indexer = $tnt->createIndex('category_' . $lang->getLocale() . '.index');
            $indexer->query('SELECT c.id AS id,
                                ci.title AS title,
                                ci.chapo AS chapo,
                                ci.description AS description,
                                ci.postscriptum AS postscriptum
                                FROM category AS c LEFT JOIN category_i18n AS ci ON c.id = ci.id
                                WHERE ci.locale=\'' . $lang->getLocale() . '\';');
            $indexer->run();

            $indexer = $tnt->createIndex('content_' . $lang->getLocale() . '.index');
            $indexer->query('SELECT c.id AS id,
                                ci.title AS title,
                                ci.chapo AS chapo,
                                ci.description AS description,
                                ci.postscriptum AS postscriptum
                                FROM content AS c LEFT JOIN content_i18n AS ci ON c.id = ci.id
                                WHERE ci.locale=\'' . $lang->getLocale() . '\';');
            $indexer->run();

            $indexer = $tnt->createIndex('folder_' . $lang->getLocale() . '.index');
            $indexer->query('SELECT f.id AS id,
                                fi18n.title AS title,
                                fi18n.chapo AS chapo,
                                fi18n.description AS description,
                                fi18n.postscriptum AS postscriptum
                                FROM folder AS f LEFT JOIN folder_i18n AS fi18n ON f.id = fi18n.id
                                WHERE fi18n.locale=\'' . $lang->getLocale() . '\';');
            $indexer->run();

            $indexer = $tnt->createIndex('brand_' . $lang->getLocale() . '.index');
            $indexer->query('SELECT b.id AS id,
                                bi.title AS title,
                                bi.chapo AS chapo,
                                bi.description AS description,
                                bi.postscriptum AS postscriptum
                                FROM brand AS b LEFT JOIN brand_i18n AS bi ON b.id = bi.id
                                WHERE bi.locale=\'' . $lang->getLocale() . '\';');
            $indexer->run();
        }
    }
}