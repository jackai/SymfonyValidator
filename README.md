# Jackai Symfony Validator

Validate request on symfony controller annotation.

## Installation
1.Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

```
composer require jackai/symfony-validator
```

2.Open config/services.yaml and add this config:

```
services:
    Jackai\Validator\RequestValidateListener:
        tags:
            - { name: kernel.event_listener, event: kernel.request }
```

## Useage

### 驗證器參數說明
* throwOnMissingValidate: 當帶入的參數不存在驗證列表時，是否拋出例外
* throwOnValidateFail: 當驗證失敗時，是否拋出例外
* emptyStringIsUndefined: 帶入的參數為空字串時，是否當作未帶入參數處理
* requireQuery: 在query中必填的欄位
* requireForm: 在form中必填的欄位
* requireQueryErrorCode: 當query中必填未填寫時，要拋出的錯誤代碼
* requireFormErrorCode: 當form中必填未填寫時，要拋出的錯誤代碼
* query: 在query中欄位驗證規則
* form: 在form中欄位驗證規則

### query及form驗證規則說明
在query跟form參數中的驗證規則如下：
* name: 欄位名稱，可以用 `.` 來串接欄位名稱，例如參數為 `config[abc]` 就可寫為 `config.abc`
* rule: 驗證規則，目前有三種能用
    * Assert\\*: 為symfony的驗證器，可參考官方網站的列表: (https://symfony.com/doc/current/validation.html#constraints)
    * 自製的symfony的驗證器: 在參數中寫入Class位置即可，例如： `App\Validator\Constraints\ContainsAlphanumeric`，詳細說明可參考symfony官方網站 (https://symfony.com/doc/current/validation/custom_constraint.html)
    * require: 特殊規則的必填欄位
* ruleOption: 驗證規則的參數設置
* errorCode: 條件驗證失敗時回傳的錯誤代碼，預設為 `null`
* errorMsg: 條件驗證失敗時回傳的錯誤訊息，預設為 `驗證規則提供的錯誤訊息`

### 特殊規則的必填欄位
當 `"rule" = "require"` 時，為驗證特殊必填狀況，當特殊必填情況符合時 `name` 欄位為必填。特殊必填有以下幾個模式：
* `"mode" = "if", "values" = {"指定欄位", "數值1", "數值2",,, "數值N"}`: 當指定的欄位值等於其中一個數值時。
* `"mode" = "with", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}` 。當其中一個指定欄位有值時。
* `"mode" = "withAll", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}` 。當全部指定欄位都有值時。
* `"mode" = "without", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}` 。當指定的欄位其中一個沒有值時。
* `"mode" = "withoutAll", "values" = {"指定欄位A", "指定欄位B",,, "指定欄位N"}` 。當指定的欄位全部沒有值時。

### 使用範例
```
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Jackai\Validator\Validator;

class TestController extends AbstractController
{
     /**
      * @Route("/test")
      * @Validator(
      *     throwOnMissingValidate = true,
      *     throwOnValidateFail = true,
      *     emptyStringIsUndefined = true,
      *     requireQuery = {"name"},
      *     requireQueryErrorCode = 998,
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid name"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "errorCode" = "112", "default" = "99999", "errorMsg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "113", "errorMsg" = "Invalid picture"},
      *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "name"}}, "errorCode" = "118", "errorMsg" = "If price is 999, you should private picture."},
      *     }
      *     requireForm = {"postParamName"},
      *     requireFormErrorCode = 999,
      *     form = {
      *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid post_field"},
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
use Jackai\Validator\Validator;

class TestController extends AbstractController
{
     /**
      * @Route("/test")
      * @Validator(
      *     throwOnMissingValidate = true,
      *     throwOnValidateFail = true,
      *     emptyStringIsUndefined = true,
      *     requireQuery = {"name"},
      *     requireQueryErrorCode = 998,
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid name"},
+      *         {"name" = "name", "rule" = "App\Validator\Constraints\ContainsAlphanumeric", "errorCode" = "114", "errorMsg" = "Name should be alphanumeric"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "errorCode" = "112", "default" = "99999", "errorMsg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "113", "errorMsg" = "Invalid picture"},
      *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "name"}}, "errorCode" = "118", "errorMsg" = "If price is 999, you should private picture."},
      *     }
      *     requireForm = {"postParamName"},
      *     requireFormErrorCode = 999,
      *     form = {
      *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid post_field"},
      *     }
      * )
      */
    public function test(Request $request)
    {
        // do something
    }
}
```