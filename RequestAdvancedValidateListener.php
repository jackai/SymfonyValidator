<?php

namespace Jackai\Validator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\Validation;

/**
 * Class RequestAdvancedValidateListener
 *
 * @package Jackai\Validator
 */
class RequestAdvancedValidateListener
{
    private $requireFormCode;
    private $requireQueryCode;
    private $throwOnValidateFail = true;
    private $throwOnMissingValidate = false;
    private $emptyStringIsUndefined = true;
    private $shortErrorMsg = false;
    private $ruleAlias = [];
    private $doctrine;
    private $formRule = [];
    private $queryRule = [];
    private $ruleRequireForm = [];
    private $ruleRequireQuery = [];

    /**
     * RequestAdvancedValidateListener constructor.
     * @param ManagerRegistry|null $doctrine
     * @param bool $throwOnValidateFail
     * @param bool $throwOnMissingValidate
     * @param bool $emptyStringIsUndefined
     * @param bool $shortErrorMsg
     * @param null $requireFormCode
     * @param null $requireQueryCode
     * @param array $ruleAlias
     */
    function __construct($options) {
        $setters = [
            'requireFormCode',
            'requireQueryCode',
            'throwOnValidateFail',
            'throwOnMissingValidate',
            'emptyStringIsUndefined',
            'shortErrorMsg',
            'ruleAlias',
            'doctrine',
        ];

        foreach ($setters as $k => $v) {
            if (array_key_exists($v, $options)) {
                $this->$v = $options[$v];
            }
        }
    }

    /**
     * @param RequestEvent $event
     * @throws \ErrorException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $requset = $event->getRequest();
        $controller = $requset->attributes->get('_controller');
        list($controllerService, $controllerMethod) = explode('::', $controller);

        if (!class_exists($controllerService)) {
            return;
        }

        $reflectedMethod = new \ReflectionMethod(new $controllerService(), $controllerMethod);

        // the annotations
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotation($reflectedMethod, AdvancedValidator::class);

        if (!$annotations) {
            // 不存在要驗證的參數則跳過
            return;
        }

        foreach ($annotations->setters as $k => $v) {
            if ($annotations->$v !== null) {
                $this->$v = $annotations->$v;
            }
        }

        // 驗證必填欄位
        $this->checkRequired($annotations->requireForm, $requset->request->all(), $this->requireFormCode);
        $this->checkRequired($annotations->requireQuery, $requset->query->all(), $this->requireQueryCode);

        foreach ($annotations->form as $k => $v) {
            if (array_key_exists('require', $v) && $v['require']) {
                $tmp = $v;
                unset($tmp['ruleOption']);
                array_push($this->ruleRequireForm, $tmp);
            }

            if (!array_key_exists('rule', $v)) {
                continue;
            }

            if ($v['rule'] == 'require') {
                array_push($this->ruleRequireForm, $v);
                continue;
            }

            if (!array_key_exists($v['name'], $this->formRule)) {
                $this->formRule[$v['name']] = [];
            }

            array_push($this->formRule[$v['name']], $v);
        }

        foreach ($annotations->query as $k => $v) {
            if (array_key_exists('require', $v) && $v['require']) {
                $tmp = $v;
                unset($tmp['ruleOption']);
                array_push($this->ruleRequireQuery, $tmp);
            }

            if (!array_key_exists('rule', $v)) {
                continue;
            }

            if ($v['rule'] == 'require') {
                array_push($this->ruleRequireQuery, $v);
                continue;
            }

            if (!array_key_exists($v['name'], $this->queryRule)) {
                $this->queryRule[$v['name']] = [];
            }

            array_push($this->queryRule[$v['name']], $v);
        }

        // 檢查條件必填欄位
        $this->ruleRequireCheck($this->ruleRequireForm, $requset->request->all());
        $this->ruleRequireCheck($this->ruleRequireQuery, $requset->query->all());

        // 填充預設值
        $requset->request->replace($this->fillingData($this->formRule, $requset->request->all()));
        $requset->query->replace($this->fillingData($this->queryRule, $requset->query->all()));

        // 驗證欄位值
        $this->recursiveValidate($this->formRule, $requset->request->all());
        $this->recursiveValidate($this->queryRule, $requset->query->all());
    }

    /**
     * 驗證必填欄位
     *
     * @param array $requiredColumn
     * @param array $data
     * @param integer $code
     * @throws \InvalidArgumentException
     */
    private function checkRequired($requiredColumn, $data, $code)
    {
        if ($this->throwOnValidateFail) {
            foreach ($requiredColumn as $path) {
                if (!$this->recursiveValidateRequired($data, $path)) {
                    throw new \InvalidArgumentException("$path is required", $code);
                }
            }
        }
    }

    /**
     * 驗證特殊必填規則
     *
     * @param array $rules
     * @param array $data
     *
     * @throws \ErrorException
     * @throws \InvalidArgumentException
     */
    private function ruleRequireCheck($rules, $data)
    {
        foreach ($rules as $rule) {
            if (!array_key_exists('name', $rule)) {
                throw new \ErrorException('name is require: ' . json_encode($rule));
            }

            // 如果目標必填欄位已經填寫了，那就略過
            if ($this->recursiveValidateRequired($data, $rule['name'])) {
                continue;
            }

            $code = isset($rule['code']) ? $rule['code'] : null;
            $defaultMsg = $this->shortErrorMsg ? "Invalid {$rule['name']}" : "{$rule['name']} is required";
            $msg = isset($rule['msg']) ? $rule['msg'] : $defaultMsg;

            // 如果沒有驗證規格，那就是直接必填
            if (!array_key_exists('ruleOption', $rule)) {
                throw new \InvalidArgumentException($msg, $code);
            }

            $ruleOption = $rule['ruleOption'];

            foreach (['values', 'mode'] as $v) {
                if (!array_key_exists($v, $ruleOption)) {
                    throw new \ErrorException(sprintf('Rule missing %s error: %s', $v, json_encode($rule)));
                }
            }

            if (!is_array($ruleOption['values']) || count($ruleOption['values']) < 1) {
                throw new \ErrorException('ruleOption.value error, At least one value :' . json_encode($rule));
            }

            switch ($ruleOption['mode']) {
                case 'if':
                    if (count($ruleOption['values']) < 2) {
                        throw new \ErrorException('ruleOption.value error, There must be at least two value :' . json_encode($rule));
                    }

                    $checkValue = $ruleOption['values'];
                    $targetField = $checkValue[0];
                    array_shift($checkValue);

                    // 如果條件欄位不存在則略過
                    if (!$this->recursiveValidateRequired($data, $targetField)) {
                        break;
                    }

                    $fieldValue = $this->getValue($data, $targetField);

                    if(in_array($fieldValue, $checkValue)) {
                        throw new \InvalidArgumentException($msg, $code);
                    }

                    break;

                case 'with':
                    foreach ($ruleOption['values'] as $checkValue) {
                        if ($this->recursiveValidateRequired($data, $checkValue)) {
                            throw new \InvalidArgumentException($msg, $code);
                        }
                    }

                    break;

                case 'withAll':
                    $count = 0;

                    foreach ($ruleOption['values'] as $checkValue) {
                        if ($this->recursiveValidateRequired($data, $checkValue)) {
                            $count += 1;
                        }
                    }

                    if (count($ruleOption['values']) == $count) {
                        throw new \InvalidArgumentException($msg, $code);
                    }

                    break;

                case 'without':
                    foreach ($ruleOption['values'] as $checkValue) {
                        if (!$this->recursiveValidateRequired($data, $checkValue)) {
                            throw new \InvalidArgumentException($msg, $code);
                        }
                    }

                    break;

                case 'withoutAll':
                    $count = 0;

                    foreach ($ruleOption['values'] as $checkValue) {
                        if ($this->recursiveValidateRequired($data, $checkValue)) {
                            $count += 1;
                        }
                    }

                    if ($count == 0) {
                        throw new \InvalidArgumentException($msg, $code);
                    }

                    break;

                default:
                    throw new \ErrorException('Unknown rule mode: ' . json_encode($rule));
            }
        }
    }

    /**
     * 填充預設值
     *
     * @param array $rules
     * @param array $data
     *
     * @return array
     */
    private function fillingData($rules, $data)
    {
        foreach ($rules as $path => $value) {
            if (!$this->recursiveValidateRequired($data, $path) && isset($value[0]['default'])) {
                $data = $this->setValue($data, $path, $value[0]['default']);
            }
        }

        return $data;
    }

    /**
     * 遞迴驗證資料
     *
     * @param array $rules
     * @param mixed $value
     * @param string $path
     * @param array $rawValues
     * @throws \InvalidArgumentException
     */
    private function recursiveValidate($rules, $value, $path = '', $rawValues = [])
    {
        if (is_array($value) && !isset($value[0])) {
            foreach ($value as $k => $v) {
                $recursivePath = $path == '' ? $k : "$path.$k";
                $rawValues = $rawValues ? $rawValues : $value;
                $this->recursiveValidate($rules, $v, $recursivePath, $rawValues);
            }
            return;
        }

        if (!array_key_exists($path, $rules)) {
            if ($this->throwOnMissingValidate) {
                throw new \InvalidArgumentException("Missing validate: $path");
            }

            return;
        }

        $columnRule = $rules[$path];

        foreach ($columnRule as $rule) {
            $validator = Validation::createValidator();

            $ruleOption = array_key_exists('ruleOption', $rule) ? $rule['ruleOption'] : [];
            $ruleClass = $rule['rule'];

            foreach ($this->ruleAlias as $k => $v) {
                if (strncasecmp($ruleClass, "$k\\", strlen($k) + 1) === 0) {
                    $ruleClass = str_replace($k, $v, $ruleClass);
                    break;
                }
            }

            $value = is_array($value) ? $value : [$value];

            foreach ($value as $v) {
                if ($this->emptyStringIsUndefined && $v === '') {
                    continue;
                }

                $constraint =  new $ruleClass($ruleOption);

                if ($constraint instanceof \Jackai\Validator\Constraint) {
                    $constraint->rawValues = $rawValues;
                    $constraint->doctrine = $this->doctrine;
                }

                $errors = $validator->validate($v, $constraint);

                if (count($errors) > 0 && $this->throwOnValidateFail) {
                    $defaultMsg = $this->shortErrorMsg ? "Invalid $path" : "Invalid $path:" . $errors[0]->getMessage();

                    $code = isset($rule['code']) ? $rule['code'] : null;
                    $msg = isset($rule['msg']) ? $rule['msg'] : $defaultMsg;

                    throw new \InvalidArgumentException($msg, $code);
                }
            }
        }
    }

    /**
     * 遞迴驗證欄位值是否存在
     *
     * @param $arr
     * @param $recursivePath
     * @return boolean
     */
    private function recursiveValidateRequired($arr, $recursivePath)
    {
        if (!is_array($recursivePath)) {
            return $this->recursiveValidateRequired($arr, explode('.', $recursivePath));
        }

        $pathName = $recursivePath[0];

        if (count($recursivePath) == 1) {
            return array_key_exists($pathName, $arr) && !($this->emptyStringIsUndefined && $arr[$pathName] === '');
        }

        if (!array_key_exists($pathName, $arr)) {
            return false;
        }

        array_shift($recursivePath);
        return $this->recursiveValidateRequired($arr[$pathName], $recursivePath);
    }

    /**
     * 遞迴填入資料
     *
     * @param $arr
     * @param $recursivePath
     * @param $value
     * @return mixed
     */
    private function setValue($arr, $recursivePath, $value)
    {
        if (!is_array($recursivePath)) {
            return $this->setValue($arr, explode('.', $recursivePath), $value);
        }

        $pathName = $recursivePath[0];
        if (count($recursivePath) == 1) {
            $arr[$pathName] = $value;

            return $arr;
        }

        if (!array_key_exists($pathName, $arr)) {
            $arr[$pathName] = [];
        }

        array_shift($recursivePath);
        $arr[$pathName] = $this->setValue($arr[$pathName], $recursivePath, $value);

        return $arr;
    }

    /**
     * 遞迴取得資料
     *
     * @param $arr
     * @param $recursivePath
     * @return mixed
     */
    private function getValue($arr, $recursivePath)
    {
        if (!is_array($recursivePath)) {
            return $this->getValue($arr, explode('.', $recursivePath));
        }

        $pathName = $recursivePath[0];

        if (count($recursivePath) == 1) {
            return array_key_exists($pathName, $arr) ? $arr[$pathName] : null;
        }

        if (!array_key_exists($pathName, $arr)) {
            return null;
        }

        array_shift($recursivePath);
        return $this->getValue($arr[$pathName], $recursivePath);
    }
}
