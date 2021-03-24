<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\StatRepository")
 */
class Stat
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(name="last_hit", type="datetime")
     */
    public \DateTime $lastHit;

    /**
     * @ORM\Column(name="hits_count", type="integer")
     */
    public int $hitsCount;

    public function __construct()
    {
        $this->lastHit = new \DateTime();
        $this->hitsCount = 0;
    }
}
