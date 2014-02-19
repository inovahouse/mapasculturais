<?php
namespace MapasCulturais\Entities\AgentRelations;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities;
use MapasCulturais\App;

/**
 * @ORM\Entity
 */
class Project extends Entities\AgentRelation {
    const STATUS_REGISTRATION = -5;
    const STATUS_REGISTRATION_REJECTED = -6;

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;


    protected function canUserModify($user){
        $group = App::i()->projectRegistrationAgentRelationGroupName;

        if($this->group == $group && $this->status == self::STATUS_REGISTRATION && $this->agent->user->id == $user->id)
            return true;
        else
            return parent::genericPermissionVerification($user);
    }

    function delete($flush = false){
        $this->checkPermission('remove');

        if($this->group == App::i()->projectRegistrationAgentRelationGroupName){
            $this->status = self::STATUS_REGISTRATION;
            $this->save($flush);
        }else{
            parent::delete($flush);
        }
    }

    function getGroup(){
        if($this->group == 'registration')
            return App::txt("project registration");
        else
            return $this->group;

    }

    function jsonSerialize() {
        $result = parent::jsonSerialize();
        return $result;
    }
}