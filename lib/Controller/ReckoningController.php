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

namespace OCA\SharedExpenses\Controller;

use Exception;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\SharedExpenses\Service\ReckoningService;

class ReckoningController extends Controller {

     private $service;
     private $userId;

     use Errors;

     public function __construct($AppName, IRequest $request, ReckoningService $service, $UserId){
         parent::__construct($AppName, $request);
         $this->service = $service;
         $this->userId = $UserId;
     }

     /**
      * @NoAdminRequired
      */
     public function index() {
        $reckonings = $this->service->findAll($this->userId);
        return new DataResponse($reckonings);
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     public function show($id) {
       return $this->handleNotFound(function () use ($id) {
         return $this->service->find($id, $this->userId);
       });
     }

     /**
      * @NoAdminRequired
      *
      * @param string $title
      * @param string $description
      */
     public function create($title, $description) {
       return $this->service->create($title, $description, $this->userId);
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      * @param string $title
      * @param string $description
      */
     public function update($id, $title, $description) {
       return $this->handleNotFound(function () use ($id, $title, $description) {
         return $this->service->update($id, $title, $description, $this->userId);
       });
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     public function destroy($id) {
       return $this->handleNotFound(function () use ($id) {
         return $this->service->delete($id, $this->userId);
       });
     }

 }
