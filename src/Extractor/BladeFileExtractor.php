<?php

namespace Bottelet\TranslationChecker\Extractor;

use Exception;
use Jenssegers\Blade\Blade;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use RuntimeException;
use SplFileInfo;

class BladeFileExtractor extends NodeVisitorAbstract implements ExtractorContract
{
    /**
     * @var string[]
     */
    protected array $translationKeys = [];

    public function extractFromFile(SplFileInfo $file): array
    {
        $filePath = $file->getRealPath();
        if ($file->getExtension() !== 'php') {
            return [];
        }

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 2));
        $traverser = new NodeTraverser;
        $traverser->addVisitor($this);
        try {
            $code = file_get_contents($filePath);
            if ($code === false) {
                return [];
            }
            $renderer = new Blade($filePath, $filePath);
            $compiler = $renderer->compiler();
            try {
                $compiledCode = $compiler->compileString($code);
                if (! $compiledCode) {
                    return [];
                }
                $code = $compiledCode;
            } catch (Exception $e) {
                throw new $e;
            }

            $ast = $parser->parse($code);
            if (is_array($ast)) {
                $traverser->traverse($ast);
            }

        } catch (Error $error) {
            throw new RuntimeException("Error parsing file {$filePath}: {$error->getMessage()}");
        }

        return $this->translationKeys;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof FuncCall) {
            if ($node->name instanceof Name) {
                $functionName = $node->name->toString();
                if (in_array($functionName, ['__', '__t', '@lang', '@trans', 'trans', 'lang'], true)) {
                    $this->addTranslationKey($node->getArgs());
                }
            }
        } elseif ($node instanceof MethodCall && $node->name instanceof Identifier && $node->name->name === 'get') {
            if (! $node->var instanceof FuncCall) {
                return NodeVisitor::STOP_TRAVERSAL;
            }
            /** @var FuncCall $variable */
            $variable = $node->var;
            /** @var Name $function */
            $function = $variable->name;

            if ($function->name === 'app') {
                /** @var Node\Arg $argument */
                $argument = $variable->getArgs()[0];
                /** @var Node\Scalar\String_ $value */
                $value = $argument->value;
                if ($value->value === 'translator') {
                    $this->addTranslationKey($node->getArgs());

                    return NodeVisitor::STOP_TRAVERSAL;
                }
            }
        }

        return null;
    }

    /** @param  array<int, Node\Arg>  $args*/
    private function addTranslationKey(array $args): void
    {
        if (! empty($args)) {
            $firstArg = $args[0]->value;
            if ($firstArg instanceof Node\Scalar\String_) {
                $this->translationKeys[] = $firstArg->value;
            }
        }
    }
}
