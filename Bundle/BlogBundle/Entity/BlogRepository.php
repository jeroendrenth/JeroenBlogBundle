<?php

namespace Jeroen\Bundle\BlogBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BlogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BlogRepository extends EntityRepository
{
	public function loadList()
    {
        $q = $this
            ->createQueryBuilder('b')
            ->orderBy('b.creationDate', 'DESC')
            ->getQuery();
       
       	return $q->getResult();
    }
}
