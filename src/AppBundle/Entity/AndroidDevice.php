<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AndroidDevice
 * @ORM\Table(name="android_devices")
 * @ORM\Entity
 */
class AndroidDevice
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Friendly name to recognize a device in Python's script configuration
     * @var string
     * @ORM\Column(name="friendlyName", type="string", length=255)
     */
    private $name;

    /**
     * Google Cloud Messaging registration id
     * @var string
     * @ORM\Column(name="registrationId", type="string", length=255)
     */
    private $registrationId;

    /**
     * @var ArrayCollection|MuninMaster[]
     * @ORM\OneToMany(targetEntity="MuninMaster", mappedBy="androidDevice")
     */
    private $masters;


    public function __construct()
    {
        $this->masters = new ArrayCollection();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AndroidDevice
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $registrationId
     * @return AndroidDevice
     */
    public function setRegistrationId($registrationId)
    {
        $this->registrationId = $registrationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationId()
    {
        return $this->registrationId;
    }

    /**
     * @param MuninMaster $master
     * @return AndroidDevice
     */
    public function addMaster(MuninMaster $master)
    {
        $this->masters[] = $master;
        return $this;
    }

    /**
     * @param MuninMaster $master
     */
    public function removeMaster(MuninMaster $master)
    {
        $this->masters->removeElement($master);
    }

    /**
     * @return Collection
     */
    public function getMasters()
    {
        return $this->masters;
    }
}
