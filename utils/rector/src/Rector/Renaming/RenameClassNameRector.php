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
		if ($node instanceof Class_) {
			$oldName = $node->name->toLowerString();
			$names = explode('_', $oldName);

			// Take name parts, capitalize the first letter and return a string.
			$newName = implode('', array_map(fn($namePart) => ucfirst($namePart), $names));

			return $this->classRenamer->renameNode($node, [$oldName => $newName]);
		}

		return null;
	}
}
