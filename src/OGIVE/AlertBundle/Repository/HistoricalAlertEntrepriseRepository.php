<?php

namespace OGIVE\AlertBundle\Repository;

/**
 * HistoricalAlertEntrepriseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HistoricalAlertEntrepriseRepository extends \Doctrine\ORM\EntityRepository {

    public function deleteHistoricalAlertEntreprise(\OGIVE\AlertBundle\Entity\HistoricalAlertEntreprise $historicalAlertEntreprise) {
        $em = $this->_em;
        $historicalAlertEntreprise->setStatus(0);
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($historicalAlertEntreprise);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
    }

    public function saveHistoricalAlertEntreprise(\OGIVE\AlertBundle\Entity\HistoricalAlertEntreprise $historicalAlertEntreprise) {
        $em = $this->_em;
        $historicalAlertEntreprise->setStatus(1);
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($historicalAlertEntreprise);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
        return $historicalAlertEntreprise;
    }

    public function updateHistoricalAlertEntreprise(\OGIVE\AlertBundle\Entity\HistoricalAlertEntreprise $historicalAlertEntreprise) {
        $em = $this->_em;
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($historicalAlertEntreprise);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $em->close();
            throw $ex;
        }
        return $historicalAlertEntreprise;
    }

    public function getAll($offset = null, $limit = null, $search_query = null) {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.status = ?1');
        if ($search_query) {
            $qb->andWhere(
                    $qb->expr()->like('lower(e.message)', '?2')
            );
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
                    $qb->expr()->like('lower(e.message)', '?2')
            );
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

    public function getHistoricalAlertEntrepriseQueryBuilder() {
        return $this
                        ->createQueryBuilder('e')
                        ->where('e.status = :status')
                        ->orderBy('e.name', 'ASC')
                        ->setParameter('status', 1)
        ;
    }

}
