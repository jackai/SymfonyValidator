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

        $nullValue = ['requireFormCode', 'requireQueryCode', 'throwOnValidateFail', 'throwOnMissingValidate', 'emptyStringIsUndefined', 'shortErrorMsg'];

        foreach ($nullValue as $k => $v) {
            $this->assertEquals(null, $annotation->$v);
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
        $this->assertEquals(true, $annotations->throwOnMissingValidate);
        $this->assertEquals(false, $annotations->emptyStringIsUndefined);
        $this->assertEquals(998, $annotations->requireQueryCode);
        $this->assertEquals(999, $annotations->requireFormCode);
        $this->assertEquals(true, $annotations->shortErrorMsg);
        $this->assertEquals(['name'], $annotations->requireQuery);
        $this->assertEquals(['postParamName'], $annotations->requireForm);
        $this->assertEquals(
            [
                [
                    'name' => 'name',
                    'rule' => 'Assert\Length',
                    'ruleOption' => ["min" => 1, "max" => 30],
                    'code' => '111',
                    'msg' => 'Invalid name',
                ],
                [
                    'name' => 'price',
                    'rule' => 'Assert\GreaterThan',
                    'ruleOption' => 0,
                    'code' => '112',
                    'default' => '99999',
                    'msg' => 'Invalid price',
                ],
                [
                    'name' => 'picture',
                    'rule' => 'Assert\Length',
                    'ruleOption' => ["min" => 1, "max" => 30],
                    'code' => '113',
                    'msg' => 'Invalid picture',
                ],
                [
                    'name' => 'picture',
                    'rule' => 'require',
                    'ruleOption' => [
                        "mode" => 'if',
                        "values" => ['price', '999'],
                    ],
                    'code' => '118',
                    'msg' => 'If price is 999, you should private picture.',
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
                    'code' => '111',
                    'msg' => 'Invalid post_field',
                ],
            ],
            $annotations->form
        );
    }
}
