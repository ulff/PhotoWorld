<?php

namespace Ulff\PhotoWorldBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Like
 *
 * @author ulff
 *
 * @ORM\Entity(repositoryClass="Ulff\PhotoWorldBundle\Entity\Repository\LikeRepository")
 * @ORM\Table(name="likeit")
 * @ORM\HasLifecycleCallbacks
 */
class Like
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Photo", inversedBy="likeits")
     * @ORM\JoinColumn(name="photoid", referencedColumnName="id")
     */
    protected $photo;

    /**
     * @ORM\ManyToOne(targetEntity="Ulff\UserBundle\Entity\User", inversedBy="likeits")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    protected $user;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

}
