<?php

declare(strict_types=1);

namespace Bottelet\TranslationChecker\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class EnumExtractor extends NodeVisitorAbstract
{
    /** @var array<int, string> */
    private array $translationKeys = [];

    public function enterNode(Node $node): Node
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, ['__', '__t', 'trans', 'lang'], true)) {
                $this->processTranslationFunction($node);
            }
        }

        return $node;
    }

    /**
     * @return array<int, string>
     */
    public function getTranslationKeys(): array
    {
        return array_unique($this->translationKeys);
    }

    private function processTranslationFunction(FuncCall $node): void
    {
        $args = $node->getArgs();
        if (!empty($args)) {
            $this->processArgument($args[0]->value);
        }
    }

    private function processArgument(Node $node): void
    {
        if ($node instanceof PropertyFetch) {
            $this->handlePropertyFetch($node);
        } elseif ($node instanceof MethodCall) {
            $this->handleMethodCall($node);
        }
    }

    private function handlePropertyFetch(PropertyFetch $node): void
    {
        if ($node->var instanceof ClassConstFetch) {
            $caseName = $this->getEnumCaseName($node->var);
            $property = $this->getNodeName($node->name);

            if ($property === 'name') {
                $this->translationKeys[] = $caseName;
            } elseif ($property === 'value') {
                $this->translationKeys[] = strtolower($caseName);
            }
        }
    }

    private function handleMethodCall(MethodCall $node): void
    {
        if ($node->var instanceof ClassConstFetch &&
            //TODO: Make config with array of allowed methods empty would mean that it doesnt check for method calls
            in_array($this->getNodeName($node->name), ['label'], true)) {
            $caseName = $this->getEnumCaseName($node->var);
            $this->translationKeys[] = ucfirst(str_replace('_', ' ', strtolower($caseName)));
        }
    }

    private function getEnumCaseName(ClassConstFetch $node): string
    {
        return $node->name instanceof Identifier ? $node->name->toString() : '';
    }

    private function getNodeName(Node\Expr|Identifier $node): string
    {
        if ($node instanceof Identifier) {
            return $node->toString();
        }

        return '';
    }
}
