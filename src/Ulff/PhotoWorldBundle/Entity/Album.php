<?php

namespace Ulff\PhotoWorldBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Description of Album
 *
 * @author ulff
 * 
 * @ORM\Entity(repositoryClass="Ulff\PhotoWorldBundle\Entity\Repository\AlbumRepository")
 * @ORM\Table(name="album")
 * @ORM\HasLifecycleCallbacks
 */
class Album {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdate;

    /**
     * @ORM\Column(type="integer")
     */
    protected $createdby;
    
    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="album")
     */
    protected $photos;
    
    public function __construct() {
        $this->setCreatedate(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setCreatedate($createdate) {
        $this->createdate = $createdate;
        return $this;
    }

    public function getCreatedate() {
        return $this->createdate;
    }

    public function setCreatedby($createdby) {
        $this->createdby = $createdby;
        return $this;
    }

    public function getCreatedby() {
        return $this->createdby;
    }
    
    public function addPhoto(\Ulff\PhotoWorldBundle\Entity\Photo $photos) {
        $this->photos[] = $photos;
        return $this;
    }

    public function removePhoto(\Ulff\PhotoWorldBundle\Entity\Photo $photos) {
        $this->photos->removeElement($photos);
    }

    public function getPhotos() {
        return $this->photos;
    }

    public function getAlbumDirectoryPath() {
        return __DIR__ . '/../../../../web/uploads/photoworld/'.$this->getId();
    }

    public function getAlbumDirectoryWebPath() {
        return 'uploads/photoworld/'.$this->getId();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addPropertyConstraint('title', new NotBlank());
        $metadata->addPropertyConstraint('title', new Length(array('max' => 140)));
    }

}
