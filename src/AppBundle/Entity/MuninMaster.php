<?php

namespace AppBundle\Entity;

use AppBundle\Helper\Util;
use Doctrine\ORM\Mapping as ORM;

/**
 * MuninMaster
 * @ORM\Table(name="munin_masters")
 * @ORM\Entity
 */
class MuninMaster
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Unique hexadecimal string - identifies this master in Python script config
     * @var string
     * @ORM\Column(name="hex", type="string", length=255)
     */
    private $hex;

    /**
     * @var AndroidDevice
     * @ORM\ManyToOne(targetEntity="AndroidDevice", inversedBy="masters")
     */
    private $androidDevice;


    public function __construct()
    {
        $this->hex = Util::randomHex();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $hex
     * @return MuninMaster
     */
    public function setHex($hex)
    {
        $this->hex = $hex;
        return $this;
    }

    /**
     * @return string
     */
    public function getHex()
    {
        return $this->hex;
    }

    /**
     * @param AndroidDevice $androidDevice
     * @return MuninMaster
     */
    public function setAndroidDevice(AndroidDevice $androidDevice=null)
    {
        $this->androidDevice = $androidDevice;
        return $this;
    }

    /**
     * @return AndroidDevice
     */
    public function getAndroidDevice()
    {
        return $this->androidDevice;
    }
}
