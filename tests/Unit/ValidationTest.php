<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\Validation\Validator;

class ValidationTest extends TestCase
{
    protected $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testRequiredRule()
    {
        $this->validator->add('name', 'required');

        $this->assertTrue($this->validator->validate(['name' => 'John']));
        
        $this->assertFalse($this->validator->validate(['name' => '']));
        $this->assertFalse($this->validator->validate(['name' => null]));
        
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('name', $messages);
    }

    public function testNumberRule()
    {
        $this->validator->add('age', 'number');

        $this->assertTrue($this->validator->validate(['age' => 25]));
        $this->assertTrue($this->validator->validate(['age' => '25']));
        
        $this->assertFalse($this->validator->validate(['age' => 'twenty']));
    }

    public function testEmailRule()
    {
        $this->validator->add('email', 'email');

        $this->assertTrue($this->validator->validate(['email' => 'test@example.com']));
        
        $this->assertFalse($this->validator->validate(['email' => 'invalid-email']));
    }

    public function testMultipleRules()
    {
        $this->validator->add('age', 'required | number | between(18, 99)');

        $this->assertTrue($this->validator->validate(['age' => 25]));
        
        $this->assertFalse($this->validator->validate(['age' => 17])); // Below range
        $this->assertFalse($this->validator->validate(['age' => 100])); // Above range
        $this->assertFalse($this->validator->validate(['age' => ''])); // Missing
    }
    
    public function testLabeledSelector()
    {
        $this->validator->add('email:Email Address', 'required');
        
        $this->assertFalse($this->validator->validate(['email' => '']));
        
        $messages = $this->validator->getMessages();
        // The exact message depends on the template, usually "{label} is required"
        $this->assertStringContainsString('Email Address', $messages['email'][0]);
    }
}
