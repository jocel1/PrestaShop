<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace Tests\PrestaShopBundle\Utils;

use Composer\Script\Event;
use Doctrine\DBAL\DBALException;
use PrestaShopBundle\Install\DatabaseDump;
use PrestaShopBundle\Install\Install;

class Database
{
    /**
     * Create the initialize database used for test
     *
     * composer create-test-db could receive some args :
     * first arg in the HTTP_HOST we want to use
     * second arg control if we want to load the default prestashop modules
     *
     * @param Event|null $event
     */
    public static function createTestDB(Event $event = null)
    {
        define('_PS_IN_TEST_', true);
        define('__PS_BASE_URI__', '/');
        define('_PS_ROOT_DIR_', __DIR__ . '/../../..');

        $_SERVER['HTTP_HOST'] = 'localhost';
        $module_dir = _PS_ROOT_DIR_ . '/tests/resources/modules/';

        if ($event) {
            $args = $event->getArguments();
            if (!empty($args)) {
                $_SERVER['HTTP_HOST'] = $args[0];
                if (isset($args[1])) {
                    $module_dir = _PS_ROOT_DIR_ . '/modules/';
                }
            }
        }

        define('_PS_MODULE_DIR_', $module_dir);
        require_once(__DIR__ . '/../../../install-dev/init.php');

        $install = new Install();
        \DbPDOCore::createDatabase(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_, false);
        $install->clearDatabase();
        $install->installDatabase();
        $install->initializeTestContext();
        $install->installDefaultData('test_shop', false, false, true);
        $install->populateDatabase();
        $install->installCldrDatas();

        $install->configureShop(array(
            'admin_firstname' => 'puff',
            'admin_lastname' => 'daddy',
            'admin_password' => 'prestashop_demo',
            'admin_email' => 'demo@prestashop.com',
            'configuration_agrement' => true,
            'send_informations' => false,
        ));
        $install->installFixtures();
        $language = new \Language(1);
        \Context::getContext()->language = $language;
        $install->installModules();
        $install->installModulesAddons();
        $install->installTheme('classic');

        DatabaseDump::create();
    }

    /**
     * Restore the test database in its initial state from a dump generated during createTestDB
     *
     * @throws DBALException
     */
    public static function restoreTestDB()
    {
        if (!file_exists(sys_get_temp_dir() . '/' . 'ps_dump.sql')) {
            throw new DBALException('You need to run \'composer create-test-db\' to create the initial test database');
        }

        DatabaseDump::restoreDb();
    }
}
