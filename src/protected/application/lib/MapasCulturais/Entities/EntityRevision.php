<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Entity Revision
 *
 * @ORM\Table(name="entity_revision",indexes={@ORM\Index(name="entity_revision_idx", columns={"object_id", "object_type", "timestamp"})}))
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EntityRevision extends \MapasCulturais\Entity{
    const ACTION_CREATED        = 'created';
    const ACTION_MODIFIED       = 'modified';
    const ACTION_PUBLISHED      = 'published';
    const ACTION_UNPUBLISHED    = 'unpublished';
    const ACTION_ARCHIVED       = 'archived';
    const ACTION_TRASHED        = 'trashed';
    const ACTION_DELETED        = 'deleted';



    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="entity_revision_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="string", length=255, nullable=false)
     */
    protected $objectType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    protected $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=false)
     */
    protected $action = "";

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    protected $message = "";

    /**
     * @var \MapasCulturais\Entities\EntityRevisionData[]
     *
     * @ORM\ManyToMany(targetEntity="MapasCulturais\Entities\EntityRevisionData")
     * @ORM\JoinTable(name="entity_revision_revision_data",
     *      joinColumns={@ORM\JoinColumn(name="revision_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="revision_data_id", referencedColumnName="id")}
     * )
     */
    protected $data;


    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    public function __construct(array $dataRevision, $action, $message = "") {
        parent::__construct();


        $this->data = new \Doctrine\Common\Collections\ArrayCollection();

        // Se a ação for created, criar todos os atributos para incluir no revision_data
        // if() ...
        // else ...
        // Se a ação for qualquer outra ação, pegar a revisão anterior, comparar com o data da revisão atual e gerar somente das informações atualizadas
        // E quando tiver diferenças, utilizar o RevisionData da revisão anterior.
        $this->user = App::i()->user;

    }



    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
