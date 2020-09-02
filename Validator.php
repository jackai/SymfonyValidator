<?php

namespace Jackai\Validator;

/**
 * @Annotation
 */
class Validator
{
    public $form = [];
    public $query = [];
    public $requireForm = [];
    public $requireQuery = [];
    public $requireFormErrorCode = null;
    public $requireQueryErrorCode = null;
    public $throwOnValidateFail = true;
    public $throwOnMissingValidate = true;
    public $emptyStringIsUndefined = true;


    public function __construct(array $data)
    {
        $arraySetters = ['form', 'query', 'requireForm', 'requireQuery'];

        foreach ($arraySetters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = is_array($data[$v]) ? $data[$v] : [$data[$v]];
            }
        }

        $setters = ['requireFormErrorCode', 'requireQueryErrorCode', 'throwOnValidateFail', 'throwOnMissingValidate'];

        foreach ($setters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = $data[$v];
            }
        }
    }
}
