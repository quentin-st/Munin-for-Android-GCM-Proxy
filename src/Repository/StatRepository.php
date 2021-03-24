<?php

namespace App\Repository;

use App\Entity\Stat;
use Doctrine\ORM\EntityRepository;

class StatRepository extends EntityRepository
{
    public function getStat(): Stat
    {
        $stats = $this->findAll();
        return empty($stats) ? new Stat() : $stats[0];
    }

    public function incrementStat(): void
    {
        $this->createQueryBuilder('stat')
            ->update()
            ->set('stat.lastHit', ':now')
            ->setParameter('now', new \DateTime())
            ->set('stat.hitsCount', 'stat.hitsCount+1')
            ->getQuery()
            ->execute();
    }
}
