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

    private const TRANSLATION_FUNCTIONS = ['__', 'trans', 'lang'];

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if ($this->isTranslationFunction($functionName)) {
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

    private function isTranslationFunction(string $functionName): bool
    {
        if (in_array($functionName, self::TRANSLATION_FUNCTIONS, true)) {
            return true;
        }

        return config('translator.noop_translation') === $functionName;
    }
}
