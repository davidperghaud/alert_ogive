<?php

namespace OGIVE\AlertBundle\Repository;

/**
 * ExpressionInterestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExpressionInterestRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteExpressionInterest(\OGIVE\AlertBundle\Entity\ExpressionInterest $expressionInterest) {
        $em= $this->_em;
        $expressionInterest->setStatus(0);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($expressionInterest);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }


    public function saveExpressionInterest(\OGIVE\AlertBundle\Entity\ExpressionInterest $expressionInterest) {
        $em= $this->_em;
        $expressionInterest->setStatus(1);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($expressionInterest);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }

    public function updateExpressionInterest(\OGIVE\AlertBundle\Entity\ExpressionInterest $expressionInterest) {
        $em= $this->_em;
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($expressionInterest);
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
