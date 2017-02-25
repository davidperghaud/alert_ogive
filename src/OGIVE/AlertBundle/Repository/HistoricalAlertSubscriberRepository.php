<?php

namespace OGIVE\AlertBundle\Repository;

/**
 * HistoricalAlertSubscriberRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HistoricalAlertSubscriberRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteHistoricalAlertSubscriber(\OGIVE\AlertBundle\Entity\HistoricalAlertSubscriber $historicalAlertSubscriber) {
        $em= $this->_em;
        $historicalAlertSubscriber->setStatus(0);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($historicalAlertSubscriber);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }


    public function saveHistoricalAlertSubscriber(\OGIVE\AlertBundle\Entity\HistoricalAlertSubscriber $historicalAlertSubscriber) {
        $em= $this->_em;
        $historicalAlertSubscriber->setStatus(1);
        //$em->getConnection()->beginTransaction();
        try{
            $em->persist($historicalAlertSubscriber);
            $em->flush();
            //$em->getConnection()->commit();
        } catch (Exception $ex) {
//            $em->getConnection()->rollback();
//            $em->close();
            throw $ex;
        }
        return $historicalAlertSubscriber;
    }

    public function updateHistoricalAlertSubscriber(\OGIVE\AlertBundle\Entity\HistoricalAlertSubscriber $historicalAlertSubscriber) {
        $em= $this->_em;
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($historicalAlertSubscriber);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
        return $historicalAlertSubscriber;
    }
    public function getAll($offset=null, $limit=null) 
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.status = :status')
            ->orderBy('e.createDate', 'DESC')
            ->setParameter('status', 1);
        if($offset){
            $qb->setFirstResult($offset);
        }
        if($limit){
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }
    
    public function getHistoricalAlertSubscriberQueryBuilder() {
         return $this
          ->createQueryBuilder('e')
          ->where('e.status = :status')
          ->orderBy('e.name', 'ASC')
          ->setParameter('status', 1)
         ;

    }
}
