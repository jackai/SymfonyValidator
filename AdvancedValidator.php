<?php

namespace Jackai\Validator;

/**
 * Class AdvancedValidator
 *
 * @package Jackai\Validator
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class AdvancedValidator
{
    public $form = [];
    public $query = [];
    public $requireForm = [];
    public $requireQuery = [];
    public $requireFormCode;
    public $requireQueryCode;
    public $throwOnValidateFail;
    public $throwOnMissingValidate;
    public $emptyStringIsUndefined;
    public $shortErrorMsg;

    public $arraySetters = ['form', 'query', 'requireForm', 'requireQuery'];
    public $setters = [
        'requireFormCode',
        'requireQueryCode',
        'throwOnValidateFail',
        'throwOnMissingValidate',
        'emptyStringIsUndefined',
        'shortErrorMsg'
    ];

    public function __construct(array $data)
    {
        foreach ($this->arraySetters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = is_array($data[$v]) ? $data[$v] : [$data[$v]];
            }
        }

        foreach ($this->setters as $k => $v) {
            if (isset($data[$v])) {
                $this->$v = $data[$v];
            }
        }
    }
}
