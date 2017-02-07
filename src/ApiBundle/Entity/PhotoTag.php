<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhotoTag
 *
 * @ORM\Table(name="photo_tag")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\PhotoTagRepository")
 */
class PhotoTag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ApiBundle\Entity\Photos", inversedBy="tag")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="ApiBundle\Entity\Tags", inversedBy="photo")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     */
    private $tag;




    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set photo
     *
     * @param integer $photo
     *
     * @return PhotoTag
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return int
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set tag
     *
     * @param string $tag
     *
     * @return PhotoTag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }


}

