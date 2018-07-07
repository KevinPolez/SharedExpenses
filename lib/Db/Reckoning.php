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

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\AppFramework\Db\Entity;

class Reckoning extends Entity implements JsonSerializable {

    protected $modified;
    protected $title;
    //protected $hash;
    protected $description;
    protected $owner;
    protected $created;
    protected $lines;

    function __contruct() {
      parent::__construct();
      $this->addType('modified', 'integer');
      $this->lines = array();
    }

    function __construct($content)
    {
       $this->setId($content['id']);
       $this->setTitle($content['title']);
       $this->setModified($content['modified']);
       $this->setDescription($content['description']);
       $this->setCreated($content['created']);
       $this->setOwner($content['owner']);

       foreach ($content['lines'] as $l) {
         $line = new Line();
         $line->setId($l['id']);
         $line->setReckoningId($l['reckoningId']);
         $line->setAmount($l['amount']);
         $line->setWho($l['who']);
         $line->setWhy($l['why']);
         $line->setUserId($l['userId']);
         $line->setWhen($l['when']);
         $line->setCreated($l['created']);

         $this->lines[] = $line;
       }
    }

    /**
     * @param File $file
     * @return static
     */
    public static function fromFile(File $file, Folder $reckoningsFolder, $tags=[]){
        return new static(json_decode($file->getContent(), true));
    }

    private static function convertEncoding($str) {
        if(!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8');
        }
        return $str;
    }

    public function jsonSerialize() {
        // workarround for a new reckoning (we don't want send null value, but an empty array)
        if ( $this->lines === null ) $this->lines = array();

        // date format
        //this->created = date('Ymd H:i:s', $this->created);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'owner' => $this->owner,
            'created' => $this->created,
            'modified' => $this->modified,
            'description' => $this->description,
            'lines' => $this->lines
        ];
    }

    /**
     * add a new line, set id of this line
     * @param Line $line
     * @return Line
     */
    public function addLine(Line $line) {
      $line->setReckoningId($this->getId());
      $id = 0;
      if ( $lastLine = end($this->lines))
      {
          $id = $lastLine->getId()+1;
      }
      $line->setId($id);
      $this->lines[] = $line;
      return $line;
    }

    /**
     * Find a line on a reckoning
     * @param $lineId
     */
    public function findLine($lineId) {
        foreach ($this->lines as $line) {
          if ( $line->getId() == $lineId)
          return $line;
        }
        return null;
    }

    /**
     * Delete a line from a reckoning
     * @param Line $line
     */
    public function deleteLine(Line $line) {
        $key = array_search($line, $this->lines);
        if ( $key !== false ) unset($this->lines[$key]);
    }

    /**
     * Update a line
     * @param Line $line
     */
    public function updateLine(Line $line) {
        foreach ( $this->lines as $key => $l) {
          if ($l->getId() == $line->getId() ) {
            $this->lines[$key] = $line;
            break;
          }
        }
    }

}
