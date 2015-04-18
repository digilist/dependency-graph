<?php

namespace Digilist\DependencyGraph\Tests;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;

class DependencyGraphTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testResolving()
    {
        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');

        $nodeA->dependsOn($nodeB);
        $nodeA->dependsOn($nodeD);
        $nodeB->dependsOn($nodeC);
        $nodeB->dependsOn($nodeE);
        $nodeC->dependsOn($nodeD);
        $nodeC->dependsOn($nodeE);

        $resolved = DependencyGraph::resolve($nodeA);
        $this->assertEquals('D', $resolved[0]->getElement());
        $this->assertEquals('E', $resolved[1]->getElement());
        $this->assertEquals('C', $resolved[2]->getElement());
        $this->assertEquals('B', $resolved[3]->getElement());
        $this->assertEquals('A', $resolved[4]->getElement());
    }

    /**
     * Tests whether a circular dependency is detected.
     *
     * @expectedException Digilist\DependencyGraph\CircularDependencyException
     * @test
     */
    public function testCircularDependencyException()
    {
        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');

        $nodeA->dependsOn($nodeB);
        $nodeA->dependsOn($nodeD);
        $nodeB->dependsOn($nodeC);
        $nodeB->dependsOn($nodeE);
        $nodeC->dependsOn($nodeD);
        $nodeC->dependsOn($nodeE);
        $nodeD->dependsOn($nodeB);

        $resolved = DependencyGraph::resolve($nodeA);
        $this->fail('Circular dependency not detected');
    }
}
