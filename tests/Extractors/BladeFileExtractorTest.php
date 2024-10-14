<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitor;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class BladeFileExtractorTest extends TestCase
{
    #[Test]
    public function ensureFileIsCompilable(): void
    {
        $bladeContent = file_get_contents($this->bladeFile->getRealPath());
        $compiledString = Blade::compileString($bladeContent);
        $this->assertNotEmpty($compiledString);
    }

    #[Test]
    public function canFindFunctionsInBlade(): void
    {
        $phpExtractor = new BladeFileExtractor;
        $foundStrings = $phpExtractor->extractFromFile($this->bladeFile);

        $this->assertContains('This is a demo page to showcase translations and Blade components.', $foundStrings);
        $this->assertContains('You are currently not logged in.', $foundStrings);
    }

    #[Test]
    public function canFindExtendedUnderScoreFunctionsInBlade(): void
    {
        $this->app->config->set('translator.noop_translation', '__noop');
        $phpExtractor = new BladeFileExtractor;
        $code = <<<'CODE'
            @extends('layouts.app')
            @section('content')
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">Example Component</div>

                                <div class="card-body">
                                    {{ __noop('Hello, world!') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        CODE;

        $file = $this->createTempFile('extended-underscore-test.blade.php', $code);
        $foundStrings = $phpExtractor->extractFromFile($file);

        $this->assertContains('Hello, world!', $foundStrings);
    }

    #[Test]
    public function emptyArrayOnEmptyBladeFile()
    {
        $phpExtractor = new BladeFileExtractor;
        $bladeFilePath = $this->createTempFile('empty-test.blade.php', null);
        $bladeFile = new SplFileInfo($bladeFilePath);

        $foundStrings = $phpExtractor->extractFromFile($bladeFile);
        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function canHandleFuncCallNode(): void
    {
        $funcCallNode = new FuncCall(new Name('trans'), [new Node\Arg(new Node\Scalar\String_('Hello, world!'))]);

        $bladeExtractor = new BladeFileExtractor;

        $result = $bladeExtractor->enterNode($funcCallNode);

        $this->assertNull($result);
    }

    #[Test]
    public function isNullOnNonFuncCallNode(): void
    {
        $methodCallNode = new MethodCall(new Node\Expr\Variable('someVar'), 'someMethod');
        $bladeExtractor = new BladeFileExtractor;
        $result = $bladeExtractor->enterNode($methodCallNode);

        $this->assertNull($result);
    }

    #[Test]
    public function willIgnoreGetMethodIfNodeHasNoNameProperty():void
    {
        $code = <<<'BLADE'
                <div>
                    @if ($request->get('version') === "1.0")
                        {{ __('Complete registration') }}
                    @endif
                </div>
BLADE;

        $file = $this->createTempFile('node-without-name-prop.blade.php', $code);
        $phpExtractor = new BladeFileExtractor;
        $foundStrings = $phpExtractor->extractFromFile($file);

        $this->assertContains('Complete registration', $foundStrings);
    }
}
