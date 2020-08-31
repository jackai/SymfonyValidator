# Jackai Symfony Validator

Validate request on symfony controller annotation.

## Installation
Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

```
composer require jackai/symfony-validator
```

Open config/services.yaml and add this config:
```
services:
    Jackai\Validator\RequestValidateListener:
        tags:
            - { name: kernel.event_listener, event: kernel.request }
```

## Useage
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
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid name"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "errorCode" = "112", "default" = "99999", "errorMsg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "113", "errorMsg" = "Invalid picture"},
      *     }
      *     requireParam = {"postParamName"},
      *     param = {
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

## Create custom validation

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
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid name"},
+      *         {"name" = "name", "rule" = "App\Validator\Constraints\ContainsAlphanumeric", "errorCode" = "114", "errorMsg" = "Name should be alphanumeric"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "errorCode" = "112", "default" = "99999", "errorMsg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "113", "errorMsg" = "Invalid picture"},
      *     }
      *     requireParam = {"postParamName"},
      *     param = {
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