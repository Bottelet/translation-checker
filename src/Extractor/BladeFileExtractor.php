<?php

namespace Bottelet\TranslationChecker\Extractor;

use Exception;
use Illuminate\Support\Facades\Blade;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeVisitor;
use SplFileInfo;

class BladeFileExtractor extends PhpBaseClassExtractor
{
    protected function getCode(SplFileInfo $file): ?string
    {
        $code = parent::getCode($file);
        if (empty($code)) {
            return null;
        }

        $compiledCode = Blade::compileString($code);
        return $compiledCode ?: null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof FuncCall) {
            if ($node->name instanceof Name) {
                $functionName = $node->name->toString();
                if (in_array($functionName, ['__', '__t', '@lang', '@trans', 'trans', 'lang'], true)) {
                    $this->addTranslation($node->getArgs());
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
                    $this->addTranslation($node->getArgs());

                    return NodeVisitor::STOP_TRAVERSAL;
                }
            }
        }

        return null;
    }
}
