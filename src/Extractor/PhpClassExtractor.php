<?php

namespace Bottelet\TranslationChecker\Extractor;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use SplFileInfo;

class PhpClassExtractor extends NodeVisitorAbstract implements ExtractorContract
{
    /**
     * @var string[]
     */
    protected array $translationKeys = [];

    public function extractFromFile(SplFileInfo $file): array
    {
        $filePath = $file->getRealPath();
        if (! is_file($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
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
            $ast = $parser->parse($code);
            if ($ast === null) {
                return [];
            }
            $traverser->traverse($ast);
        } catch (Error $error) {
            echo "Error parsing file {$filePath}: {$error->getMessage()}";

            return [];
        }

        return $this->translationKeys;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, ['__', '__t', '@lang', '@trans', 'trans', 'lang'])) {
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
