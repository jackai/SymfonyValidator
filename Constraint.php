<?php

namespace Jackai\Validator;

use Doctrine\Persistence\ManagerRegistry;

abstract class Constraint extends \Symfony\Component\Validator\Constraint
{
    /**
     * @var array $rawValues Raw request/query values
     */
    public $rawValues = [];

    /**
     * @var ManagerRegistry|null Doctrine object
     */
    public $doctrine = null;

    /**
     * @var string path
     */
    public $path = null;
}
