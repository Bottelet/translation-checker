<?php

namespace Bottelet\TranslationChecker\Tests\Finder;

use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\PersistentKeysManager;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class PersistentKeyManagerTest extends TestCase
{
    protected PersistentKeysManager $persistentKeyManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('translator.persistent_keys', ['test']);
        $this->persistentKeyManager = new PersistentKeysManager;
    }

    #[Test]
    public function happyPath(): void
    {
        $this->assertContains('test', $this->persistentKeyManager->getKeys());
    }

    public function testAddKey(): void
    {
        $this->persistentKeyManager->addKey('cake');
        $this->assertContains('cake', $this->persistentKeyManager->getKeys());
    }
}
