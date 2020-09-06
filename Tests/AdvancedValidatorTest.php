<?php

namespace Jackai\Validator\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Jackai\Validator\AdvancedValidator;
use Jackai\Validator\Tests\Fixtures\TestController;
use PHPUnit\Framework\TestCase;

class AdvancedValidatorTest extends TestCase
{
    /**
     * 測試預設值
     */
    public function testWithoutData()
    {
        $data = array();

        $annotation = new AdvancedValidator($data);

        $emptyArray = ['form', 'query', 'requireForm', 'requireQuery'];

        foreach ($emptyArray as $k => $v) {
            $this->assertTrue(is_array($annotation->$v));
            $this->assertEquals(0, count($annotation->$v));
        }

        $nullValue = ['requireFormErrorCode', 'requireQueryErrorCode'];

        foreach ($nullValue as $k => $v) {
            $this->assertEquals(null, $annotation->$v);
        }

        $trueValue = ['throwOnValidateFail', 'throwOnMissingValidate', 'emptyStringIsUndefined'];

        foreach ($trueValue as $k => $v) {
            $this->assertEquals(true, $annotation->$v);
        }
    }

    /**
     * 測試載入資料
     */
    public function testData()
    {
        $reflectedMethod = new \ReflectionMethod(new TestController(), 'allConfigAction');

        // the annotations
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotation($reflectedMethod, AdvancedValidator::class);

        $this->assertEquals(false, $annotations->throwOnValidateFail);
        $this->assertEquals(false, $annotations->throwOnMissingValidate);
        $this->assertEquals(false, $annotations->emptyStringIsUndefined);
        $this->assertEquals(998, $annotations->requireQueryErrorCode);
        $this->assertEquals(999, $annotations->requireFormErrorCode);
        $this->assertEquals(['name'], $annotations->requireQuery);
        $this->assertEquals(['postParamName'], $annotations->requireForm);
        $this->assertEquals(
            [
                [
                    'name' => 'name',
                    'rule' => 'Assert\Length',
                    'ruleOption' => ["min" => 1, "max" => 30],
                    'errorCode' => '111',
                    'errorMsg' => 'Invalid name',
                ],
                [
                    'name' => 'price',
                    'rule' => 'Assert\GreaterThan',
                    'ruleOption' => 0,
                    'errorCode' => '112',
                    'default' => '99999',
                    'errorMsg' => 'Invalid price',
                ],
                [
                    'name' => 'picture',
                    'rule' => 'Assert\Length',
                    'ruleOption' => ["min" => 1, "max" => 30],
                    'errorCode' => '113',
                    'errorMsg' => 'Invalid picture',
                ],
                [
                    'name' => 'picture',
                    'rule' => 'require',
                    'ruleOption' => [
                        "mode" => 'if',
                        "values" => ['price', '999'],
                    ],
                    'errorCode' => '118',
                    'errorMsg' => 'If price is 999, you should private picture.',
                ],
            ],
            $annotations->query
        );
        $this->assertEquals(
            [
                [
                    'name' => 'postField',
                    'rule' => 'Assert\Length',
                    'ruleOption' => ["min" => 1, "max" => 30],
                    'errorCode' => '111',
                    'errorMsg' => 'Invalid post_field',
                ],
            ],
            $annotations->form
        );
    }
}