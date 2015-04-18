<?php

namespace Digilist\DependencyGraph\Tests;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;

class DependencyGraphTest extends \PHPUnit_Framework_TestCase
{

    /**
     * In case there are no dependencies all nodes should be returned in their added order.
     *
     * @test
     */
    public function testWithoutDependencies()
    {
        $graph = new DependencyGraph();

        $graph->addNode($nodeA = new DependencyNode('A'));
        $graph->addNode($nodeB = new DependencyNode('B'));
        $graph->addNode($nodeC = new DependencyNode('C'));
        $graph->addNode($nodeD = new DependencyNode('D'));

        $resolved = $graph->resolve();
        $this->assertSequence(array('A', 'B', 'C', 'D'), $resolved);
        $this->assertEquals(array($nodeA, $nodeB, $nodeC, $nodeD), $graph->getNodes());
    }

    /**
     * Tests whether the declaration of dependencies directly on the graph works correctly
     * and the dependencies are set on the node.
     *
     * @test
     */
    public function testDeclarationOnGraph()
    {
        $graph = new DependencyGraph();

        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');
        $nodeF = new DependencyNode('F');
        $nodeG = new DependencyNode('G');

        $graph->addDependency($nodeA, $nodeB);
        $graph->addDependency($nodeA, $nodeD);
        $graph->addDependency($nodeB, $nodeC);
        $graph->addDependency($nodeB, $nodeE);
        $graph->addDependency($nodeC, $nodeD);
        $graph->addDependency($nodeC, $nodeE);
        $graph->addDependency($nodeF, $nodeG);

        $this->assertEquals(array($nodeB, $nodeD), $nodeA->getDependencies());
        $this->assertEquals(array($nodeC, $nodeE), $nodeB->getDependencies());
        $this->assertEquals(array($nodeD, $nodeE), $nodeC->getDependencies());
        $this->assertEquals(array(), $nodeD->getDependencies());
        $this->assertEquals(array(), $nodeE->getDependencies());
    }

    /**
     * Tests whether the declaration of dependencies directly on the nodes works correctly
     * and the dependencies are detected by the graph.
     *
     * @test
     */
    public function testDeclarationOnNodes()
    {
        $graph = new DependencyGraph();

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

        $graph->addNode($nodeA);
        $dependencies = $graph->getDependencies();

        $this->assertEquals(array($nodeB, $nodeD), $dependencies[$nodeA]->getArrayCopy());
        $this->assertEquals(array($nodeC, $nodeE), $dependencies[$nodeB]->getArrayCopy());
        $this->assertEquals(array($nodeD, $nodeE), $dependencies[$nodeC]->getArrayCopy());
        $this->assertEquals(array(), $dependencies[$nodeD]->getArrayCopy());
        $this->assertEquals(array(), $dependencies[$nodeE]->getArrayCopy());
    }

    /**
     * Tests the behaviour if no nodes where added.
     *
     * @test
     */
    public function testResolvingWithoutNodes()
    {
        $graph = new DependencyGraph();
        $resolved = $graph->resolve();
        $this->assertEquals(array(), $resolved);
    }

    /**
     * Tests whether the internal declaration of dependencies (on the graph) works correctly.
     *
     * @test
     */
    public function testResolving()
    {
        $graph = new DependencyGraph();

        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');

        $graph->addDependency($nodeA, $nodeB);
        $graph->addDependency($nodeA, $nodeD);
        $graph->addDependency($nodeB, $nodeC);
        $graph->addDependency($nodeB, $nodeE);
        $graph->addDependency($nodeC, $nodeD);
        $graph->addDependency($nodeC, $nodeE);

        $resolved = $graph->resolve();
        $this->assertSequence(array('D', 'E', 'C', 'B', 'A'), $resolved);
    }

    /**
     * Test whether dependencies are solved correctly if there are multiple roots.
     *
     * @test
     */
    public function testWithMultipleRoots()
    {
        $graph = new DependencyGraph();

        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');
        $nodeF = new DependencyNode('F');
        $nodeG = new DependencyNode('G');

        $graph->addDependency($nodeA, $nodeB);
        $graph->addDependency($nodeA, $nodeD);
        $graph->addDependency($nodeB, $nodeC);
        $graph->addDependency($nodeB, $nodeE);
        $graph->addDependency($nodeC, $nodeD);
        $graph->addDependency($nodeC, $nodeE);
        $graph->addDependency($nodeF, $nodeG);

        $resolved = $graph->resolve();
        $this->assertSequence(array('D', 'E', 'C', 'B', 'A', 'G', 'F'), $resolved);
    }

    /**
     * Tests whether a circular dependency is detected.
     *
     * @expectedException Digilist\DependencyGraph\CircularDependencyException
     * @test
     */
    public function testCircularDependencyException()
    {
        $graph = new DependencyGraph();

        $nodeA = new DependencyNode('A');
        $nodeB = new DependencyNode('B');
        $nodeC = new DependencyNode('C');
        $nodeD = new DependencyNode('D');
        $nodeE = new DependencyNode('E');

        $graph->addDependency($nodeA, $nodeB);
        $graph->addDependency($nodeA, $nodeD);
        $graph->addDependency($nodeB, $nodeC);
        $graph->addDependency($nodeB, $nodeE);
        $graph->addDependency($nodeC, $nodeD);
        $graph->addDependency($nodeC, $nodeE);
        $graph->addDependency($nodeD, $nodeB);

        $graph->resolve();
    }

    /**
     * Assert that the resulting sequence is correct.
     *
     * @param array $sequence
     * @param DependencyNode[] $resolved
     */
    private function assertSequence(array $sequence, array $resolved)
    {
        $this->assertEquals(count($sequence), count($resolved));
        foreach ($sequence as $key => $expected) {
            $this->assertEquals($expected, $resolved[$key]->getElement());
        }
    }
}
