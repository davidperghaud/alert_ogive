<?php

namespace OGIVE\AlertBundle\Repository;

/**
 * SubcriptionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SubscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteSubscription(\OGIVE\AlertBundle\Entity\Subscription $subscription) {
        $em= $this->_em;
        $subscription->setStatus(0);
        $subscriber = new \OGIVE\AlertBundle\Entity\Subscriber();
        $repositorySubscriber = $em->getRepository("OGIVEAlertBundle:Subscriber");
        $em->getConnection()->beginTransaction();
        try{
            $subscribers = $subscription->getSubscribers();
            foreach ($subscribers as $subscriber) {
                $subscriber->setSubscription(null);
                $repositorySubscriber->updateSubscriber($subscriber);
            }
            $em->persist($subscription);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }


    public function saveSubscription(\OGIVE\AlertBundle\Entity\Subscription $subscription) {
        $em= $this->_em;
        $subscription->setStatus(1);
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($subscription);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
        return $subscription;
    }

    public function updateSubscription(\OGIVE\AlertBundle\Entity\Subscription $subscription) {
        $em= $this->_em;
        $em->getConnection()->beginTransaction();
        try{
            $em->persist($subscription);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
        return $subscription;
    }
    public function getAll($offset = null, $limit = null, $search_query = null) {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.status = ?1');
        if ($search_query) {
            $qb->andWhere(
                    $qb->expr()->orX(
                            $qb->expr()->like('lower(e.name)', '?2'), $qb->expr()->like('lower(e.price)', '?2'), $qb->expr()->like('lower(e.currency)', '?2')
            ));
        }
        $qb->orderBy('e.createDate', 'DESC');
        if ($search_query) {
            $qb->setParameters(array(1 => 1, 2 => '%' . strtolower($search_query) . '%'));
        } else {
            $qb->setParameters(array(1 => 1));
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }

    public function getAllByString($search_query = null) {
        
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.status = ?1');
        if ($search_query) {
            $qb->andWhere(
                    $qb->expr()->orX(
                            $qb->expr()->like('lower(e.name)', '?2'), $qb->expr()->like('lower(e.price)', '?2'), $qb->expr()->like('lower(e.currency)', '?2')
            ));
        }
        $qb->orderBy('e.createDate', 'DESC');
        if ($search_query) {
            $search_query = strtolower($search_query);
            $qb->setParameters(array(1 => 1, 2 => '%' . strtolower($search_query) . '%'));
        } else {
            $qb->setParameters(array(1 => 1));
        }
        return $qb->getQuery()->getResult();
    }
    
    public function getSubscriptionQueryBuilder() {
         return $this
          ->createQueryBuilder('e')
          ->where('e.status = :status')
          ->andWhere('e.state = :state')
          ->orderBy('e.periodicity', 'ASC')
          ->setParameter('status', 1)
         ->setParameter('state', 1);

    }
}
