<?php

namespace Digilist\DependencyGraph\Tests;

use Digilist\DependencyGraph\DependencyNode;
use PHPUnit\Framework\TestCase;

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
