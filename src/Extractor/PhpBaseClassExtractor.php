<?php

namespace Bottelet\TranslationChecker\Extractor;

use Bottelet\TranslationChecker\Node\EnumExtractor;
use Bottelet\TranslationChecker\Node\TranslateCommentExtractor;
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
        $enumExtractor = new EnumExtractor;
        $translateCommentExtractor = new TranslateCommentExtractor;
        $traverser->addVisitor($this);
        $traverser->addVisitor($enumExtractor);
        $traverser->addVisitor($translateCommentExtractor);

        try {
            $ast = $parser->parse($code);

            if (empty($ast)) {
                return [];
            }
            $traverser->traverse($ast);
        } catch (Exception $error) {
            throw new RuntimeException("Error parsing file {$file->getRealPath()}: {$error->getMessage()}");
        }

        return array_merge(
            $this->translationKeys,
            $enumExtractor->getTranslationKeys(),
            $translateCommentExtractor->getTranslationKeys()
        );
    }

    protected function parser(SplFileInfo $file): ?Parser
    {
        if ($file->getExtension() !== 'php') {
            return null;
        }

        /** @var string $phpVersion */
        $phpVersion = config('translator.php_version');

        return (new ParserFactory)->createForVersion(PhpVersion::fromString($phpVersion));
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

    protected function getCode(SplFileInfo $file): ?string
    {
        $filePath = $file->getRealPath();
        if (! is_file($filePath)) {
            return null;
        }

        return file_get_contents($filePath) ?: null;
    }
}
