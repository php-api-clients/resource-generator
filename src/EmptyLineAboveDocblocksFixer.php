<?php

namespace WyriHaximus\ApiClient\Tools;

use Symfony\CS\AbstractFixer;
use Symfony\CS\Tokenizer\Tokens;

class EmptyLineAboveDocblocksFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Tokens::fromCode($content);

        for ($index = 2; $index < count($tokens); $index++) {
            $token = $tokens[$index];
            $previousToken = $tokens[$index - 1];
            $sndPreviousToken = $tokens[$index - 2];
            if (
                $sndPreviousToken->getContent() !== '{' &&
                substr($token->getContent(), 0 , 3) === '/**' &&
                $previousToken->getLine() === $token->getLine() - 1
            ) {
                $previousToken->setContent(PHP_EOL . $previousToken->getContent());
            }
        }

        return $tokens->generateCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Ensure there is an empty line behind abstract or interface methods.';
    }
}
