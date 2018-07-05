<?php
/**
 * @copyright Copyright (c) 2018 Kevin Polez <kevin@hypatie.xyz>
 *
 * @author Kevin Polez <kevin@hypatie.xyz>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\SharedExpenses\AppInfo;

use OCA\SharedExpenses\Controller\PageController;
use OCP\AppFramework\App;
use OCP\IContainer;

class Application extends App {

    /**
     * Application constructor.
     * @param array $urlParams
     */
    public function __construct(array $urlParams = array()) {
        parent::__construct('sharedexpenses', $urlParams);

        $container = $this->getContainer();
        $server = $container->getServer();

        /**
         * Controllers
         */
        $container->registerService('PageController', function (IContainer $c) {
          return new PageController(
            $c->query('AppName'),
            $c->query('Request'),
            $c->query('UserManager'),
            $c->query('L10N'),
            $c->query('ServerContainer')->getURLGenerator(),
            $c->query('UserId')
            //$c->query('ReckoningMapper'),
            //$c->query('LineMapper')
          );
        });

        $container->registerService('UserManager', function (IContainer $c) {
        			return $c->query('ServerContainer')->getUserManager();
    		});

        $container->registerService('L10N', function (IContainer $c) {
        			return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });
    }

    /**
     * Register navigation entry for main navigation.
     */
    public function registerNavigationEntry() {
        $container = $this->getContainer();
        $container->query('OCP\INavigationManager')->add(function () use ($container) {
            $urlGenerator = $container->query('OCP\IURLGenerator');
            $l10n = $container->query('OCP\IL10N');
            return [
                'id' => 'sharedexpenses',
                'order' => 77,
                'href' => $urlGenerator->linkToRoute('sharedexpenses.page.index'),
                //'icon' => $urlGenerator->imagePath('SharedExpenses', 'app.svg'),
                'name' => $l10n->t('SharedExpenses')
            ];
        });
    }
}
