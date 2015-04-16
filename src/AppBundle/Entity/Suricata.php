<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suricata
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SuricataRepository")
 */
class Suricata
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="interface", type="string", length=30)
     */
    private $interface;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set interface
     *
     * @param string $interface
     * @return Suricata
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;

        return $this;
    }

    /**
     * Get interface
     *
     * @return string 
     */
    public function getInterface()
    {
        return $this->interface;
    }
}
