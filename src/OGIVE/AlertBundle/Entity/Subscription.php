<?php

namespace OGIVE\AlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription
 *
 * @ORM\Table(name="subscription")
 * @ORM\Entity(repositoryClass="OGIVE\AlertBundle\Repository\SubscriptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Subscription
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="periodicity", type="integer")
     */
    private $periodicity;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="bigint")
     */
    private $price;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="OGIVE\AlertBundle\Entity\Subscriber", mappedBy="subscriber", cascade={"remove", "persist"})
     */
    private $subscribers;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update_date", type="datetime")
     */
    private $lastUpdateDate;
    
    /** 
     * Constructor
     */
    public function __construct() {
        $this->status = 1;
    }
    
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
     * Set periodicity
     *
     * @param integer $periodicity
     *
     * @return Subcription
     */
    public function setPeriodicity($periodicity)
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    /**
     * Get periodicity
     *
     * @return int
     */
    public function getPeriodicity()
    {
        return $this->periodicity;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Subcription
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * Set status
     *
     * @param string $status
     *
     * @return Subscription
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Add subscriber
     *
     * @param OGIVE\AlertBundle\Entity\Subscriber $subscriber 
     * @return Subscription
     */
    public function addSubscriber(OGIVE\AlertBundle\Entity\Subscriber $subscriber) {
        $this->subscribers[] = $subscriber;
        return $this;
    }

    /**
     * Get subscribers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubscribers() {
        return $this->subscribers;
    }

    /**
     * Set subscribers
     *
     * @param \Doctrine\Common\Collections\Collection $subscribers
     * @return Subscription
     */
    public function setSubscribers(\Doctrine\Common\Collections\Collection $subscribers = null) {
        $this->subscribers = $subscribers;

        return $this;
    }

    /**
     * Remove subscriber
     *
     * @param OGIVE\AlertBundle\Entity\Subscriber $subscriber
     * @return Subscription
     */
    public function removeSubscriber(OGIVE\AlertBundle\Entity\Subscriber $subscriber) {
        $this->subscribers->removeElement($subscriber);
        return $this;
    }
    
    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Subscription
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }
    
    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }
    
    /**
     * Set lastUpdateDate
     *
     * @param \DateTime $lastUpdateDate
     *
     * @return Subscription
     */
    public function setLastUpdateDate($lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    /**
     * Get lastUpdateDate
     *
     * @return \DateTime
     */
    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }
    
    /**
     * @ORM\PreUpdate() 
     */
    public function preUpdate()
    {
        $this->lastUpdateDate = new \DateTime();
    }

    /**
     * @ORM\PrePersist() 
     */
    public function prePersist()
    {
        $this->createDate = new \DateTime();
        $this->lastUpdateDate = new \DateTime();
        $this->status = 1;
    }
}

