<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Defines that the entity has taxonomies.
 *
 * Use the property $terms to set terms to the entity.
 *
 *  // Example of the $terms property
 *  array(
 *      'tag' => array('Music', 'Guitar'),
 *      'category' => array('Jazz', 'Rock')
 *  )
 * </code>
 *
 * @example To remove all tags of the entity set $entity->terms['tag'] = array() and save the entity or $entity->saveTerms().
 * @example To set tags 'music', 'photo' and 'video' set $entity->terms['tag'] = array('music', 'photo', 'video') and save the entity or $entity->saveTerms()
 * @example To add the tag 'music' just do $entity->terms['tag'][] = 'music' and save the entity or $entity->saveTerms()
 *
 * @property \MapasCulturais\Entities\Term[] $taxonomyTerms Description.
 * @property array $terms array of terms string grouped by taxonomy slug. ex: array('tag' => array('Music', 'Dance'))
 */
trait EntityTaxonomies{
    /**
     * This property is used to set terms to the entity.
     *
     *
     * <code>
     *  // Example of the $terms property
     *  array(
     *      'tag' => array('Music', 'Guitar'),
     *      'category' => array('Jazz', 'Rock')
     *  )
     * </code>
     *
     * @example To remove all tags of the entity set $entity->terms['tag'] = array() and save the entity or $entity->saveTerms().
     * @example To set tags 'music', 'photo' and 'video' set $entity->terms['tag'] = array('music', 'photo', 'video') and save the entity or $entity->saveTerms()
     * @example To add the tag 'music' just do $entity->terms['tag'][] = 'music' and save the entity or $entity->saveTerms()
     *
     * @var array the taxonomy terms
     */
    protected $terms = null;

    /**
     * This entity has taxonomies
     *
     * @return bool true
     */
    static function usesTaxonomies(){
        return true;
    }

    /**
     * Returns the terms of this entity grouped by taxonomy slugs
     *
     * <code>
     *  // Example of returned array
     *  array(
     *      'tag' => array('Music', 'Guitar'),
     *      'category' => array('Jazz', 'Rock')
     *  )
     * </code>
     *
     * @return array
     */
    function getTerms(){
        if(is_null($this->terms)){
            $this->populateTermsProperty();
        }
        return $this->terms;
    }

    /**
     * Populates the terms property with values associated with this entity
     */
    protected function populateTermsProperty(){
        if(is_null($this->terms))
            $this->terms = new \ArrayObject();

        foreach ($this->taxonomyTerms as $taxonomy_slug => $terms){
            $this->terms[$taxonomy_slug] = array();
            foreach($terms as $term)
                $this->terms[$taxonomy_slug][] = $term->term;

        }
    }

    function getTaxonomiesValidationErrors(){
        $taxonomies = App::i()->getRegisteredTaxonomies($this);
        $errors = array();
        foreach($taxonomies as $definition){
            if($definition->required && empty($this->terms[$definition->slug])){
                $errors['term-'.$definition->slug] = array($definition->required);
            }
        }
        return $errors;
    }

    /**
     * Saves the terms in the way they are on the property terms.
     *
     * This method creates or removes the associations with te terms as needed.
     *
     */
    function saveTerms(){
        if(!$this->terms)
            return false;

        $app = App::i();

        // temporary array
        $taxonomy = $this->terms;

        foreach($this->taxonomyTerms as $slug => $terms){
            foreach($terms as $term){
                // if the term is in the terms property and the association already exists,
                if(in_array($term->term, $taxonomy[$slug])){
                    $i = array_search($term->term, $taxonomy[$slug]);
                    // removes the term of the temporary array because is not necessary to add it
                    unset($taxonomy[$slug][$i]);

                // if a term with an existent relation is not in the terms property, removes the relation.
                }else{
                    $tr = $app->repo('TermRelation')->findOneBy(array('term' => $term, 'objectType' => $this->getClassName(), 'objectId' => $this->id));
                    if($tr)
                        $tr->delete(true);
                }
            }
        }

        // now creates relations to the terms in the temporary array
        foreach($taxonomy as $slug => $terms){
            foreach($terms as $term){
                $this->addTerm($slug, $term);
            }
        }



        $class = $this->getClassName();
        $cache_id = "{$this->className}:{$this->id}:taxonomyTerms";

        $app->cache->delete($cache_id);
    }

    /**
     * Adds a term to the entity. If the term does not exists and the definition of the taxonomy allow insertion, first creates it.
     *
     * @param string $taxonomy_slug the taxonomy slug (like tag)
     * @param string $term the term to add (like music)
     * @param string $description (optional) the description of the term. Used only on insertion of new term.
     *
     * @return bool true if the term was added to the entity, false if not.
     */
    protected function addTerm($taxonomy_slug, $term, $description = ''){
        $app = App::i();

        $term = trim($term);

        // if this entity uses this taxonomy
        if($definition = $app->getRegisteredTaxonomy($this, $taxonomy_slug)){
            $t = $app->repo('Term')->findOneBy(array('taxonomy' => $definition->id, 'term' => $term));
            $tr = $app->repo('TermRelation')->findOneBy(array('term' => $t, 'objectType' => $this->getClassName(), 'objectId' => $this->id));

            // if the term is already associated to this entity return
            if($tr){
                return true;

            // else if the term exists, create de association
            }elseif($t){
                $tr = new \MapasCulturais\Entities\TermRelation;
                $tr->term = $t;
                $tr->objectType = $this->getClassName();
                $tr->objectId = $this->id;

                $tr->save(true);
                return true;

            // else if the term does not exists but the taxonomy definition allow insertion, create de term and the association
            }elseif($definition->allowInsert || key_exists(strtolower(trim($term)), $definition->restrictedTerms) ){

                // if not allowed to insert terms, get the term in the way as defined in restrictedTerms
                if(!$definition->allowInsert)
                    $term = $definition->restrictedTerms[strtolower(trim($term))];

                $t = new \MapasCulturais\Entities\Term;
                $t->term = $term;
                $t->taxonomy = $definition->id;
                $t->description = $description;

                $t->save();

                $tr = new \MapasCulturais\Entities\TermRelation;
                $tr->term = $t;
                $tr->objectType = $this->getClassName();
                $tr->objectId = $this->id;

                $tr->save(true);
                return true;

            // else if the term not exists and the taxonomy definition not allow insertion, return false
            }else{
                return false;
            }
        // if this entity not uses this taxonomy
        }else{
            return false;
        }
    }


    /**
     * Return the term entities associated to this entity.
     *
     * @return \MapasCulturais\Entities\Term[] array of terms
     */
    function getTaxonomyTerms($taxonomy_slug = null){
        $app = App::i();
        $class = $this->getClassName();
        $cache_id = "{$this->className}:{$this->id}:taxonomyTerms";

        $result = array();

        $taxonomies = $app->getRegisteredTaxonomies($this);
        foreach($taxonomies as $tax)
            $result[$tax->slug] = array();

        if($app->cache->contains($cache_id)){
            $terms = $app->cache->fetch($cache_id);
        }else{

            if(!$this->id)
                return $result;


            $query = $app->em->createQuery("
                SELECT
                    t
                FROM
                    \MapasCulturais\Entities\Term t
                    LEFT JOIN t.relations tr
                WHERE
                    tr.objectType = :class AND
                    tr.objectId = :oid
                ORDER BY
                    t.term ASC");

            $query->setParameters(array(
                'class' => $class,
                'oid' => $this->id
            ));

           $terms = $query->getResult();

           $app->cache->save($cache_id, $terms, $app->objectCacheTimeout());
        }

        if($terms){

            foreach($terms as $t){

                $taxonomy = $app->getRegisteredTaxonomyById($t->taxonomy);
                if(!key_exists($taxonomy->slug, $result))
                        $result[$taxonomy->slug] = array();

                $result[$taxonomy->slug][] = $t;
            }
        }

        if($taxonomy_slug){
            return key_exists($taxonomy_slug, $result) ? $result[$taxonomy_slug] : array();
        }else{
            return $result;
        }
    }
}