<?php

namespace MapasCulturais\Types\DoctrineMap;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

class STMakePoint extends AbstractSpatialDQLFunction
{
    protected $platforms = array('postgresql');

    protected $functionName = 'ST_MakePoint';

    protected $minGeomExpr = 2;

    protected $maxGeomExpr = 2;
}
