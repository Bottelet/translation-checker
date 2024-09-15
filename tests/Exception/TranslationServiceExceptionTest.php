<?php

namespace Bottelet\TranslationChecker\Tests\Exception;

use Bottelet\TranslationChecker\Exception\TranslationServiceException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TranslationServiceExceptionTest extends TestCase
{
    #[Test]
    public function botConfiguredExceptionMessage()
    {
        $serviceName = 'TestService';
        $exception = TranslationServiceException::notConfigured($serviceName);

        $expectedMessage = "Translation service TestService is not configured. add correct environment variables or configure the service.";

        $this->assertInstanceOf(TranslationServiceException::class, $exception);
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    #[Test]
    public function notConfiguredExceptionWithDifferentServiceName()
    {
        $serviceName = 'AnotherService';
        $exception = TranslationServiceException::notConfigured($serviceName);

        $expectedMessage = "Translation service AnotherService is not configured. add correct environment variables or configure the service.";

        $this->assertInstanceOf(TranslationServiceException::class, $exception);
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}