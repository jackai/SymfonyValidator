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

## Example：
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
      *     requireQuery = {"name"},
      *     query = {
      *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "111", "errorMsg" = "Invalid name"},
      *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "errorCode" = "112", "default" = "99999", "errorMsg" = "Invalid price"},
      *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "errorCode" = "113", "errorMsg" = "Invalid picture"},
      *     }
      * )
      */
    public function number(Request $request)
    {
        // do something
    }
}
```