<?php

namespace OCA\SharedExpenses\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\SharedExpenses\Db\Line;
use OCA\SharedExpenses\Service\ReckoningService;


class LineService {

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

    public function create($reckoningId, $amount, $when, $who, $why, $for, $userId) {
        $line = new Line();

        $line->setAmount($amount);
        $line->setWho($who);
        $line->setWhen($when);
        $line->setWhy($why);
        $line->setUserId($userId);
        $line->setFor($for);
        $line->setCreated(new \Datetime('NOW'));

        $reckoning = $this->reckoningService->addLine($reckoningId, $line, $userId);

        return $reckoning;
    }

    public function update($id, $reckoningId, $amount, $when, $who, $why, $for, $userId) {
        try {
            $line = $this->reckoningService->findLine($reckoningId, $id, $userId);
            $line->setReckoningId($reckoningId);
            $line->setAmount($amount);
            $line->setWho($who);
            $line->setWhen($when);
            $line->setWhy($why);
            $line->setFor($for);

            return $this->reckoningService->updateLine($reckoningId, $line, $userId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete($id, $reckoningId, $userId) {
        try {
            $line = $this->reckoningService->findLine($reckoningId,$id,$userId);
            return $this->reckoningService->deleteLine($reckoningId, $line, $userId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

}
