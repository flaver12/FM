<?php

namespace Solaria\App\Models;
use Solaria\Framework\Model\BaseModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="user_role")
 **/
class UserRole extends BaseModel {

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $user_id;

    /** @Column(type="integer") **/
    protected $role_id;

    /**
     * Many Users have One UserGroupId.
     * @ManyToOne(targetEntity="Solaria\App\Models\User", inversedBy="userRoles")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
     protected $user;

     /**
      * Many Users have One UserGroupId.
      * @ManyToOne(targetEntity="Solaria\App\Models\Role", inversedBy="userRoles")
      * @JoinColumn(name="role_id", referencedColumnName="id")
      */
     protected $role;

     public function __construct() {
       $this->user      = new ArrayCollection();
       $this->role      = new ArrayCollection();
     }

     public function getUser() {
         return $this->user;
     }

     public function getRole() {
         return $this->role;
     }

     public function getRoleId() {
         return $this->role_id;
     }

     public function setUser($user) {
         $this->user = $user;
     }

     public function setRole($role) {
         $this->role = $role;
     }
}
