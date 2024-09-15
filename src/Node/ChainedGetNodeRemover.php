<?php

namespace Bottelet\TranslationChecker\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;

class ChainedGetNodeRemover extends NodeVisitorAbstract
{
    public function enterNode(Node $node): Node
    {
        if ($node instanceof Expression) {
            $this->processExpression($node->expr);
        } elseif ($node instanceof Node\Expr\FuncCall) {
            foreach ($node->getArgs() as $arg) {
                /** @var Node\Expr $value */
                $value = $this->processNode($arg->value);
                $arg->value = $value;
            }
        } elseif ($node instanceof MethodCall) {
            return $this->processMethodCall($node);
        } elseif ($node instanceof Closure) {
            $this->processClosure($node);
        }
        return $node;
    }

    private function processExpression(Node $expr): void
    {
        if ($expr instanceof Node\Expr\Assign) {
            /** @var Node\Expr $value */
            $value = $this->processNode($expr->expr);
            $expr->expr = $value;
        }
    }

    private function processNode(Node $node): Node|Node\Expr
    {
        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node);
        }

        if ($node instanceof Closure) {
            $this->processClosure($node);
        }

        return $node;
    }

    private function processMethodCall(MethodCall $node): Node
    {
        if ($node->name instanceof Node\Identifier && $node->name->name === 'get') {
            return $node->var;
        }

        /** @var Node\Expr $value */
        $value = $this->processNode($node->var);
        $node->var = $value;
        foreach ($node->getArgs() as $arg) {
            /** @var Node\Expr $value */
            $value = $this->processNode($arg->value);
            $arg->value = $value;
        }

        return $node;
    }

    private function processClosure(Closure $closure): void
    {
        foreach ($closure->stmts as $stmt) {
            if ($stmt instanceof Node) {
                $this->processNode($stmt);
            }
        }
    }
}
