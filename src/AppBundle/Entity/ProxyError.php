<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class ProxyError
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
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * @var string
     * @ORM\Column(name="structure", type="string", length=255)
     */
    private $structure;

    /**
     * @var string
     * @ORM\Column(name="exception", type="string", length=255)
     */
    private $exception;


    public function __construct()
    {
        $this->date = new \DateTime();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $date
     * @return ProxyError
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $source
     * @return ProxyError
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $structure
     * @return ProxyError
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
        return $this;
    }

    /**
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param string $exception
     * @return ProxyError
     */
    public function setException($exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }
}
