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
    public $requireFormErrorCode = 0;
    public $requireQueryErrorCode = 0;
    public $throwOnValidateFail = true;
    public $throwOnMissingValidate = true;
    public $emptyStringIsUndefined = true;

    private $arraySetters = ['form', 'query', 'requireForm', 'requireQuery'];
    private $setters = ['requireFormErrorCode', 'requireQueryErrorCode', 'throwOnValidateFail', 'throwOnMissingValidate', 'emptyStringIsUndefined'];

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
