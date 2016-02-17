<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TalentImage {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */    
    protected $image;
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Talent", inversedBy="images")
     * @ORM\JoinColumn(name="talent_id", referencedColumnName="id")
     */
    protected $talent;
    /**
     * @ORM\Column(type="integer")
     */
    protected $main;

    /**
     * Set main
     *
     * @param integer $main
     *
     * @return TalentImage
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Get main
     *
     * @return integer
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return TalentImage
     */
    public function setImage(\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set talent
     *
     * @param \AppBundle\Entity\Equipemnt $talent
     *
     * @return TalentImage
     */
    public function setTalent(\AppBundle\Entity\Talent $talent = null)
    {
        $this->talent = $talent;

        return $this;
    }

    /**
     * Get talent
     *
     * @return \AppBundle\Entity\Equipemnt
     */
    public function getTalent()
    {
        return $this->talent;
    }
}
