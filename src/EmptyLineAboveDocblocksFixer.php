<?php

namespace ApiClients\Tools\ResourceGenerator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

class EmptyLineAboveDocblocksFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ApiClients/empty_line_above_docblocks';
    }

    public function getDefinition()
    {
        return new FixerDefinition(
            'Ensure there is an empty line behind abstract or interface methods.',
            [
                new CodeSample(
                    $this->BOM.'<?php
            
            echo "Hello!";
            '
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        for ($index = 2; $index < count($tokens); $index++) {
            $token = $tokens[$index];
            $previousToken = $tokens[$index - 1];
            $sndPreviousToken = $tokens[$index - 2];
            if ($sndPreviousToken->getContent() !== '{' &&
                substr($token->getContent(), 0, 3) === '/**' /*&&
                $previousToken->getLine() === $token->getLine() - 1*/
            ) {
                $previousToken->setContent(PHP_EOL . $previousToken->getContent());
            }
        }
    }
}
