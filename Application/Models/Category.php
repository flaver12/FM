<?php

namespace Solaria\App\models;
use Solaria\Framework\model\BaseModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="category")
 **/
class Category extends BaseModel {

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $name;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $created;

    /** @Column(type="integer") **/
    protected $enabled;

    /**
     * One Category has Many Topics.
     * @OneToMany(targetEntity="Solaria\App\Models\Topic", mappedBy="category")
     */
    protected $topics = null;

    public function __construct() {
      $this->topics = new ArrayCollection();
    }

    public function setName($name) {
      $this->name = $name;
    }

    public function setCreated($created) {

      $this->created = $created;

    }

    public function setEnabled($enabled) {

      $this->enabled = $enabled;

    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
      return $this->name;
    }

    public function getCreated() {
      return $this->created;
    }

    public function getEnabled() {
      return $this->enabled;
    }

    public function getTopics() {
      return $this->topics;
    }
}
