<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StatRepository extends EntityRepository
{
    /**
     * @return Stat
     */
    public function getStat()
    {
        $stats = $this->findAll();
        return empty($stats) ? new Stat() : $stats[0];
    }

    /**
     * @return Stat
     */
    public function incrementStat()
    {
        $em = $this->getEntityManager();

        $stat = $this->getStat();
        $stat->setHitsCount($stat->getHitsCount()+1);

        $em->persist($stat);
        $em->flush();
        return $stat;
    }
}
