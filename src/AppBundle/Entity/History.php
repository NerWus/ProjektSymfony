<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 *
 * @ORM\Table(name="history")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HistoryRepository")
 */
class History
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
     * @var \DateTime
     *
     * @ORM\Column(name="DownloadDate", type="date")
     */
    private $downloadDate;

    /**
     * @var int
     *
     * @ORM\Column(name="UserID", type="integer")
     */
    private $userID;

    /**
     * @var int
     *
     * @ORM\Column(name="FileID", type="integer")
     */
    private $fileID;


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
     * Set downloadDate
     *
     * @param \DateTime $downloadDate
     *
     * @return History
     */
    public function setDownloadDate($downloadDate)
    {
        $this->downloadDate = $downloadDate;

        return $this;
    }

    /**
     * Get downloadDate
     *
     * @return \DateTime
     */
    public function getDownloadDate()
    {
        return $this->downloadDate;
    }

    /**
     * Set userID
     *
     * @param integer $userID
     *
     * @return History
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

    /**
     * Get userID
     *
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * Set fileID
     *
     * @param integer $fileID
     *
     * @return History
     */
    public function setFileID($fileID)
    {
        $this->fileID = $fileID;

        return $this;
    }

    /**
     * Get fileID
     *
     * @return int
     */
    public function getFileID()
    {
        return $this->fileID;
    }
}

