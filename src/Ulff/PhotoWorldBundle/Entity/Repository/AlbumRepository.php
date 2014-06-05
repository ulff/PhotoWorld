<?php

namespace Ulff\PhotoWorldBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AlbumRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AlbumRepository extends EntityRepository {
    
    public function getAlbumList($params = array()) {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a');
        $qb->addOrderBy('a.createdate', 'DESC');

        if (!empty($params['limit'])) {
            $qb->setMaxResults($params['limit']);
        }

        return $qb->getQuery()->getResult();
    }
    
}