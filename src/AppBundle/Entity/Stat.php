<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StatRepository")
 */
class Stat
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_hit", type="datetime")
     */
    private $lastHit;

    /**
     * @var integer
     * @ORM\Column(name="hits_count", type="integer")
     */
    private $hitsCount;


    public function __construct()
    {
        $this->lastHit = new \DateTime();
        $this->hitsCount = 0;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getLastHit()
    {
        return $this->lastHit;
    }

    /**
     * @param \DateTime $lastHit
     * @return Stat
     */
    public function setLastHit($lastHit)
    {
        $this->lastHit = $lastHit;
        return $this;
    }

    /**
     * @return int
     */
    public function getHitsCount()
    {
        return $this->hitsCount;
    }

    /**
     * @param int $hitsCount
     * @return Stat
     */
    public function setHitsCount($hitsCount)
    {
        $this->hitsCount = $hitsCount;
        return $this;
    }
}
