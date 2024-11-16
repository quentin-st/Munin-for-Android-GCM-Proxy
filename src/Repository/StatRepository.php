<?php

namespace App\Repository;

use App\Entity\Stat;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Stat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stat[]    findAll()
 * @method Stat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stat::class);
    }

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
            ->setParameter('now', new DateTime())
            ->set('stat.hitsCount', 'stat.hitsCount+1')
            ->getQuery()
            ->execute();
    }
}
