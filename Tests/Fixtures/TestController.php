<?php

namespace Jackai\Validator\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Jackai\Validator\AdvancedValidator;

class TestController extends AbstractController
{
    /**
     * @Route("/requireQueryOne")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = true,
     *     requireQuery = {"name"},
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *     }
     * )
     */
    public function requireQueryOneAction(Request $request)
    {

    }

    /**
     * @Route("/requireQueryTwo")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = true,
     *     requireQuery = {"name", "price"},
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "msg" = "Invalid price"},
     *     }
     * )
     */
    public function requireQueryTwoAction(Request $request)
    {

    }


    /**
     * @Route("/requireFormOne")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = true,
     *     requireForm = {"name"},
     *     form = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *     }
     * )
     */
    public function requireFormOneAction(Request $request)
    {

    }

    /**
     * @Route("/requireFormTwo")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = true,
     *     requireForm = {"name", "price"},
     *     form = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "msg" = "Invalid price"},
     *     }
     * )
     */
    public function requireFormTwoAction(Request $request)
    {

    }

    /**
     * @Route("/requireQueryCode")
     * @AdvancedValidator(
     *     requireQuery = {"name"},
     *     requireQueryCode = 998,
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "113", "msg" = "Invalid picture"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999"}}, "code" = "118", "msg" = "If price is 999, you should private picture."},
     *     },
     *     requireForm = {"postField"},
     *     requireFormCode = 999,
     *     form = {
     *         {"name" = "postField", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "code" = "111", "msg" = "Invalid post_field"},
     *     }
     * )
     */
    public function requireQueryAndFormAction(Request $request)
    {

    }

    /**
     * @Route("/multiRulesA")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "msg" = "Invalid price"},
     *         {"name" = "price", "rule" = "Assert\LessThan", "ruleOption" = "99", "code" = "113", "msg" = "Invalid price"},
     *     }
     * )
     */
    public function multiRulesAction(Request $request)
    {

    }

    /**
     * @Route("/missingValidate")
     * @AdvancedValidator(
     *     throwOnMissingValidate = true,
     * )
     */
    public function missingValidateAction(Request $request)
    {

    }

    /**
     * @Route("/throwOnValidateFail")
     * @AdvancedValidator(
     *     throwOnValidateFail = false,
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *     }
     * )
     */
    public function throwOnValidateFailAction(Request $request)
    {

    }

    /**
     * @Route("/ruleEmptyStringIsUndefined")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = false,
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *     }
     * )
     */
    public function ruleEmptyStringIsUndefinedAction(Request $request)
    {

    }

    /**
     * @Route("/requireEmptyStringIsUndefined")
     * @AdvancedValidator(
     *     emptyStringIsUndefined = false,
     *     requireQuery = {"name"},
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *     }
     * )
     */
    public function requireEmptyStringIsUndefinedAction(Request $request)
    {

    }

    /**
     * @Route("/requireIf")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999"}}, "code" = "118", "msg" = "If price is 999, you should private picture."},
     *     },
     * )
     */
    public function requireIfAction(Request $request)
    {

    }

    /**
     * @Route("/requireIfWithMultiValue")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "if", "values" = {"price", "999", "9999"}}, "code" = "118", "msg" = "If price is 999 or 9999, you should private picture."},
     *     }
     * )
     */
    public function requireIfWithMultiValueAction(Request $request)
    {

    }

    /**
     * @Route("/requireWith")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "with", "values" = {"price"}}, "code" = "118", "msg" = "If private price, you should private picture."},
     *     }
     * )
     */
    public function requireWithAction(Request $request)
    {

    }

    /**
     * @Route("/requireWithMultiValue")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "with", "values" = {"price", "name"}}, "code" = "119", "msg" = "If private price or name, you should private picture."},
     *     }
     * )
     */
    public function requireWithMultiValueAction(Request $request)
    {

    }

    /**
     * @Route("/requireWith")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "withAll", "values" = {"price"}}, "code" = "118", "msg" = "If private price, you should private picture."},
     *     }
     * )
     */
    public function requireWithAllAction(Request $request)
    {

    }

    /**
     * @Route("/requireWithMultiValue")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "withAll", "values" = {"price", "name"}}, "code" = "119", "msg" = "If private price or name, you should private picture."},
     *     }
     * )
     */
    public function requireWithAllMultiValueAction(Request $request)
    {

    }

    /**
     * @Route("/requireWithout")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "without", "values" = {"price"}}, "code" = "118", "msg" = "If don't private price, you should private picture."},
     *     }
     * )
     */
    public function requireWithoutAction(Request $request)
    {

    }

    /**
     * @Route("/requireWithoutMultiValue")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "without", "values" = {"price", "name"}}, "code" = "119", "msg" = "If don't private price or name, you should private picture."},
     *     }
     * )
     */
    public function requireWithoutMultiValueAction(Request $request)
    {

    }

    /**
     * @Route("/requireWith")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "withoutAll", "values" = {"price"}}, "code" = "118", "msg" = "If don't private price, you should private picture."},
     *     }
     * )
     */
    public function requireWithoutAllAction(Request $request)
    {

    }

    /**
     * @Route("/requireWithMultiValue")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Assert\Length", "ruleOption" = {"min" = 5, "max" = 30}, "code" = "111", "msg" = "Invalid name"},
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"},
     *         {"name" = "picture", "rule" = "require", "ruleOption" = {"mode" = "withoutAll", "values" = {"price", "name"}}, "code" = "119", "msg" = "If don't private price and name, you should private picture."},
     *     }
     * )
     */
    public function requireWithoutAllMultiValueAction(Request $request)
    {

    }

    /**
     * @Route("/default")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "price", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "default" = "99999", "msg" = "Invalid price"}
     *     }
     * )
     */
    public function defaultAction(Request $request)
    {

    }

    /**
     * @Route("/customValidator")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Jackai\Validator\Tests\Fixtures\CustomValidator\Alphanumeric", "code" = "110", "msg" = "Invalid name"}
     *     }
     * )
     */
    public function customValidatorAction(Request $request)
    {

    }

    /**
     * @Route("/struct")
     * @AdvancedValidator(
     *     requireQuery = {"name", "price.shop"},
     *     query = {
     *         {"name" = "name", "rule" = "Jackai\Validator\Tests\Fixtures\CustomValidator\Alphanumeric", "code" = "110", "msg" = "Invalid name"},
     *         {"name" = "price.shop", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "111", "msg" = "Invalid shop price"},
     *         {"name" = "price.promote", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "msg" = "Invalid promote price"}
     *     }
     * )
     */
    public function structAction(Request $request)
    {

    }

    /**
     * @Route("/struct")
     * @AdvancedValidator(
     *     query = {
     *         {"name" = "name", "rule" = "Jackai\Validator\Tests\Fixtures\CustomValidator\Alphanumeric", "code" = "110", "msg" = "Invalid name"},
     *         {"name" = "price.shop", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "111", "msg" = "Invalid shop price"},
     *         {"name" = "price.promote", "rule" = "Assert\GreaterThan", "ruleOption" = "0", "code" = "112", "msg" = "Invalid promote price"},
     *         {"name" = "price.background", "rule" = "Assert\Length", "ruleOption" = {"min" = 1, "max" = 30}, "default" = "red", "code" = "113", "msg" = "Invalid background"},
     *     }
     * )
     */
    public function structDefaultAction(Request $request)
    {

    }

    /**
     * @Route("/test")
     * @AdvancedValidator(
     *     throwOnMissingValidate = true,
     *     throwOnValidateFail = false,
     *     emptyStringIsUndefined = false,
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
    public function allConfigAction(Request $request)
    {

    }
}
