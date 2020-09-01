<?php

namespace Jackai\Validator;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\Validation;

class RequestValidateListener
{
    private $throwOnValidateFail;
    private $throwOnMissingValidate;
    private $emptyStringIsUndefined;
    private $paramRule = [];
    private $queryRule = [];
    private $ruleRequireParam = [];
    private $ruleRequireQuery = [];

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
        $reflectedMethod = new \ReflectionMethod(new $controllerService(), $controllerMethod);

        // the annotations
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotation($reflectedMethod, Validator::class);

        if (!$annotations) {
            // 不存在要驗證的參數則跳過
            return;
        }

        $this->throwOnValidateFail = $annotations->throwOnValidateFail;
        $this->throwOnMissingValidate = $annotations->throwOnMissingValidate;
        $this->emptyStringIsUndefined = $annotations->emptyStringIsUndefined;

        // 驗證必填欄位
        $this->checkRequired($annotations->requireParam, $requset->request->all(), $annotations->requireParamErrorCode);
        $this->checkRequired($annotations->requireQuery, $requset->query->all(), $annotations->requireQueryErrorCode);

        foreach ($annotations->param as $k => $v) {
            if ($v['rule'] == 'require') {
                array_push($this->ruleRequireParam, $v);
                continue;
            }

            if (!array_key_exists($v['name'], $this->paramRule)) {
                $this->paramRule[$v['name']] = [];
            }

            array_push($this->paramRule[$v['name']], $v);
        }

        foreach ($annotations->query as $k => $v) {
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
        $this->ruleRequireCheck($this->ruleRequireParam, $requset->request->all());
        $this->ruleRequireCheck($this->ruleRequireQuery, $requset->query->all());

        // 填充預設值
        $requset->request->replace($this->fillingData($this->paramRule, $requset->request->all()));
        $requset->query->replace($this->fillingData($this->queryRule, $requset->query->all()));

        // 驗證欄位值
        $this->recursiveValidate($this->paramRule, $requset->request->all());
        $this->recursiveValidate($this->queryRule, $requset->query->all());
    }

    /**
     * 驗證必填欄位
     *
     * @param array $requiredColumn
     * @param array $data
     * @param integer $errorCode
     * @throws \InvalidArgumentException
     */
    private function checkRequired($requiredColumn, $data, $errorCode)
    {
        if ($this->throwOnValidateFail) {
            foreach ($requiredColumn as $path) {
                if (!$this->recursiveValidateRequired($data, $path)) {
                    throw new \InvalidArgumentException("$path is required", $errorCode);
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

            if (!array_key_exists('ruleOption', $rule)) {
                throw new \ErrorException('ruleOption is require: ' . json_encode($rule));
            }

            // 如果目標必填欄位已經填寫了，那就略過
            if ($this->recursiveValidateRequired($data, $rule['name'])) {
                continue;
            }

            $ruleOption = $rule['ruleOption'];

            foreach (['values', 'mode'] as $v) {
                if (!array_key_exists($v, $ruleOption)) {
                    throw new \ErrorException(sprintf('Rule missing %s error: %s', $v, json_encode($rule)));
                }
            }

            if (!is_array($ruleOption['values']) || count($ruleOption['values']) < 1) {
                throw new \ErrorException('ruleOption.value error' . json_encode($rule));
            }

            $errorCode = isset($rule['errorCode']) ? $rule['errorCode'] : null;
            $errorMsg = isset($rule['errorMsg']) ? $rule['errorMsg'] : "{$rule['name']} is required";

            switch ($ruleOption['mode']) {
                case 'if':
                    if (count($ruleOption['values']) < 2) {
                        throw new \ErrorException('ruleOption.value error' . json_encode($rule));
                    }

                    $checkValue = $ruleOption['values'];
                    $targetField = $checkValue[0];
                    array_shift($checkValue);

                    // 如果條件欄位不存在則略過
                    if (!$this->recursiveValidateRequired($data, $targetField)) {
                        continue;
                    }

                    $fieldValue = $this->getValue($data, $targetField);

                    if(in_array($fieldValue, $checkValue)) {
                        throw new \InvalidArgumentException($errorMsg, $errorCode);
                    }

                    break;

                case 'with':
                    foreach ($ruleOption['values'] as $checkValue) {
                        if ($this->recursiveValidateRequired($data, $checkValue)) {
                            throw new \InvalidArgumentException($errorMsg, $errorCode);
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
                        throw new \InvalidArgumentException($errorMsg, $errorCode);
                    }

                    break;

                case 'without':
                    foreach ($ruleOption['values'] as $checkValue) {
                        if (!$this->recursiveValidateRequired($data, $checkValue)) {
                            throw new \InvalidArgumentException($errorMsg, $errorCode);
                        }
                    }

                    break;

                case 'withoutAll':
                    $count = 0;

                    foreach ($ruleOption['values'] as $checkValue) {
                        if (!$this->recursiveValidateRequired($data, $checkValue)) {
                            $count += 1;
                        }
                    }

                    if ($count == 0) {
                        throw new \InvalidArgumentException($errorMsg, $errorCode);
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
     * @param $rules
     * @param $value
     * @param string $path
     * @throws \InvalidArgumentException
     */
    private function recursiveValidate($rules, $value, $path = '')
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $recursivePath = $path == '' ? $k : "$path.$k";
                $this->recursiveValidate($rules, $v, $recursivePath);
            }
            return;
        }

        if (!array_key_exists($path, $rules)) {
            if ($this->throwOnMissingValidate) {
                throw new \InvalidArgumentException("$path missing validate");
            }

            return;
        }

        $columnRule = $rules[$path];

        foreach ($columnRule as $rule) {
            $validator = Validation::createValidator();

            $ruleOption = array_key_exists('ruleOption', $rule) ? $rule['ruleOption'] : null;
            $ruleClass = str_replace('Assert', 'Symfony\Component\Validator\Constraints', $rule['rule']);
            $errors = $validator->validate($value, new $ruleClass($ruleOption));

            if (count($errors) > 0 && $this->throwOnValidateFail) {
                $errorCode = isset($rule['errorCode']) ? $rule['errorCode'] : null;
                $errorMsg = isset($rule['errorMsg']) ? $rule['errorMsg'] : $errors[0]->getMessage();

                throw new \InvalidArgumentException("$path - {$errorMsg}", $errorCode);
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
            return array_key_exists($pathName, $arr) && !($this->emptyStringIsUndefined && $arr[$pathName] == '');
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