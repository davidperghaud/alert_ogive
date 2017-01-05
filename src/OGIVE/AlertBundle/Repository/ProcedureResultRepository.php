<?php

namespace OGIVE\AlertBundle\Repository;

/**
 * ProcedureResultRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProcedureResultRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteProcedureResult(\OGIVE\AlertBundle\Entity\ProcedureResult $procedureResult) {
        $em= $this->_em;
        $procedureResult->setStatus(0);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($procedureResult);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }


    public function saveProcedureResult(\OGIVE\AlertBundle\Entity\ProcedureResult $procedureResult) {
        $em= $this->_em;
        $procedureResult->setStatus(1);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($procedureResult);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }

    public function updateProcedureResult(\OGIVE\AlertBundle\Entity\ProcedureResult $procedureResult) {
        $em= $this->_em;
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($procedureResult);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }
    public function getAll() 
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.status = :status')
            ->orderBy('e.createDate', 'DESC')
            ->setParameter('status', 1);
        return $qb->getQuery()->getResult();
    }
    
    public function getDomainQueryBuilder() {
         return $this
          ->createQueryBuilder('e')
          ->where('e.status = :status')
          ->where('e.state = :state')
          ->orderBy('e.name', 'ASC')
          ->setParameter('status', 1)
         ->setParameter('state', 1);

    }
}
