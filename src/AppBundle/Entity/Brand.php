<?php

// src/AppBundle/Entity/Brand.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="brands")
 */
class Brand
{
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\GrossMerchandiseValue", mappedBy="brand")
     */
    private $gmvs;

    public function __construct()
    {
        $this->gmvs = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private $products;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getGmvs()
    {
        return $this->gmvs;
    }

    /**
     * @param mixed $gmvs
     */
    public function setGmvs($gmvs)
    {
        $this->gmvs = $gmvs;
    }

}