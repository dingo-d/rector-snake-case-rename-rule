<?php

namespace Utils\Rector\Tests\Rector\Renaming\RenameClassNameRector\Fixture;

class some_class_name
{
}

?>
-----
<?php

namespace Utils\Rector\Tests\Rector\Renaming\RenameClassNameRector\Fixture;

class SomeClassName
{
}

?>
