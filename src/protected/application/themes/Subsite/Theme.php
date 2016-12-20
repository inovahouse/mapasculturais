<?php
namespace Subsite;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    static protected $config;

    protected $subsitePath;

    /**
     * Subsite Instance
     *
     * @var \MapasCulturais\Entities\Subsite
     */
    protected $subsiteInstance;

    public function __construct(\MapasCulturais\AssetManager $asset_manager, \MapasCulturais\Entities\Subsite $subsiteInstance) {
        $this->subsiteInstance = $subsiteInstance;

        parent::__construct($asset_manager);
    }

    protected static function _getTexts(){
        $app = App::i();

        $subsite = $app->getCurrentSubsite();

        $result = parent::_getTexts();

        if(is_array($subsite->dict)){
            $subsite_texts = $subsite->dict;
        }

        foreach($subsite_texts as $key => $val){
            if($val){
                $result[$key] = $val;
            }
        }
        return $result;
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();

        $this->subsitePath = SAAS_PATH . '/' . $this->subsiteInstance->url;

        $this->addPath($this->subsitePath);

        $cache_id = $this->subsiteInstance->getSassCacheId();

        if($app->isEnabled('subsite') && !$app->msCache->contains($cache_id)){
            $app->cache->deleteAll();
            if(!is_dir($this->subsitePath . '/assets/css/sass/')) {
                mkdir($this->subsitePath . '/assets/css/sass/',0755,true);
            }
            putenv('LC_ALL=en_US.UTF-8');

            if ($this->subsiteInstance->namespace == 'Subsite'){
                $variables_scss = "";
                $main_scss = '// Child theme main
                @import "variables";
                @import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
                ';

                if($institude = $this->subsiteInstance->institute){
                    $main_scss .= "
                    .header-image {
                        background-image: url({$institude->url});
                    }";
                }

                if($bg = $this->subsiteInstance->background){

    //                $bg = $bg->transform('backgroundFull');
                    $main_scss .= "
                    #home-watermark {
                        background-image: url({$bg->url});
                    }";
                }

                $variables_scss .= "\$brand-agent:   " . ($this->subsiteInstance->cor_agentes?  $this->subsiteInstance->cor_agentes:  $app->config['themes.brand-agent'])   . " !default;\n";
                $variables_scss .= "\$brand-project: " . ($this->subsiteInstance->cor_projetos? $this->subsiteInstance->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
                $variables_scss .= "\$brand-event:   " . ($this->subsiteInstance->cor_eventos?  $this->subsiteInstance->cor_eventos:  $app->config['themes.brand-event'])   . " !default;\n";
                $variables_scss .= "\$brand-space:   " . ($this->subsiteInstance->cor_espacos?  $this->subsiteInstance->cor_espacos:  $app->config['themes.brand-space'])   . " !default;\n";
                $variables_scss .= "\$brand-seal:    " . ($this->subsiteInstance->cor_selos?    $this->subsiteInstance->cor_selos:    $app->config['themes.brand-seal'])    . " !default;\n";
                $variables_scss .= "\$brand-subsite: " . ($this->subsiteInstance->cor_subsite?  $this->subsiteInstance->cor_subsite:  $app->config['themes.brand-subsite']) . " !default;\n";
                $variables_scss .= "\$brand-primary: " . ($this->subsiteInstance->cor_intro?    $this->subsiteInstance->cor_intro:    $app->config['themes.brand-intro'])   . " !default;\n";
                $variables_scss .= "\$brand-developer:" . ($this->subsiteInstance->cor_dev?     $this->subsiteInstance->cor_dev:      $app->config['themes.brand-developer']) . " !default;\n";

                file_put_contents($this->subsitePath . '/assets/css/sass/_variables.scss', $variables_scss);
                file_put_contents($this->subsitePath . '/assets/css/sass/main.scss', $main_scss);

                exec("sass " . $this->subsitePath . '/assets/css/sass/main.scss ' . $this->subsitePath . '/assets/css/main.css');
            } else {
                $assets_path = $app->config['namespaces'][$this->subsiteInstance->namespace] . "/assets/";
                if(file_exists($assets_path.'css/sass/main.scss'))
                    exec("sass " . $assets_path.'css/sass/main.scss ' . $this->subsitePath . '/assets/css/main.css');
            }
            $app->msCache->save($cache_id, true);
        }

        parent::_init();

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });
    }

    protected function _publishAssets() {
        if($this->subsiteInstance->getLogo()) {
            $this->jsObject['assets']['logo-instituicao'] = $this->subsiteInstance->logo->url;
        } else {
            $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
        }
    }
}
