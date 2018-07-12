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
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;

use OCA\SharedExpenses\Service\ParticipantService;

class ParticipantController extends Controller {

    private $service;
    private $userId;

    use Errors;

    public function __construct($AppName, IRequest $request, ParticipantService $service, $UserId) {
      parent::__construct($AppName, $request);
      $this->service = $service;
      $this->userId = $UserId;
    }

    /**
     * @NoAdminRequired
     */
    public function index() {
        $participants = $this->service->findAll($this->userId);
        return new DataResponse($participants);
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
     * @param int $reckoningId
     * @param string $name
     * @param int $percent
     * @return Reckoning
     */
    public function create($reckoningId, $name, $percent) {
       return $this->service->create($reckoningId, $name, $percent, $this->userId);
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     * @param int $reckoningId
     * @param string $name
     * @param int $percent
     * @return Reckoning
     */
    public function update($id, $reckoningId, $name, $percent) {
        return $this->handleNotFound(function () use ($id, $reckoningId, $name, $percent) {
          return $this->service->update($id, $reckoningId, $name, $percent, $this->userId);
        });
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     * @return Reckoning
     */
    public function destroy($id, $reckoningId) {
      return $this->handleNotFound(function () use ($id, $reckoningId) {
        $reckoning = $this->service->delete($id, $reckoningId, $this->userId);
        return $reckoning;
      });
    }
}
