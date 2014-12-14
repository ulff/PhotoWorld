<?php

namespace Ulff\PhotoWorldBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * AlbumRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AlbumRepository extends EntityRepository {
    
    public function getAlbumList($params = array()) {
        $qb = $this->createQueryBuilder('a');

        $qb->select(array(
            'a.id',
            'a.title',
            'a.createdate',
            'a.createdby',
            'u.id AS uid',
            'u.lastname',
            'u.firstname',
            'COUNT(p.id) AS total'
        ));

        $qb->leftJoin('a.photos', 'p');
        $qb->leftJoin('Ulff\UserBundle\Entity\User', 'u', Expr\Join::WITH, 'a.createdby = u.id');
        $qb->addOrderBy('a.createdate', 'DESC');
        $qb->groupBy('a.id');

        if (!empty($params['limit'])) {
            $qb->setMaxResults($params['limit']);
        }

        return $qb->getQuery()->getResult();
    }
    
}
