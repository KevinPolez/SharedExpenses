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
    protected $description;
    protected $owner;
    protected $created;
    protected $lines;
    protected $participants;

    function __contruct() {
        parent::__construct();
        $this->addType('modified', 'integer');
        $this->lines = array();
        $this->participants = array();
    }

    /**
     * Constructor
     * Read a json structure and create object
     */
    function __construct($content)
    {
         $this->setId($content['id']);
         $this->setTitle($content['title']);
         $this->setModified($content['modified']);
         $this->setDescription($content['description']);
         $this->setCreated($content['created']);
         $this->setOwner($content['owner']);
         $this->lines = array();
         $this->participants = array();

         if ( isset($content['participants']) ) {
             foreach ($content['participants'] as $p) {
                 $participant = new Participant();
                 $participant->setId($p['id']);
                 $participant->setReckoningId($p['reckoningId']);
                 $participant->setName($p['name']);
                 $participant->setPercent($p['percent']);

                 $this->participants[] = $participant;
             }
         }

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
             // if the participant is not on the participant list, add it
             if ( $this->findParticipantByName($line->getWho()) === null )
             {
                 $participant = new Participant();
                 $participant->setName($line->getWho());
                 $participant->setPercent(50);
                 $this->addParticipant($participant);
             }
         }
    }

    /**
     * Create reckoning from a file
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
        if ( $this->participants === null ) $this->participants = array();

        // date format
        //this->created = date('Ymd H:i:s', $this->created);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'owner' => $this->owner,
            'created' => $this->created,
            'modified' => $this->modified,
            'description' => $this->description,
            'lines' => $this->lines,
            'participants' => $this->participants
        ];
    }

    /**
     * add a new line, set id of this line
     * if the participant is not on the participant list, add it
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

      if ( $this->findParticipantByName($line->getWho()) === null )
      {
          $participant = new Participant();
          $participant->setName($line->getWho());
          $participant->setPercent(50);
          $this->addParticipant($participant);
      }

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

    /**
     * Add an participant on participant list
     * @param Participant $participant
     */
    public function addParticipant(Participant $participant) {
        $participant->setReckoningId($this->getId());
        $id = 0;
        if ( $lastParticipant = end($this->participants))
        {
            $id = $lastParticipant->getId()+1;
        }
        $participant->setId($id);
        $this->participants[] = $participant;
        return $participant;
    }

    /**
     * Find a participant on a reckoning
     * @param $lineId
     */
    public function findParticipant($participantId) {
        foreach ($this->participants as $participant) {
            if ( $participant->getId() == $participantId)
                return $participant;
        }
        return null;
    }

    /**
     * Delete a participant from a reckoning
     * all lines related to this participant will be deleted too
     * @param Participant $participant
     */
    public function deleteParticipant(Participant $participant) {
        $linesToDelete = array();
        foreach ($this->lines as $line) {
            if ( $line->getWho() === $participant->getName() ) {
                $linesToDelete[] = $line;
            }
        }
        foreach ($linesToDelete as $line) {
            $this->deleteLine($line);
        }
        $key = array_search($participant, $this->participants);
        if ( $key !== false ) unset($this->participants[$key]);
    }

    /**
     * Update a participant
     * all lines erlated to this participant should be updated if participant name change
     * @param Participant $participant
     */
    public function updateParticipant(Participant $participant) {

        $oldName = $participant->getName();
        foreach ( $this->participants as $key => $p) {
            if ($p->getId() == $participant->getId() ) {
                $oldName = $p->getName();
                $this->participants[$key] = $participant;
                break;
            }
        }

        if ( $oldName !== $participant->getName() ) {
          $linesToUpdate = array();
          foreach ($this->lines as $line) {
              if ( $line->getWho() === $oldName ) {
                  $linesToUpdate[] = $line;
              }
          }
          foreach ($linesToUpdate as $line) {
              $line->setWho($participant->getName());
              $this->updateLine($line);
          }
        }
    }

    /**
     * Find a participant by his name on the participant list
     * @param string $name
     * @return Participant
     */
    private function findParticipantByName($name) {
        foreach ( $this->participants as $participant) {
            if ($participant->getName() === $name ) return $participant;
        }
        return null;
    }

}
