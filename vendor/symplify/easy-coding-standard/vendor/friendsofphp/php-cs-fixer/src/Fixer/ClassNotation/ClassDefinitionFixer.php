<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * Fixer for part of the rules defined in PSR2 ¶4.1 Extends and Implements and PSR12 ¶8. Anonymous Classes.
 */
final class ClassDefinitionFixer extends \PhpCsFixer\AbstractFixer implements \PhpCsFixer\Fixer\ConfigurableFixerInterface, \PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Whitespace around the keywords of a class, trait or interfaces definition should be one space.', [new \PhpCsFixer\FixerDefinition\CodeSample('<?php

class  Foo  extends  Bar  implements  Baz,  BarBaz
{
}

final  class  Foo  extends  Bar  implements  Baz,  BarBaz
{
}

trait  Foo
{
}

$foo = new  class  extends  Bar  implements  Baz,  BarBaz {};
'), new \PhpCsFixer\FixerDefinition\CodeSample('<?php

class Foo
extends Bar
implements Baz, BarBaz
{}
', ['single_line' => \true]), new \PhpCsFixer\FixerDefinition\CodeSample('<?php

class Foo
extends Bar
implements Baz
{}
', ['single_item_single_line' => \true]), new \PhpCsFixer\FixerDefinition\CodeSample('<?php

interface Bar extends
    Bar, BarBaz, FooBarBaz
{}
', ['multi_line_extends_each_single_line' => \true]), new \PhpCsFixer\FixerDefinition\CodeSample('<?php
$foo = new class(){};
', ['space_before_parenthesis' => \true])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer.
     * Must run after NewWithBracesFixer.
     */
    public function getPriority() : int
    {
        return 36;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        return $tokens->isAnyTokenKindsFound(\PhpCsFixer\Tokenizer\Token::getClassyTokenKinds());
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        // -4, one for count to index, 3 because min. of tokens for a classy location.
        for ($index = $tokens->getSize() - 4; $index > 0; --$index) {
            if ($tokens[$index]->isClassy()) {
                $this->fixClassyDefinition($tokens, $index);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition() : \PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface
    {
        return new \PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('multi_line_extends_each_single_line', 'Whether definitions should be multiline.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption(), (new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('single_item_single_line', 'Whether definitions should be single line when including a single item.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption(), (new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('single_line', 'Whether definitions should be single line.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption(), (new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('space_before_parenthesis', 'Whether there should be a single space after the parenthesis of anonymous class (PSR12) or not.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * @param int $classyIndex Class definition token start index
     */
    private function fixClassyDefinition(\PhpCsFixer\Tokenizer\Tokens $tokens, int $classyIndex) : void
    {
        $classDefInfo = $this->getClassyDefinitionInfo($tokens, $classyIndex);
        // PSR2 4.1 Lists of implements MAY be split across multiple lines, where each subsequent line is indented once.
        // When doing so, the first item in the list MUST be on the next line, and there MUST be only one interface per line.
        if (\false !== $classDefInfo['implements']) {
            $classDefInfo['implements'] = $this->fixClassyDefinitionImplements($tokens, $classDefInfo['open'], $classDefInfo['implements']);
        }
        if (\false !== $classDefInfo['extends']) {
            $classDefInfo['extends'] = $this->fixClassyDefinitionExtends($tokens, \false === $classDefInfo['implements'] ? $classDefInfo['open'] : $classDefInfo['implements']['start'], $classDefInfo['extends']);
        }
        // PSR2: class definition open curly brace must go on a new line.
        // PSR12: anonymous class curly brace on same line if not multi line implements.
        $classDefInfo['open'] = $this->fixClassyDefinitionOpenSpacing($tokens, $classDefInfo);
        if ($classDefInfo['implements']) {
            $end = $classDefInfo['implements']['start'];
        } elseif ($classDefInfo['extends']) {
            $end = $classDefInfo['extends']['start'];
        } else {
            $end = $tokens->getPrevNonWhitespace($classDefInfo['open']);
        }
        // 4.1 The extends and implements keywords MUST be declared on the same line as the class name.
        $this->makeClassyDefinitionSingleLine($tokens, $classDefInfo['start'], $end);
    }
    private function fixClassyDefinitionExtends(\PhpCsFixer\Tokenizer\Tokens $tokens, int $classOpenIndex, array $classExtendsInfo) : array
    {
        $endIndex = $tokens->getPrevNonWhitespace($classOpenIndex);
        if (\true === $this->configuration['single_line'] || \false === $classExtendsInfo['multiLine']) {
            $this->makeClassyDefinitionSingleLine($tokens, $classExtendsInfo['start'], $endIndex);
            $classExtendsInfo['multiLine'] = \false;
        } elseif (\true === $this->configuration['single_item_single_line'] && 1 === $classExtendsInfo['numberOfExtends']) {
            $this->makeClassyDefinitionSingleLine($tokens, $classExtendsInfo['start'], $endIndex);
            $classExtendsInfo['multiLine'] = \false;
        } elseif (\true === $this->configuration['multi_line_extends_each_single_line'] && $classExtendsInfo['multiLine']) {
            $this->makeClassyInheritancePartMultiLine($tokens, $classExtendsInfo['start'], $endIndex);
            $classExtendsInfo['multiLine'] = \true;
        }
        return $classExtendsInfo;
    }
    private function fixClassyDefinitionImplements(\PhpCsFixer\Tokenizer\Tokens $tokens, int $classOpenIndex, array $classImplementsInfo) : array
    {
        $endIndex = $tokens->getPrevNonWhitespace($classOpenIndex);
        if (\true === $this->configuration['single_line'] || \false === $classImplementsInfo['multiLine']) {
            $this->makeClassyDefinitionSingleLine($tokens, $classImplementsInfo['start'], $endIndex);
            $classImplementsInfo['multiLine'] = \false;
        } elseif (\true === $this->configuration['single_item_single_line'] && 1 === $classImplementsInfo['numberOfImplements']) {
            $this->makeClassyDefinitionSingleLine($tokens, $classImplementsInfo['start'], $endIndex);
            $classImplementsInfo['multiLine'] = \false;
        } else {
            $this->makeClassyInheritancePartMultiLine($tokens, $classImplementsInfo['start'], $endIndex);
            $classImplementsInfo['multiLine'] = \true;
        }
        return $classImplementsInfo;
    }
    private function fixClassyDefinitionOpenSpacing(\PhpCsFixer\Tokenizer\Tokens $tokens, array $classDefInfo) : int
    {
        if ($classDefInfo['anonymousClass']) {
            if (\false !== $classDefInfo['implements']) {
                $spacing = $classDefInfo['implements']['multiLine'] ? $this->whitespacesConfig->getLineEnding() : ' ';
            } elseif (\false !== $classDefInfo['extends']) {
                $spacing = $classDefInfo['extends']['multiLine'] ? $this->whitespacesConfig->getLineEnding() : ' ';
            } else {
                $spacing = ' ';
            }
        } else {
            $spacing = $this->whitespacesConfig->getLineEnding();
        }
        $openIndex = $tokens->getNextTokenOfKind($classDefInfo['classy'], ['{']);
        if (' ' !== $spacing && \strpos($tokens[$openIndex - 1]->getContent(), "\n") !== \false) {
            return $openIndex;
        }
        if ($tokens[$openIndex - 1]->isWhitespace()) {
            if (' ' !== $spacing || !$tokens[$tokens->getPrevNonWhitespace($openIndex - 1)]->isComment()) {
                $tokens[$openIndex - 1] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $spacing]);
            }
            return $openIndex;
        }
        $tokens->insertAt($openIndex, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $spacing]));
        return $openIndex + 1;
    }
    private function getClassyDefinitionInfo(\PhpCsFixer\Tokenizer\Tokens $tokens, int $classyIndex) : array
    {
        $openIndex = $tokens->getNextTokenOfKind($classyIndex, ['{']);
        $extends = \false;
        $implements = \false;
        $anonymousClass = \false;
        if (!$tokens[$classyIndex]->isGivenKind(\T_TRAIT)) {
            $extends = $tokens->findGivenKind(\T_EXTENDS, $classyIndex, $openIndex);
            $extends = \count($extends) ? $this->getClassyInheritanceInfo($tokens, \key($extends), 'numberOfExtends') : \false;
            if (!$tokens[$classyIndex]->isGivenKind(\T_INTERFACE)) {
                $implements = $tokens->findGivenKind(\T_IMPLEMENTS, $classyIndex, $openIndex);
                $implements = \count($implements) ? $this->getClassyInheritanceInfo($tokens, \key($implements), 'numberOfImplements') : \false;
                $tokensAnalyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
                $anonymousClass = $tokensAnalyzer->isAnonymousClass($classyIndex);
            }
        }
        if ($anonymousClass) {
            $startIndex = $tokens->getPrevMeaningfulToken($classyIndex);
            // go to "new" for anonymous class
        } else {
            $prev = $tokens->getPrevMeaningfulToken($classyIndex);
            $startIndex = $tokens[$prev]->isGivenKind([\T_FINAL, \T_ABSTRACT]) ? $prev : $classyIndex;
        }
        return ['start' => $startIndex, 'classy' => $classyIndex, 'open' => $openIndex, 'extends' => $extends, 'implements' => $implements, 'anonymousClass' => $anonymousClass];
    }
    private function getClassyInheritanceInfo(\PhpCsFixer\Tokenizer\Tokens $tokens, int $startIndex, string $label) : array
    {
        $implementsInfo = ['start' => $startIndex, $label => 1, 'multiLine' => \false];
        ++$startIndex;
        $endIndex = $tokens->getNextTokenOfKind($startIndex, ['{', [\T_IMPLEMENTS], [\T_EXTENDS]]);
        $endIndex = $tokens[$endIndex]->equals('{') ? $tokens->getPrevNonWhitespace($endIndex) : $endIndex;
        for ($i = $startIndex; $i < $endIndex; ++$i) {
            if ($tokens[$i]->equals(',')) {
                ++$implementsInfo[$label];
                continue;
            }
            if (!$implementsInfo['multiLine'] && \strpos($tokens[$i]->getContent(), "\n") !== \false) {
                $implementsInfo['multiLine'] = \true;
            }
        }
        return $implementsInfo;
    }
    private function makeClassyDefinitionSingleLine(\PhpCsFixer\Tokenizer\Tokens $tokens, int $startIndex, int $endIndex) : void
    {
        for ($i = $endIndex; $i >= $startIndex; --$i) {
            if ($tokens[$i]->isWhitespace()) {
                if ($tokens[$i - 1]->isComment() || $tokens[$i + 1]->isComment()) {
                    $content = $tokens[$i - 1]->getContent();
                    if (!('#' === $content || \strncmp($content, '//', \strlen('//')) === 0)) {
                        $content = $tokens[$i + 1]->getContent();
                        if (!('#' === $content || \strncmp($content, '//', \strlen('//')) === 0)) {
                            $tokens[$i] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                        }
                    }
                    continue;
                }
                if ($tokens[$i - 1]->isGivenKind(\T_CLASS) && $tokens[$i + 1]->equals('(')) {
                    if (\true === $this->configuration['space_before_parenthesis']) {
                        $tokens[$i] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                    } else {
                        $tokens->clearAt($i);
                    }
                    continue;
                }
                if (!$tokens[$i - 1]->equals(',') && $tokens[$i + 1]->equalsAny([',', ')']) || $tokens[$i - 1]->equals('(')) {
                    $tokens->clearAt($i);
                    continue;
                }
                $tokens[$i] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                continue;
            }
            if ($tokens[$i]->equals(',') && !$tokens[$i + 1]->isWhitespace()) {
                $tokens->insertAt($i + 1, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
                continue;
            }
            if (\true === $this->configuration['space_before_parenthesis'] && $tokens[$i]->isGivenKind(\T_CLASS) && !$tokens[$i + 1]->isWhitespace()) {
                $tokens->insertAt($i + 1, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
                continue;
            }
            if (!$tokens[$i]->isComment()) {
                continue;
            }
            if (!$tokens[$i + 1]->isWhitespace() && !$tokens[$i + 1]->isComment() && \strpos($tokens[$i]->getContent(), "\n") === \false) {
                $tokens->insertAt($i + 1, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
            if (!$tokens[$i - 1]->isWhitespace() && !$tokens[$i - 1]->isComment()) {
                $tokens->insertAt($i, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
        }
    }
    private function makeClassyInheritancePartMultiLine(\PhpCsFixer\Tokenizer\Tokens $tokens, int $startIndex, int $endIndex) : void
    {
        for ($i = $endIndex; $i > $startIndex; --$i) {
            $previousInterfaceImplementingIndex = $tokens->getPrevTokenOfKind($i, [',', [\T_IMPLEMENTS], [\T_EXTENDS]]);
            $breakAtIndex = $tokens->getNextMeaningfulToken($previousInterfaceImplementingIndex);
            // make the part of a ',' or 'implements' single line
            $this->makeClassyDefinitionSingleLine($tokens, $breakAtIndex, $i);
            // make sure the part is on its own line
            $isOnOwnLine = \false;
            for ($j = $breakAtIndex; $j > $previousInterfaceImplementingIndex; --$j) {
                if (\strpos($tokens[$j]->getContent(), "\n") !== \false) {
                    $isOnOwnLine = \true;
                    break;
                }
            }
            if (!$isOnOwnLine) {
                if ($tokens[$breakAtIndex - 1]->isWhitespace()) {
                    $tokens[$breakAtIndex - 1] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $this->whitespacesConfig->getLineEnding() . $this->whitespacesConfig->getIndent()]);
                } else {
                    $tokens->insertAt($breakAtIndex, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $this->whitespacesConfig->getLineEnding() . $this->whitespacesConfig->getIndent()]));
                }
            }
            $i = $previousInterfaceImplementingIndex + 1;
        }
    }
}
