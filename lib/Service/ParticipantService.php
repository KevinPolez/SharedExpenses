<?php

namespace OCA\SharedExpenses\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\SharedExpenses\Db\Participant;
use OCA\SharedExpenses\Service\ReckoningService;


class ParticipantService {

    private $reckoningService;

    public function __construct(ReckoningService $reckoningService){
        $this->reckoningService = $reckoningService;
    }

    public function findAll($userId) {
        //return $this->mapper->findAll($userId);
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    public function find($id, $userId) {
        try {
            //return $this->mapper->find($id, $userId);

        // in order to be able to plug in different storage backends like files
        // for instance it is a good idea to turn storage related exceptions
        // into service related exceptions so controllers and service users
        // have to deal with only one type of exception
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a new Participant
     */
    public function create($reckoningId, $name, $percent, $userId) {
        $participant = new Participant();

        $participant->setReckoningId($reckoningId);
        $participant->setName($name);
        $participant->setPercent($percent);

        $reckoning = $this->reckoningService->addParticipant($reckoningId, $participant, $userId);

        return $reckoning;
    }

    /**
     * Update a Participant
     */
    public function update($id, $reckoningId, $name, $percent, $userId) {
        try {
            $participant = $this->reckoningService->findParticipant($reckoningId, $id, $userId);

            $participant->setReckoningId($reckoningId);
            $participant->setName($name);
            $participant->setPercent($percent);

            $reckoning = $this->reckoningService->updateParticipant($reckoningId, $participant, $userId);
            return $reckoning;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete a Participant
     */
    public function delete($id, $reckoningId, $userId) {
        try {
            $participant = $this->reckoningService->findParticipant($reckoningId,$id,$userId);
            $reckoning = $this->reckoningService->deleteParticipant($reckoningId, $participant, $userId);
            return $reckoning;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

}
