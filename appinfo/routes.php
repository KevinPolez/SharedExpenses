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

$app = new \OCA\SharedExpenses\AppInfo\Application();

$app->registerRoutes($this, [
    'resources' => [
      'reckoning' => ['url' => '/reckonings'],
      'reckoning_api' => ['url' => '/api/0.1/reckonings'],
      'line' => ['url' => '/reckonings/{reckoningId}/lines']
    ],
    'routes' => [
      ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
      ['name' => 'reckoning_api#preflighted_cors', 'url' => '/api/0.1/{path}',
       'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
  ]
]);
