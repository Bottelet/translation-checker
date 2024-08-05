<?php

namespace Bottelet\TranslationChecker\Extractor;

use Exception;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use RuntimeException;
use SplFileInfo;

class PhpBaseClassExtractor extends NodeVisitorAbstract implements ExtractorContract
{
    /**
     * @var string[]
     */
    protected array $translationKeys = [];

    protected function parser(SplFileInfo $file): ?Parser
    {
        if ($file->getExtension() !== 'php') {
            return null;
        }

        return (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 2));
    }

    /** @param  array<int, Node\Arg>  $args */
    protected function addTranslation(array $args): void
    {
        if (! empty($args)) {
            $firstArg = $args[0]->value;
            if ($firstArg instanceof Node\Scalar\String_) {
                $this->translationKeys[] = $firstArg->value;
            }
        }
    }

    public function extractFromFile(SplFileInfo $file): array
    {
        $parser = $this->parser($file);
        if (is_null($parser)) {
            return [];
        }

        $code = $this->getCode($file);
        if (is_null($code)) {
            return [];
        }

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this);
        try {
            $ast = $parser->parse($code);
            if ($ast === null) {
                return [];
            }
            $traverser->traverse($ast);
        } catch (Exception $error) {
            throw new RuntimeException("Error parsing file {$file->getRealPath()}: {$error->getMessage()}");
        }

        return $this->translationKeys;
    }

    protected function getCode(SplFileInfo $file): ?string
    {
        $filePath = $file->getRealPath();
        if (! is_file($filePath)) {
            return null;
        }


        $code = file_get_contents($filePath);
        if ($code === false) {
            return null;
        }

        return $code;
    }
}
