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

namespace OCA\SharedExpenses\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Line extends Entity implements JsonSerializable {

    protected $reckoningId;
    protected $created;
    protected $userId;
    protected $amount;
    protected $when;
    protected $who;
    protected $why;
    protected $for;

    function __construct() {
      $this->for = array();
    }

    public function jsonSerialize() {
        // workarround for a new line (we don't want send null value, but an empty array)
        if ( $this->for === null ) $this->for = array();

        return [
            'id' => $this->id,
            'reckoningId' => $this->reckoningId,
            'created' => $this->created,
            'userId' => $this->userId,
            'amount' => $this->amount,
            'when' => $this->when,
            'who' => $this->who,
            'why' => $this->why,
            'for' => $this->for
        ];
    }
}
