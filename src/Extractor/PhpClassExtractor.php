<?php

namespace Bottelet\TranslationChecker\Extractor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

class PhpClassExtractor extends PhpBaseClassExtractor
{
    /**
     * @var string[]
     */
    protected array $translationKeys = [];

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, ['__', '__t', 'trans', 'lang'])) {
                $args = $node->getArgs();
                if (! empty($args)) {
                    $firstArg = $args[0]->value;
                    if ($firstArg instanceof String_) {
                        $this->translationKeys[] = $firstArg->value;
                    }
                }
            }
        }

        return null;
    }
}
