<?php
namespace MapasCulturais;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Repository extends \Doctrine\ORM\EntityRepository{
    use Traits\MagicCallers;
    
    function findAllPaged($limit, $page) {
        $class = $this->getClassName();
        if($page === 0) return [];
        
        $first = ($page-1) * $limit;
        
        $dql = "SELECT e FROM {$class} e ORDER BY e.id ASC";
        $query = $this->_em->createQuery($dql)
                               ->setFirstResult($first)
                               ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return  $paginator->getIterator()->getArrayCopy();
    }
}