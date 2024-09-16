<?php

namespace Bottelet\TranslationChecker\Node;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;

class TranslateCommentExtractor extends NodeVisitorAbstract
{
    /** @var array <string> */
    private array $translationKeys = [];

    public function enterNode(Node $node)
    {

        if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->stmts ?? [] as $stmt) {
                if ($stmt instanceof Node\Stmt\Expression) {
                    $this->processExpression($stmt);
                }
            }
        }
        if ($node instanceof Node\Stmt\Expression ) {
            $this->processExpression($node);
        }


        return null;
    }

    /** @return array<int, string> */
    public function getTranslationKeys(): array
    {
        return array_unique($this->translationKeys);
    }

    private function processExpression(Node\Stmt\Expression $node): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        if ( ! str_contains($docComment, '@translate')) {
            return;
        }

        $expr = $node->expr;

        if ($expr instanceof Node\Expr\Assign) {

            if (preg_match('/@translate\s*<(.+)>/', $docComment->getText(), $matches)) {
                $this->processTranslateInputs($matches[1]);
            } elseif ($expr->expr instanceof Node\Scalar\String_) {
                $this->translationKeys[] = $expr->expr->value;
            }
            return;
        }

        if (!$expr instanceof Node\Expr\MethodCall) {
            return;
        }

        $this->processNodeWithComment($expr, $docComment);
    }

    private function processNodeWithComment(Node\Expr\MethodCall $node, Doc $docComment): void
    {
        $docText = $docComment->getText();

        if (preg_match('/@translate\s*<(.+)>/', $docText, $matches)) {
            $this->processTranslateInputs($matches[1]);
        } else {
            $this->processMethodCallArgument($node);
        }
    }

    private function processTranslateInputs(string $inputs): void
    {
        $keys = explode(',', $inputs);
        foreach ($keys as $key) {
            $this->translationKeys[] = trim($key);
        }
    }

    private function processMethodCallArgument(Node\Expr\MethodCall $node): void
    {
        if (isset($node->getArgs()[0])) {
            $arg = $node->getArgs()[0]->value;
            if ($arg instanceof Node\Scalar\String_) {
                $this->translationKeys[] = $arg->value;
            } elseif ($arg instanceof Node\Expr\BinaryOp\Concat) {
                $this->processConcat($arg);
            }
        }
    }

    private function processConcat(Node\Expr\BinaryOp\Concat $node): void
    {
        if ($node->left instanceof Node\Scalar\String_) {
            $this->translationKeys[] = $node->left->value;
        } elseif ($node->left instanceof Node\Expr\BinaryOp\Concat) {
            $this->processConcat($node->left);
        }

        if ($node->right instanceof Node\Scalar\String_) {
            $this->translationKeys[] = $node->right->value;
        } elseif ($node->right instanceof Node\Expr\BinaryOp\Concat) {
            $this->processConcat($node->right);
        }
    }
}
