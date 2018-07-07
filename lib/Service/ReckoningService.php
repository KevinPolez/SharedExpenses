<?php

namespace OCA\SharedExpenses\Service;

use Exception;

use OCP\Files\FileInfo;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\Security\ISecureRandom;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;

use OCA\SharedExpenses\Db\Reckoning;
use OCA\SharedExpenses\Db\Line;



class ReckoningService {

    private $root;

    public function __construct(IRootFolder $root){
        $this->root = $root;
    }

    /**
     * @param string $userId
     * @return array with all reckoning in the current directory
     */
    public function findAll ($userId){
        $reckoningsFolder = $this->getFolderForUser($userId);
        $reckonings = $this->gatherReckoningFiles($reckoningsFolder);
        $filesById = [];
        foreach($reckonings as $reckoning) {
            $filesById[$reckoning->getId()] = $reckoning;
        }
        $tagger = \OC::$server->getTagManager()->load('files');
        if($tagger===null) {
            $tags = [];
        } else {
            $tags = $tagger->getTagsForObjects(array_keys($filesById));
        }
        $reckonings = [];
        foreach($filesById as $id=>$file) {
            $reckonings[] = $this->getReckoning($file, $reckoningsFolder, array_key_exists($id, $tags) ? $tags[$id] : []);
        }
        return $reckonings;
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
    * Used to get a single reckoning by id
    * @param int $id the id of the reckoning to get
    * @param string $userId
    * @throws NotFoundException if reckoning does not exist
    * @return Reckoning
    */
    public function find($id, $userId) {
       $folder = $this->getFolderForUser($userId);
       return $this->getReckoning($this->getFileById($folder, $id), $folder, $this->getTags($id));
    }

    /*public function create($title, $description, $userId) {
        $reckoning = new Reckoning();
        $reckoning->setTitle($title);
        $reckoning->setDescription($description);
        $reckoning->setOwner($userId);
        $reckoning->setCreated(date('Y-m-d H:i:s'));
  			$reckoning->setHash(\OC::$server->getSecureRandom()->generate(
  				16,
  				ISecureRandom::CHAR_DIGITS .
  				ISecureRandom::CHAR_LOWER .
  				ISecureRandom::CHAR_UPPER
  			));
        return $this->mapper->insert($reckoning);
    }*/

    /**
     * Creates a reckoning and returns the empty reckoning
     * @param string $userId
     * @see update for setting reckoning content
     * @return Reckoning the newly created reckoning
     */
    public function create ($title, $description, $userId) {

        $folder = $this->getFolderForUser($userId);
        // check new note exists already and we need to number it
        // pass -1 because no file has id -1 and that will ensure
        // to only return filenames that dont yet exist
        $path = $this->generateFileName($folder, $title, "json", -1);
        $file = $folder->newFile($path);

        $title = trim(basename($file->getName(),'.json'));

        $reckoning = new Reckoning();
        $reckoning->setId($file->getId());
        $reckoning->setTitle($title);
        $reckoning->setOwner($userId);
        $reckoning->setCreated(new \Datetime('NOW'));

        $file->putContent(json_encode($reckoning));

        return $reckoning;
    }

    /**
     * Delete a reckoning
     * @param int $id the id of the reckoning which should be deleted
     * @param string $userId
     * @throws ReckoningDoesNotExistException if reckoning does not exist
     */
    public function delete ($id, $userId) {
        $folder = $this->getFolderForUser($userId);
        $file = $this->getFileById($folder, $id);
        $file->delete();
    }

    /**
     * Update a reckoning
     */
    public function update($id, $title, $description, $userId) {
        $reckoning = $this->find($id, $userId);
        $reckoning->setTitle($title);
        $reckoning->setDescription($description);
        $reckoning->setModified(new \Datetime('NOW'));
        $this->save($reckoning, $userId);
        return $reckoning;
    }
    /*public function findLines($reckoningId) {
        return $this->lineMapper->findByReckoning($reckoningId);
    }*/

    /**
     * Add a line on a reckoning
     * @param int $reckoningId the id of the reckoning
     * @param Line $line
     * @param string $userId
     * @return Line
     */
    public function addLine($reckoningId, Line $line, $userId) {
        try {
            $reckoning = $this->find($reckoningId, $userId);
            $line = $reckoning->addLine($line);
            $this->save($reckoning, $userId);
            return $line;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete a line from a reckoning
     * @param int $reckoningId the id of the reckoning
     * @param Line $line
     * @param string $userId
     * @return Line
     */
    public function deleteLine($reckoningId, Line $line, $userId) {
      try {
        $reckoning = $this->find($reckoningId, $userId);
        $reckoning->deleteLine($line);
        $this->save($reckoning, $userId);
        return $line;
      } catch(Exception $e) {
        $this->handleException($e);
      }
    }

    /**
     * Find a line on a reckoning
     */
    public function findLine($reckoningId, $lineId, $userId)
    {
      try {
        $reckoning = $this->find($reckoningId, $userId);
        $line = $reckoning->findLine($lineId);
        return $line;
      } catch(Exception $e) {
        $this->handleException($e);
      }

    }

    private function save(Reckoning $reckoning, $userId)
    {
        $reckoningsFolder = $this->getFolderForUser($userId);
        $file = $this->getFileById($reckoningsFolder, $reckoning->getId());
        $file->putContent(json_encode($reckoning));
    }

    /**
     *
     */
    private function getTags ($id) {
        $tagger = \OC::$server->getTagManager()->load('files');
        if($tagger===null) {
            $tags = [];
        } else {
            $tags = $tagger->getTagsForObjects([$id]);
        }
        return array_key_exists($id, $tags) ? $tags[$id] : [];
    }

    /**
     *
     */
    private function getReckoning($file,$reckoningsFolder,$tags=[]) {
        $id=$file->getId();
        try{
            $reckoning=Reckoning::fromFile($file, $reckoningsFolder, $tags);
        }catch(FileNotFoundException $e){
            $reckoning = Reckoning::fromException($this->l10n->t('File error').': ('.$file->getName().') '.$e->getMessage(), $file, $notesFolder, array_key_exists($id, $tags) ? $tags[$id] : []);
        }catch(DecryptionFailedException $e){
            $reckoning = Reckoning::fromException($this->l10n->t('Encryption Error').': ('.$file->getName().') '.$e->getMessage(), $file, $notesFolder, array_key_exists($id, $tags) ? $tags[$id] : []);
        }catch(\Exception $e){
            $reckoning = Reckoning::fromException($this->l10n->t('Error').': ('.$file->getName().') '.$e->getMessage(), $file, $notesFolder, array_key_exists($id, $tags) ? $tags[$id] : []);
        }
        return $reckoning;
    }

    /**
     * @param Folder $folder
     * @param int $id
     * @throws NoteDoesNotExistException
     * @return \OCP\Files\File
     */
    private function getFileById ($folder, $id) {
        $file = $folder->getById($id);

        if(count($file) <= 0 || !$this->isReckoning($file[0])) {
            throw new NotFoundException();
        }
        return $file[0];
    }

    /**
     * @param string $userId the user id
     * @return Folder
     */
    private function getFolderForUser ($userId) {
        $path = '/' . $userId . '/files/Reckonings';
        return $this->getOrCreateFolder($path);
    }

    /**
     * Finds a folder and creates it if non-existent
     * @param string $path path to the folder
     * @return Folder
     */
    private function getOrCreateFolder($path) {
        if ($this->root->nodeExists($path)) {
            $folder = $this->root->get($path);
        } else {
            $folder = $this->root->newFolder($path);
        }
        return $folder;
    }

    /**
     * get path of file and the title.json and check if they are the same
     * file. If not the title needs to be renamed
     *
     * @param Folder $folder a folder to the notes directory
     * @param string $title the filename which should be used
     * @param string $extension the extension which should be used
     * @param int $id the id of the note for which the title should be generated
     * used to see if the file itself has the title and not a different file for
     * checking for filename collisions
     * @return string the resolved filename to prevent overwriting different
     * files with the same title
     */
    private function generateFileName (Folder $folder, $title, $extension, $id) {
        $path = $title . '.' . $extension;
        // if file does not exist, that name has not been taken. Similar we don't
        // need to handle file collisions if it is the filename did not change
        if (!$folder->nodeExists($path) || $folder->get($path)->getId() === $id) {
            return $path;
        } else {
            // increments name (2) to name (3)
            $match = preg_match('/\((?P<id>\d+)\)$/u', $title, $matches);
            if($match) {
                $newId = ((int) $matches['id']) + 1;
                $newTitle = preg_replace('/(.*)\s\((\d+)\)$/u',
                    '$1 (' . $newId . ')', $title);
            } else {
                $newTitle = $title . ' (2)';
            }
            return $this->generateFileName($folder, $newTitle, $extension, $id);
        }
    }

    /**
     * gather reckonings files in given directory and all subdirectories
     * @param Folder $folder
     * @return array
     */
    private function gatherReckoningFiles ($folder) {
    	$reckonings = [];
    	$nodes = $folder->getDirectoryListing();
    	foreach($nodes as $node) {
    		if($node->getType() === FileInfo::TYPE_FOLDER) {
    			$notes = array_merge($notes, $this->gatherReckoningsFiles($node));
    			continue;
    		}
    		if($this->isReckoning($node)) {
    			$reckonings[] = $node;
    		}
    	}
    	return $reckonings;
    }

    /**
     * test if file is a reckoning
     *
     * @param \OCP\Files\File $file
     * @return bool
     */
    private function isReckoning($file) {
        $allowedExtensions = ['json'];
        if($file->getType() !== 'file') return false;
        if(!in_array(
            pathinfo($file->getName(), PATHINFO_EXTENSION),
            $allowedExtensions
        )) return false;
        return true;
    }

}
