# Jackai Symfony Validator

[![CircleCI](https://circleci.com/gh/jackai/SymfonyValidator/tree/master.svg?style=svg)](https://circleci.com/gh/jackai/SymfonyValidator/tree/master)

Validate request on symfony controller annotation.

## Installation
1.Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

```
composer require jackai/symfony-validator
```

2.Open config/services.yaml and add this config:

```
services:
    Jackai\Validator\RequestAdvancedValidateListener:
        arguments:
          - {
            doctrine: "@doctrine",
            ruleAlias: {
              "Assert": "Symfony\\Component\\Validator\\Constraints",
              "My": "App\\Validator"
            },
            requireFormCode: null,
            requireQueryCode: null,
            throwOnValidateFail: true,
            throwOnMissingValidate: false,
            emptyStringIsUndefined: true,
            shortErrorMsg: false,
          }
        tags:
            - { name: kernel.event_listener, event: kernel.request }
```

## Useage

### 驗證器參數說明
* throwOnMissingValidate: 當帶入的參數不存在驗證列表時，是否拋出例外，預設為 `false`
* throwOnValidateFail: 當驗證失敗時，是否拋出例外，預設為 `true`
* emptyStringIsUndefined: 帶入的參數為空字串時，是否當作未帶入參數處理，預設為 `true`
* requireQuery: 在query中必填的欄位
* requireForm: 在form中必填的欄位
* requireQueryCode: 當query中必填未填寫時，要拋出的錯誤代碼，預設為 `null`
* requireFormCode: 當form中必填未填寫時，要拋出的錯誤代碼，預設為 `null`
* query: 在query中欄位驗證規則
* form: 在form中欄位驗證規則

### query及form驗證規則說明
在query跟form參數中的驗證規則如下：
* name: 欄位名稱，可以用 `.` 來串接欄位名稱，例如參數為 `config[abc]` 就可寫為 `config.abc`
* dataType: 轉換欄位型態
* rule: 驗證規則，目前有三種能用
    * Assert\\*: 為symfony的驗證器，可參考官方網站的列表: (https://symfony.com/doc/current/validation.html#constraints)
    * 自製的symfony的驗證器: 在參數中寫入Class位置即可，例如： `App\Validator\Constraints\ContainsAlphanumeric`，詳細說明可參考symfony官方網站 (https://symfony.com/doc/current/validation/custom_constraint.html)
    * require: 特殊規則的必填欄位
* ruleOption: 驗證規則的參數設置
* code: 條件驗證失敗時回傳的錯誤代碼，預設為 `null`
* msg: 條件驗證失敗時回傳的錯誤訊息，預設為 `驗證規則提供的錯誤訊息`
* require: 是否為必填欄位，預設值為 `false`
* default: 當欄位未填寫時，預設傳入 `controller` 的值

### 特殊規則的必填欄位
當 `"rule" = "require"` 時，為驗證特殊必填狀況，特殊必填情況符合時 `name` 欄位為必填。

特殊必填有以下幾個模式：
* 指定的欄位值等於其中一個數值: `"mode" = "if", "values" = {"指定欄位", "數值1", "數值2",,, "數值N"}`
* 其中一個指定欄位有值: `"mode" = "with", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}`
* 全部指定欄位都有值: `"mode" = "withAll", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}`
* 指定的欄位其中一個沒有值: `"mode" = "without", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}`
* 指定的欄位全部沒有值: `"mode" = "withoutAll", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}`

### 使用範例
```
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Jackai\Validator\AdvancedValidator;

class TestController extends AbstractController
{
     /**
      * @Route("/test")
      * @AdvancedValidator(
      *     throwOnMissingValidate = true,
      *     throwOnValidateFail = true,
      *     emptyStringIsUndefined = true,
      *     requireQuery = {"name"},
      *     requireQueryCode = 998,
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "113", "msg" = "Invalid picture"},
      *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999"}}, "code" = "118", "msg" = "If price is 999, you should private picture."},
      *     },
      *     requireForm = {"postParamName"},
      *     requireFormCode = 999,
      *     form = {
      *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid post_field"},
      *     }
      * )
      */
    public function test(Request $request)
    {
        // do something
    }
}
```

### Create custom validation

After creating a validator using the example on the official Symfony website (https://symfony.com/doc/current/validation/custom_constraint.html), add the following line in the annotation.

```
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Jackai\Validator\AdvancedValidator;

class TestController extends AbstractController
{
     /**
      * @Route("/test")
      * @AdvancedValidator(
      *     throwOnMissingValidate = true,
      *     throwOnValidateFail = true,
      *     emptyStringIsUndefined = true,
      *     requireQuery = {"name"},
      *     requireQueryCode = 998,
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
+      *         {"name" = "name", "rule" = "App\Validator\Constraints\ContainsAlphanumeric", "code" = "114", "msg" = "Name should be alphanumeric"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "113", "msg" = "Invalid picture"},
      *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999"}}, "code" = "118", "msg" = "If price is 999, you should private picture."},
      *     },
      *     requireForm = {"postParamName"},
      *     requireFormCode = 999,
      *     form = {
      *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid post_field"},
      *     }
      * )
      */
    public function test(Request $request)
    {
        // do something
    }
}
```

### Create advanced custom validation

1. Use Jackai\Validator\Constraint
1. You can get rawValues and doctrine in constraint
1. Don't forget to add alias: `"My": "App\\Validator"`
1. And you can use `"rule" = "My\DataUnique"`

src\Validator\DataUnique.php
```
namespace App\Validator;

use Jackai\Validator\Constraint;

class DataUnique extends Constraint
{
    public $message = 'The attribute "{{ path }}" data "{{ string }}" is duplicate.';
    public $options = null;
    public $entity = null;
    public $rule = null;

    public function __construct($options = null)
    {
        $this->options = $options;
        parent::__construct($options);
    }
}

```

src\Validator\DataUniqueValidator.php
```
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DataUniqueValidator extends ConstraintValidator
{
   public function validate($value, Constraint $constraint)
   {
       if (!$constraint instanceof DataUnique) {
           throw new UnexpectedTypeException($constraint, DataUnique::class);
       }

       if ($this->checkDataBase($constraint)) {
           $this->context->buildViolation($constraint->message)
               ->setParameter('{{ path }}', $path)
               ->setParameter('{{ string }}', $value)
               ->addViolation();
       }
   }

   private function checkDataBase(DataUnique $constraint)
   {
       $ruleOption = $constraint->options;
       foreach (['entity', 'rule'] as $k => $v) {
           if (!array_key_exists($v, $ruleOption)) {
               throw new \RuntimeException("ruleOption.{$v} is require.");
           }
       }

       $entity = $ruleOption['entity'];
       $rule = $ruleOption['rule'];
       $values = $constraint->rawValues;
       $doctrine = $constraint->doctrine;
       $em = array_key_exists('em', $ruleOption) ? $ruleOption['em'] : 'default';
       $search = [];

       foreach ($rule as $k => $v) {
           if (strncasecmp($v, ":", 1) === 0) {
               $search[$k] = array_key_exists($k, $values) ? $values[$k] : null;
           }

           if (strncasecmp($v, ":", 1) !== 0) {
               $search[$k] = $v;
           }

           $search[$k] = str_replace('\:', ':', $search[$k]);
       }

       $data = $doctrine->getManager($em)->getRepository("App\\Entity\\{$entity}")->findOneBy($search);

       return $data !== null;
   }
}
```

config/service.yaml
```
    Jackai\Validator\RequestAdvancedValidateListener:
        arguments:
          - {
            doctrine: "@doctrine",
            ruleAlias: {
              "Assert": "Symfony\\Component\\Validator\\Constraints",
              "My": "App\\Validator"
            },
          }
        tags:
            - { name: kernel.event_listener, event: kernel.request }
```

src/Controller/TestController.php
```
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Jackai\Validator\AdvancedValidator;

class TestController extends AbstractController
{
     /**
      * @Route("/test")
      * @AdvancedValidator(
      *     throwOnMissingValidate = true,
      *     throwOnValidateFail = true,
      *     emptyStringIsUndefined = true,
      *     requireQuery = {"name"},
      *     requireQueryCode = 998,
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
+      *         {"name" = "name", "rule" = "My\DataUnique", "ruleOption" = {"entity" = "Product", "rule" = {"name" = ":name"}}, "errorCode" = "119"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "113", "msg" = "Invalid picture"},
      *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999"}}, "code" = "118", "msg" = "If price is 999, you should private picture."},
      *     },
      *     requireForm = {"postParamName"},
      *     requireFormCode = 999,
      *     form = {
      *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid post_field"},
      *     }
      * )
      */
    public function test(Request $request)
    {
        // do something
    }
}
```
