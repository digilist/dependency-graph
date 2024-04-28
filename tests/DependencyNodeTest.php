<?php declare(strict_types=1);

namespace Digilist\DependencyGraph\Tests;

use Digilist\DependencyGraph\DependencyNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DependencyNode::class)]
class DependencyNodeTest extends TestCase
{
    public function testBasicNode(): void
    {
        $node = new DependencyNode('Foobar');

        $this->assertEquals('Foobar', $node->getElement());
        $this->assertNull($node->getName());

        $node->setElement('Foo');
        $node->setName('Bar');
        $this->assertEquals('Foo', $node->getElement());
        $this->assertEquals('Bar', $node->getName());
    }
}
