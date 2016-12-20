<?php
namespace MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entities\EntityRevision as Revision;

trait EntityRevision{

    /**
     * This entity uses Revision
     *
     * @return bool true
     */
    public static function usesRevision(){
        return true;
    }

    /*
     * @ORM\PostPersist
     * @TODO: Testar sem o uso do flush
     */
    public function _newCreatedRevision() {
        $revisionData = $this->getRevisionData();
        $revision = new Revision($revisionData,Revision::ACTION_CREATED);
        $revision->save(true);
    }

    /*
     * @ORM\PostUpdate
     */
    public function _newModifiedRevision() {
        $revisionData = $this->getRevisionData();
        $revision = new Revision($revisionData,Revision::ACTION_MODIFIED);
        $revision->save(true);
    }

    /*
     * @ORM\PostRemove
     */
    public function _newDeletedRevision() {

    }
}
