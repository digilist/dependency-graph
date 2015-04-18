<?php

namespace Digilist\DependencyGraph\Tests;

use Digilist\DependencyGraph\DependencyNode;

class DependencyNodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testBasicNode()
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
