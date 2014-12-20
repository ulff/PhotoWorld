<?php

namespace Ulff\PhotoWorldBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Description of Photo
 *
 * @author ulff
 *
 * @ORM\Entity(repositoryClass="Ulff\PhotoWorldBundle\Entity\Repository\PhotoRepository")
 * @ORM\Table(name="photo")
 * @ORM\HasLifecycleCallbacks
 */
class Photo
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=140, nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdate;

    /**
     * @ORM\Column(type="integer")
     */
    protected $createdby;

    /**
     * @Assert\File(maxSize="250M")
     */
    protected $photofile;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortnumber;

    /**
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="photos")
     * @ORM\JoinColumn(name="albumid", referencedColumnName="id")
     */
    protected $album;

    public function __construct()
    {
        $this->setCreatedate(new \DateTime());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTypeCategory()
    {
        $typeExploded = explode('/',$this->type);
        return reset($typeExploded);
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
        return $this;
    }

    public function getCreatedate()
    {
        return $this->createdate;
    }

    public function setCreatedby($createdby)
    {
        $this->createdby = $createdby;
        return $this;
    }

    public function getCreatedby()
    {
        return $this->createdby;
    }

    public function setPhotofile(UploadedFile $photofile = null)
    {
        $this->photofile = $photofile;

        return $this;
    }

    public function getPhotofile()
    {
        return $this->photofile;
    }

    public function setSortnumber($sortnumber)
    {
        $this->sortnumber = $sortnumber;
        return $this;
    }

    public function getSortnumber()
    {
        return $this->sortnumber;
    }

    public function setAlbum(\Ulff\PhotoWorldBundle\Entity\Album $album = null)
    {
        $this->album = $album;
        return $this;
    }

    public function getAlbum()
    {
        return $this->album;
    }

    public function upload()
    {
        if (null === $this->getPhotofile()) {
            return;
        }

        $photofilePath = $this->resolvePhotoPath();

        $this->getPhotofile()->move(
            $this->getUploadRootDir() . '/' . $photofilePath, $this->getPhotofile()->getClientOriginalName()
        );

        $this->path = $photofilePath . '/' . $this->getPhotofile()->getClientOriginalName();

        $this->photofile = null;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getPhotoFileName()
    {
        return str_replace($this->getAlbum()->getId() . '/', '', $this->path);
    }

    public function resolveType()
    {
        $this->setType(strtolower($this->getPhotofile()->getMimeType()));
    }

    protected function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/photoworld';
    }

    protected function resolvePhotoPath()
    {
        return $this->getAlbum()->getId();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        //$metadata->addPropertyConstraint('photofile', new NotBlank());
    }

}
