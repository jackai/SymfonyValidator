<?php

namespace Jackai\Validator;

/**
 * @Annotation
 */
class Validator
{
    public $param = [];
    public $query = [];
    public $requireParam = [];
    public $requireQuery = [];
    public $requireParamErrorCode = 0;
    public $requireQueryErrorCode = 0;
    public $throwOnValidateFail = true;
    public $throwOnMissingValidate = true;


    public function __construct(array $data)
    {
        $arraySetters = ['param', 'query', 'requireParam', 'requireQuery'];

        foreach ($arraySetters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = is_array($data[$v]) ? $data[$v] : [$data[$v]];
            }
        }

        $setters = ['requireParamErrorCode', 'requireQueryErrorCode', 'throwOnValidateFail', 'throwOnMissingValidate'];

        foreach ($setters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = $data[$v];
            }
        }
    }
}
