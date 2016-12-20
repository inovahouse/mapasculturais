<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Subsite
 * @property \MapasCulturais\Entities\Agent $owner The owner of this subsite
 *
 * @ORM\Table(name="subsite", indexes={
 *  @ORM\Index(name="url_index", columns={"url"}),
 *  @ORM\Index(name="alias_url_index", columns={"alias_url"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Subsite")
 * @ORM\HasLifecycleCallbacks
 */
class Subsite extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityFiles,
        Traits\EntityMetadata,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="subsite_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alias_url", type="string", length=255, nullable=true)
     */
    protected $aliasUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="verified_seals", type="json_array", nullable=true)
     */
    protected $verifiedSeals = [];
    
    function setVerifiedSeals($val) {
        if(is_string($val)) {
            if(trim($val)){
                $val = explode(';', $val);
            } else {
                $val = [];
            }
        } else if( $val ){
            $val = (array) $val;
        } else {
            $val = [];
        }
        $this->verifiedSeals = $val;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=50, nullable=false)
     */
    protected $namespace = 'Subsite';

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SubsiteMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SubsiteFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SubsiteFile", fetch="EAGER", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;
    
    
    protected $filters = [];
    
    static function getValidations(){
        return [
            'name' => [
                'required' => 'O nome da instalação é obrigatório'
            ],
            'url' => [
                'required' => 'A url da instalação é obrigatória',
                'unique' => 'Esta URL já está sendo utilizada'
            ],
            'aliasUrl' => [
                'unique' => 'Esta URL já está sendo utilizada'
            ]
        ];
    }

    
    public function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }
    
    public function getEditUrl() {
        return $this->getSingleUrl();
    }

    protected $_logo;

    function getLogo(){
        if(!$this->_logo)
            $this->_logo = $this->getFile('logo');

        return $this->_logo;
    }

    protected $_background;

    function getBackground(){

        if(!$this->_background)
            $this->_background = $this->getFile('background');

        return $this->_background;
    }

    protected $_institute;

    function getInstitute(){
        if(!$this->_institute)
            $this->_institute = $this->getFile('institute');

        return $this->_institute;
    }
    
    function getParentIds() {
        $app = App::i();
        
        $cid = "subsite-parent-ids:{$this->id}";
        
        if ($app->cache->contains($cid)) {
            $ids = $app->cache->fetch($cid);
        } else {
            // @TODO: quando o parent estiver implementado fazer percorrer a arvore....
            $ids = [$this->id];
            
            $app->cache->save($cid, $ids, 300);
        }
        
        return $ids;
    }
    
    
    public function applyApiFilters(){
        $app = App::i();

        $subsite_meta = $app->getRegisteredMetadata("MapasCulturais\Entities\Subsite");
        
        
        $IN = ['type'];
        
        foreach($subsite_meta as $k => $v) {
            $meta_name = $k;

            $pos_meta_filter      = strpos($meta_name,"filtro_");
            $pos_meta_controller  = 0;
            $controller           = "";
            $pos_meta_type        = 0;
            $meta_type            = "";

            if($pos_meta_filter === 0) {
                $meta_name = substr($meta_name,strpos($meta_name,"_")+1);
                $pos_meta_controller = strpos($meta_name,"_");
                if($pos_meta_controller > 0) {
                    $controller = substr($meta_name,0,$pos_meta_controller);
                    $meta_name = substr($meta_name,$pos_meta_controller+1);
                    $pos_meta_type = strpos($meta_name,"_");
                    if($pos_meta_type > 0) {
                        $meta_type = substr($meta_name,0,$pos_meta_type);
                        $meta_name = substr($meta_name,$pos_meta_type+1);
                        
                        if($this->$k) {
                            $meta_name = $meta_type == "term"? "term:".$meta_name: $meta_name;
                            $meta_cont = $this->$k;
                            $meta_cont = is_array($meta_cont)? implode(',',$meta_cont): $meta_cont;
                            $this->filters[$controller] = isset($this->filters[$controller]) ? $this->filters[$controller] : [];
                            if(in_array($meta_name, $IN)){
                                $this->filters[$controller][$meta_name] = "IN(" . str_replace(";",",",$meta_cont) . ")";
                                
                            } else {
                                $this->filters[$controller][$meta_name] = "IIN(" . str_replace(";",",",$meta_cont) . ")";
                            }
                        }
                    }
                }
            }
        }
        
        $subsite_id = $app->getCurrentSubsiteId();
        
        $app->applyHookBoundTo($this, 'subsite.applyFilters:before');

        foreach($this->filters as $controller => $entity_filters){
            $cache_id = "subsite:{$controller}:Ids";
            
            $ids = null;
            
            if($app->msCache->contains($cache_id)){
                $ids = $app->msCache->fetch($cache_id);

            } else {
                $subsite_qdata = ['@select' => 'id'];

                foreach($entity_filters as $key => $val){
                    $subsite_qdata[$key] = $val;
                }

                $ids = $app->controller($controller)->apiQuery($subsite_qdata);
                
                if(is_array($ids)){
                    $ids = implode(',',array_map(function($e){ return $e['id']; }, $ids));
                } 
                $app->msCache->save($cache_id, $ids, 60);
            }
            
            
            $app->hook("API.<<*>>({$controller}).query", function(&$qdata, &$select_properties, &$dql_joins, &$dql_where) use($ids, $subsite_id) {
                if($ids){
                    $dql_where .= " AND (e.id IN ($ids) OR e._subsiteId = {$subsite_id})";
                } else {
                    $dql_where .= " AND e._subsiteId = {$subsite_id}";
                }
            });
        }
        
        $app->hook("API.<<*>>(PROJECT).query", function(&$qdata, &$select_properties, &$dql_joins, &$dql_where) use($subsite_id) {
            $dql_where .= " AND e._subsiteId = {$subsite_id}";
        });
        
        $app->applyHookBoundTo($this, 'subsite.applyFilters:after');
    }
    
    
    public function applyConfigurations(&$config){
        $app = App::i();

        $app->applyHookBoundTo($this, 'subsite.applyConfigurations:before', ['config' => &$config]);

        $config['app.verifiedSealsIds'] = $this->verifiedSeals;
        
        if($this->longitude && $this->longitude) {
            $config['maps.center'] = array($this->latitude, $this->longitude);
        }
        
        if($this->zoom_default) {
            $config['maps.zoom.default'] = $this->zoom_default;
        }
        
        if($this->zoom_max){
            $config['maps.zoom.max'] = $this->zoom_max;
        }
        
        if($this->zoom_max){
            $config['maps.zoom.min'] = $this->zoom_max;
        }
                
        $entidades = explode(';', $this->entidades_habilitadas);
        
        if(!in_array('Agentes', $entidades)){
            $config['app.enabled.agents'] = false;
        }

        if (!in_array('Projetos', $entidades)) {
            $config['app.enabled.projects'] = false;
        }

        if (!in_array('Espaços', $entidades)) {
            $config['app.enabled.spaces'] = false;
        }

        if (!in_array('Eventos', $entidades)) {
            $config['app.enabled.events'] = false;
        }
        
        $app->applyHookBoundTo($this, 'subsite.applyConfigurations:after', ['config' => &$config]);

    }
    
    
    public function getSassCacheId(){
        return "Subsite-{$this->id}:_variables.scss";
    }
    
    protected function canUserDestroy($user) {
        return $user->is('saasSuperAdmin');
    }
    
    protected function canUserRemove($user) {
        return $user->is('saasAdmin');
    }
    
    protected function canUserModify($user) {
        return $user->is('superAdmin');
    }
    
    function clearCache(){
        $this->checkPermission('modify');
        
        $app = App::i();
        
        $app->msCache->delete($this->getSassCacheId());
        
        $subsite_cache = clone $app->cache;
        $subsite_cache->deleteAll();
        
    }
    
    public function save($flush = false) {
        parent::save($flush);
        $this->clearCache();
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
