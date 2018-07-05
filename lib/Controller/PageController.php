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

use OCA\SharedExpenses\Db\Reckoning;
//use OCA\SharedExpenses\Db\ReckoningMapper;
use OCA\SharedExpenses\Db\Line;
//use OCA\SharedExpenses\Db\LineMapper;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\Security\ISecureRandom;
use OCP\User;

class PageController extends Controller {

	private $userId;
	//private $reckoningMapper;
	//private $lineMapper;
	private $urlGenerator;
	private $userMgr;
	private $trans;

 /**
  * PageController constructor.
	* @param string $appName
	* @param IRequest $request
	* @param IUserManager $userMgr
	* @param IL10N $trans
  * @param IURLGenerator $urlGenerator
	* @param string $userId
	//* @param ReckoningMapper $reckoningMapper
	//* @param LineMapper $lineMapper
	*/
	public function __construct(
	    $AppName,
			IRequest $request,
			IUserManager $userMgr,
			IL10N $trans,
			IURLGenerator $urlGenerator,
			$UserId
			//ReckoningMapper $reckoningMapper,
			//LineMapper $lineMapper
			){
		parent::__construct($AppName, $request);
		$this->userMgr = $userMgr;
		$this->userId = $UserId;
		$this->trans = $trans;
		$this->urlGenerator = $urlGenerator;
		//$this->reckoningMapper = $reckoningMapper;
		//$this->lineMapper = $lineMapper;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new TemplateResponse('sharedexpenses', 'index');  // templates/index.php
	}

}
