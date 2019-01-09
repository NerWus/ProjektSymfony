<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UsersRepository")
 */
class Users
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
     * @var string
     *
     * @ORM\Column(name="UserName", type="string", length=7, unique=true)
     */
    private $userName;

    /**
     * @var string
     *
     * @ORM\Column(name="Password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="UserRole", type="string", length=255)
     */
    private $userRole;

    /**
     * @var int
     *
     * @ORM\Column(name="UserRemoteAdress", type="integer")
     */
    private $userRemoteAdress;


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
     * Set userName
     *
     * @param string $userName
     *
     * @return Users
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set userRole
     *
     * @param string $userRole
     *
     * @return Users
     */
    public function setUserRole($userRole)
    {
        $this->userRole = $userRole;

        return $this;
    }

    /**
     * Get userRole
     *
     * @return string
     */
    public function getUserRole()
    {
        return $this->userRole;
    }

    /**
     * Set userRemoteAdress
     *
     * @param integer $userRemoteAdress
     *
     * @return Users
     */
    public function setUserRemoteAdress($userRemoteAdress)
    {
        $this->userRemoteAdress = $userRemoteAdress;

        return $this;
    }

    /**
     * Get userRemoteAdress
     *
     * @return int
     */
    public function getUserRemoteAdress()
    {
        return $this->userRemoteAdress;
    }
}

