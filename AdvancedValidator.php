<?php

namespace Jackai\Validator;

/**
 * @Annotation
 */
class AdvancedValidator
{
    public $form = [];
    public $query = [];
    public $requireForm = [];
    public $requireQuery = [];
    public $requireFormCode = 0;
    public $requireQueryCode = 0;
    public $throwOnValidateFail = true;
    public $throwOnMissingValidate = false;
    public $emptyStringIsUndefined = true;

    private $arraySetters = ['form', 'query', 'requireForm', 'requireQuery'];
    private $setters = ['requireFormCode', 'requireQueryCode', 'throwOnValidateFail', 'throwOnMissingValidate', 'emptyStringIsUndefined'];

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
