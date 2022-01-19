<?php

declare (strict_types=1);

namespace Utils\Rector\Rector\Renaming;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule for renaming class names
 */
final class RenameClassNameRector extends AbstractRector
{
	/**
	 * @readonly
	 * @var ClassRenamer
	 */
	private $classRenamer;

	public function __construct(ClassRenamer $classRenamer)
	{
		$this->classRenamer = $classRenamer;
	}

	/**
	 * @throws \Symplify\RuleDocGenerator\Exception\PoorDocumentationException
	 */
	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Renames the Snake_Case classnames into PascalCase, e.g. Some_Class_Name to SomeClassName', [
				new CodeSample(
					<<<'CODE_SAMPLE'
class Some_Class_Name
{
	public function run()
	{
		$this->something();
	}
}
CODE_SAMPLE
					, <<<'CODE_SAMPLE'
class SomeClassName
{
	public function run()
	{
		$this->something();
	}
}
CODE_SAMPLE
				)
			]
		);
	}

	/**
	 * @return array<class-string<Node>>
	 */
	public function getNodeTypes(): array
	{
		return [Class_::class];
	}

	/**
	 * @param \PhpParser\Node\Stmt\Class_ $node
	 */
	public function refactor(Node $node): ?Node
	{
		$oldName = $node->name->toLowerString();
		if (!$this->str_contains($oldName, '_')) {
			return null;
		}

		$names = explode('_', $oldName);

		// Take name parts, capitalize the first letter and return a string.
		$newName = implode('', array_map(fn($namePart) => ucfirst($namePart), $names));

		$node->name = new Node\Identifier($newName);

		return $node;
	}

	/**
	 * PHP 7 polyfill for the PHP 8 str_contains function
	 *
	 * @link https://www.php.net/manual/en/function.str-contains.php#125977
	 *
	 * @param string $haystack String to search in.
	 * @param string $needle String to search for in haystack.
	 *
	 * @return bool
	 */
	private function str_contains(string $haystack, string $needle): bool
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}
