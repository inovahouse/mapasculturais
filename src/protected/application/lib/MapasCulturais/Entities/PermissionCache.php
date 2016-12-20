<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * PermissionCache
 * @ORM\Table(name="pcache", indexes={
 *      @ORM\Index(name="pcache_owner_idx", columns={"object_type", "object_id"}), 
 *      @ORM\Index(name="pcache_permission_idx", columns={"object_type", "object_id", "action"}),
 *      @ORM\Index(name="pcache_permission_user_idx", columns={"object_type", "object_id", "action", "user_id"}),
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Project"                       = "\MapasCulturais\Entities\ProjectPermissionCache",
        "MapasCulturais\Entities\Event"                         = "\MapasCulturais\Entities\EventPermissionCache",
        "MapasCulturais\Entities\Agent"                         = "\MapasCulturais\Entities\AgentPermissionCache",
        "MapasCulturais\Entities\Space"                         = "\MapasCulturais\Entities\SpacePermissionCache",
        "MapasCulturais\Entities\Seal"                          = "\MapasCulturais\Entities\SealPermissionCache",
        "MapasCulturais\Entities\Registration"                  = "\MapasCulturais\Entities\RegistrationPermissionCache"
   })
 */
abstract class PermissionCache extends \MapasCulturais\Entity {


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="permission_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=false)
     */
    protected $action;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;
    
    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;
    
    protected function canUserCreate($user){
        return true;
    }

}