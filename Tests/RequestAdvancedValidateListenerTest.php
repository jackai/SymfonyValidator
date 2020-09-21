<?php

namespace Jackai\Validator\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Jackai\Validator\RequestAdvancedValidateListener;

class RequestAdvancedValidateListenerTest extends BaseWebTestCase
{
    /**
     * 測試Query欄位必填的狀況
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequiredQueryFail()
    {
        $listener = new RequestAdvancedValidateListener();

        // 當一個欄位必填卻沒帶入時會擲出 InvalidArgumentException
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryOneAction';
            $request = new Request([], [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
        }


        // 當兩個欄位必填卻沒帶入時會擲出第一個欄位 InvalidArgumentException
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryTwoAction';
            $request = new Request([], [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
        }

        // 當第一個欄位填寫後，會擲出第二個欄位必填
        try {
            $query = ['name' => 'test'];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryTwoAction';
            $request = new Request($query, [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('price is required', $e->getMessage());
        }

        // 當欄位都填寫後，可以正常執行
        $query = ['name' => 'test'];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryOneAction';
        $request = new Request($query, [], ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        // 當兩個欄位都填寫後，可以正常執行
        $query = ['name' => 'test', 'price' => 100];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryTwoAction';
        $request = new Request($query, [], ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 測試Form欄位必填的狀況
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequiredFormFail()
    {
        $listener = new RequestAdvancedValidateListener();

        // 當一個欄位必填卻沒帶入時會擲出 InvalidArgumentException
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormOneAction';
            $request = new Request([], [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
        }

        // 當兩個欄位必填卻沒帶入時會擲出第一個欄位 InvalidArgumentException
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormTwoAction';
            $request = new Request([], [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
        }

        // 當第一個欄位填寫後，會擲出第二個欄位必填
        try {
            $form = ['name' => 'test'];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormTwoAction';
            $request = new Request([], $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('price is required', $e->getMessage());
        }

        // 當欄位都填寫後，可以正常執行
        $form = ['name' => 'test'];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormOneAction';
        $request = new Request([], $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        // 當兩個欄位都填寫後，可以正常執行
        $form = ['name' => 'test', 'price' => 100];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormTwoAction';
        $request = new Request([], $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 測試Query及Form都有必填欄位的狀況，及錯誤代碼
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireQueryAndForm()
    {
        $listener = new RequestAdvancedValidateListener();
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireQueryAndFormAction';

        // 當一個Query及Form欄位必填卻沒帶入時Form會先擲出 InvalidArgumentException
        try {
            $request = new Request([], [], ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('postField is required', $e->getMessage());
            $this->assertEquals(999, $e->getCode());
        }

        // 當Query及Form欄位必填卻沒帶入Form欄位時會擲出第一個Query欄位 InvalidArgumentException
        try {
            $form = ['postField' => 'test'];
            $request = new Request([], $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
            $this->assertEquals(998, $e->getCode());
        }

        // 當欄位都填寫後，可以正常執行
        $query = ['name' => 'test'];
        $form = ['postField' => 'test'];
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 測試同一個欄位有多個驗證規則的狀況
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testMultiRules()
    {
        $listener = new RequestAdvancedValidateListener();
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::multiRulesAction';

        // 因為沒有指定必填，所以可以通過
        $query = [];
        $form = [];
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        // 測試錯誤判斷
        try {
            $query = ['name' => '1234'];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(111, $e->getCode());
        }

        // 測試錯誤判斷
        try {
            $query = ['name' => '123456789012345678901234567890A'];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(111, $e->getCode());
        }

        // 測試第一個通過但是第二個不通過的狀況
        try {
            $query = ['name' => '123456789012345678901234567890', 'price' => 0];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid price', $e->getMessage());
            $this->assertEquals(112, $e->getCode());
        }

        // 測試同一個欄位有多個驗證規則
        try {
            $query = ['name' => '123456789012345678901234567890', 'price' => 100];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid price', $e->getMessage());
            $this->assertEquals(113, $e->getCode());
        }

        // 測試都符合規則應該通過
        $query = ['name' => '123456789012345678901234567890', 'price' => 50];
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 測試缺少驗證規則的狀況
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testMissingValidate()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['test' => 'test'];
        $form = [];

        // 測試同一個欄位有多個驗證規則
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::missingValidateAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Missing validate: test', $e->getMessage());
        }

        // 因為設定缺少驗證規則不擲出，所以可以通過
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::multiRulesAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

    }

    /**
     * 測試設定驗證失敗時不擲出的狀況
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testThrowOnValidateFail()
    {
        $listener = new RequestAdvancedValidateListener();
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::throwOnValidateFailAction';
        $query = ['name' => '1234'];
        $request = new Request($query, [], ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);
        $this->assertEquals(true, true);
    }

    /**
     * 當參數為空字串，會當作沒帶入參數
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testEmptyStringIsUndefined()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['name' => ''];
        $form = [];

        // 測試rule部份
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::multiRulesAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        // 測試require部份
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireFormOneAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('name is required', $e->getMessage());
        }

        // 當設定關閉時，會驗證：測試rule部份
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::ruleEmptyStringIsUndefinedAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(111, $e->getCode());
        }

        // 當設定關閉時，會驗證：測試rule部份
        try {
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireEmptyStringIsUndefinedAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(111, $e->getCode());
        }
    }

    /**
     * 測試特殊驗證規則: Require if
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireIf()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireIfAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 999];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireIfAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If price is 999, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }

        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1000];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireIfWithMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 9999];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireIfWithMultiValueAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If price is 999 or 9999, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }
    }

    /**
     * 測試特殊驗證規則: Require with
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireWith()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = [];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 1];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }

        $listener = new RequestAdvancedValidateListener();

        $query = [];
        $form = [];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 1];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }

        try {
            $query = ['name' => 'test123'];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }

        try {
            $query = ['price' => 1, 'name' => 'test123'];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }
    }

    /**
     * 測試特殊驗證規則: Require withAll
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireWithAll()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = [];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAllAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 1];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAllAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }

        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAllMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        $query = ['name' => 'test123'];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithAllMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 1, 'name' => 'test123'];
            $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithMultiValueAction';
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }
    }

    /**
     * 測試特殊驗證規則: Require without
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireWithout()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithoutAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = [];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }

        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1, 'name' => 'test123'];
        $form = [];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithoutMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['price' => 1];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }

        try {
            $query = ['name' => 'test123'];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }

        try {
            $query = [];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price or name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }
    }

    /**
     * 測試特殊驗證規則: Require withoutAll
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testRequireWithoutAll()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithoutAllAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = [];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price, you should private picture.', $e->getMessage());
            $this->assertEquals(118, $e->getCode());
        }

        $listener = new RequestAdvancedValidateListener();

        $query = ['price' => 1, 'name' => 'test123'];
        $form = [];
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::requireWithoutAllMultiValueAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        $query = ['price' => 1];
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        $query = ['name' => 'test123'];
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = [];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('If don\'t private price and name, you should private picture.', $e->getMessage());
            $this->assertEquals(119, $e->getCode());
        }
    }

    /**
     * 測試未帶入參數時會填寫預設值
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testDefault()
    {
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::defaultAction';
        $listener = new RequestAdvancedValidateListener();

        $mockRequest = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(['request', 'query'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestParameterBag->expects($this->any())
            ->method('all')
            ->willReturn([]);

        $mockQueryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $mockQueryParameterBag->expects($this->any())
            ->method('all')
            ->willReturn([]);

        $mockQueryParameterBag->expects($this->any())
            ->method('replace')
            ->will($this->returnCallback(function ($res) {
                $this->assertEquals(['price' => "99999"], $res);
            }));

        $mockRequest->attributes = new \Symfony\Component\HttpFoundation\ParameterBag(['_controller' => $controller]);
        $mockRequest->query = $mockQueryParameterBag;
        $mockRequest->request = $mockRequestParameterBag;

        $mockEvent = $this->mockEvent($mockRequest);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 測試客制化驗證器
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testCustomValidator()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['name' => 'aabbcc1234567890'];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::customValidatorAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['name' => 'aabbcc1234567890+-='];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(110, $e->getCode());
        }
    }

    /**
     * 測試陣列驗證
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testArrayValue()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['name' => ['aabbcc1234567890', '123abc']];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::customValidatorAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        try {
            $query = ['name' => ['aabbcc1234567890', 'aabbcc1234567890+-=']];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid name', $e->getMessage());
            $this->assertEquals(110, $e->getCode());
        }
    }

    /**
     * 測試結構化參數
     */
    public function testStruct()
    {
        $listener = new RequestAdvancedValidateListener();

        $query = ['name' => ['aabbcc1234567890'], 'price' => ['shop' => 100]];
        $form = [];

        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::structAction';
        $request = new Request($query, $form, ['_controller' => $controller]);
        $mockEvent = $this->mockEvent($request);
        $listener->onKernelRequest($mockEvent);

        // 驗證必填欄位
        try {
            $query = ['name' => ['aabbcc1234567890']];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('price.shop is required', $e->getMessage());
        }

        // 驗證欄位值
        try {
            $query = ['name' => ['aabbcc1234567890'], 'price' => ['shop' => 0]];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid shop price', $e->getMessage());
            $this->assertEquals(111, $e->getCode());
        }

        // 驗證選填欄位值
        try {
            $query = ['name' => ['aabbcc1234567890'], 'price' => ['shop' => 1, 'promote' => 0]];
            $request = new Request($query, $form, ['_controller' => $controller]);
            $mockEvent = $this->mockEvent($request);
            $listener->onKernelRequest($mockEvent);
            $this->assertEquals(0, 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid promote price', $e->getMessage());
            $this->assertEquals(112, $e->getCode());
        }
    }

    /**
     * 測試結構化參數未帶入參數時會填寫預設值
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function testStructDefault()
    {
        $controller = 'Jackai\Validator\Tests\Fixtures\TestController::structDefaultAction';
        $listener = new RequestAdvancedValidateListener();

        $mockRequest = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(['request', 'query'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestParameterBag->expects($this->any())
            ->method('all')
            ->willReturn([]);

        $mockQueryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $mockQueryParameterBag->expects($this->any())
            ->method('all')
            ->willReturn([]);

        $mockQueryParameterBag->expects($this->any())
            ->method('replace')
            ->will($this->returnCallback(function ($res) {
                $this->assertEquals(['price' => ['background' => 'red']], $res);
            }));

        $mockRequest->attributes = new \Symfony\Component\HttpFoundation\ParameterBag(['_controller' => $controller]);
        $mockRequest->query = $mockQueryParameterBag;
        $mockRequest->request = $mockRequestParameterBag;

        $mockEvent = $this->mockEvent($mockRequest);
        $listener->onKernelRequest($mockEvent);
    }

    /**
     * 建立Mock
     *
     * @param Request $request
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function mockEvent(Request $request)
    {
        $mockEvent = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\RequestEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEvent->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        $mockEvent->expects($this->any())
            ->method('isMasterRequest')
            ->willReturn(true);

        return $mockEvent;
    }
}
