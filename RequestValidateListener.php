<?php

namespace Jackai\Validator;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\Validation;

class RequestValidateListener
{
    private $throwOnValidateFail;
    private $throwOnMissingValidate;
    private $paramRule = [];
    private $queryRule = [];

    /**
     * @param RequestEvent $event
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

        // 驗證必填欄位
        $this->checkRequired($annotations->requireParam, $requset->request->all(), $annotations->requireParamErrorCode);
        $this->checkRequired($annotations->requireQuery, $requset->query->all(), $annotations->requireQueryErrorCode);

        foreach ($annotations->param as $k => $v) {
            if (!array_key_exists($v['name'], $this->paramRule)) {
                $this->paramRule[$v['name']] = [];
            }

            array_push($this->paramRule[$v['name']], $v);
        }

        foreach ($annotations->query as $k => $v) {
            if (!array_key_exists($v['name'], $this->queryRule)) {
                $this->queryRule[$v['name']] = [];
            }

            array_push($this->queryRule[$v['name']], $v);
        }

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
                if (!$this->recursiveValidateRequired($data, explode('.', $path))) {
                    throw new \InvalidArgumentException("$path is required", $errorCode);
                }
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
            if (!$this->recursiveValidateRequired($data, explode('.', $path)) && isset($value[0]['default'])) {
                $recursivePath = explode('.', $path);
                $data = $this->setValue($data, $recursivePath, $value[0]['default']);
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

            $ruleClass = str_replace('Assert', 'Symfony\Component\Validator\Constraints', $rule['rule']);
            $errors = $validator->validate($value, new $ruleClass($rule['ruleOption']));

            if (count($errors) > 0 && $this->throwOnValidateFail) {
                $errorCode = isset($rule['errorCode']) ? $rule['errorCode'] : null;
                $errorMsg = isset($rule['errorMsg']) ? $rule['errorMsg'] : $errors[0]->getMessage();

                throw new \InvalidArgumentException("$path - {$errorMsg}", $errorCode);
            }
        }
    }

    /**
     * 遞迴驗證必填欄位
     *
     * @param $arr
     * @param $recursivePath
     * @return boolean
     */
    private function recursiveValidateRequired($arr, $recursivePath)
    {
        $pathName = $recursivePath[0];

        if (count($recursivePath) == 1) {
            return array_key_exists($pathName, $arr);
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
}