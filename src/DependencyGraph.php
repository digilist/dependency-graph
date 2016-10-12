<?php

namespace Digilist\DependencyGraph;

use ArrayObject;
use SplObjectStorage;

/**
 * This class can resolve a dependency graph.
 */
class DependencyGraph
{

    /**
     * @var DependencyNode[]
     */
    private $nodes = array();

    /**
     * @var SplObjectStorage|ArrayObject[]
     */
    private $dependencies;

    public function __construct()
    {
        $this->dependencies = new SplObjectStorage();
    }

    /**
     * Add a new node to the graph and adopt the defined dependencies automatically.
     *
     * @param DependencyNode $node
     */
    public function addNode(DependencyNode $node)
    {
        if (!$this->dependencies->contains($node)) {
            $this->dependencies->attach($node, new ArrayObject());
            $this->nodes[] = $node;

            foreach ($node->getDependencies() as $depency) {
                $this->addDependency($node, $depency);
            }
        }
    }

    /**
     * Add a new dependency between two nodes.
     * If the starting node or the dependend is not added to the graph yet, they will be added automatically.
     *
     * @param DependencyNode $node
     * @param DependencyNode $dependsOn
     */
    public function addDependency(DependencyNode $node, DependencyNode $dependsOn)
    {
        if (!$this->dependencies->contains($node)) {
            $this->addNode($node);
        }
        if (!$this->dependencies->contains($dependsOn)) {
            $this->addNode($dependsOn);
        }

        if (!$this->arrayObjectContains($dependsOn, $this->dependencies[$node])) {
            $this->dependencies[$node]->append($dependsOn);
        }

        $node->dependsOn($dependsOn);
    }

    /**
     * Find all connected graphs in the set of all graphs.
     *
     * @return ArrayObject
     */
    public function findRootNodes()
    {
        $possibleRoots = new SplObjectStorage();
        foreach ($this->nodes as $node) {
            $possibleRoots[$node] = true;
        }

        // Detect all nodes which couldn't be root
        foreach ($this->dependencies as $node) {
            $nodeDependencies = $this->dependencies[$node];
            foreach ($nodeDependencies as $dependency) {
                $possibleRoots[$dependency] = false;
            }
        }

        // Create array which contains all roots
        $rootNodes = array();
        foreach ($possibleRoots as $node) {
            if ($possibleRoots[$node]) {
                $rootNodes[] = $node;
            }
        }

        return $rootNodes;
    }

    /**
     * Resolve this dependency graph. In the end a valid path will be returned.
     *
     * @return DependencyNode[]
     */
    public function resolve()
    {
        if ($this->dependencies->count() === 0) {
            return array();
        }

        $resolved = new ArrayObject();
        foreach ($this->findRootNodes() as $rootNode) {
            $this->innerResolve($rootNode, $resolved, new ArrayObject());
        }

        //all resolved?
        if ($resolved->count() !== count($this->nodes)) {
            throw new CircularDependencyException();
        }

        $resolvedElements = array_map(function (DependencyNode $node) {
            return $node->getElement();
        }, $resolved->getArrayCopy());

        return $resolvedElements;
    }

    /**
     * Inner recursive function.
     *
     * @param DependencyNode $rootNode
     * @param ArrayObject|DependencyNode[] $resolved
     * @param ArrayObject|DependencyNode[]  $seen
     * @return ArrayObject|DependencyNode[]
     * @throws \Exception
     */
    private function innerResolve(DependencyNode $rootNode, ArrayObject $resolved, ArrayObject $seen)
    {
        $seen->append($rootNode);
        foreach ($rootNode->getDependencies() as $edge) {
            if (!$this->arrayObjectContains($edge, $resolved)) {
                if ($this->arrayObjectContains($edge, $seen)) {
                    throw new CircularDependencyException(
                        sprintf('Circular dependency detected: %s depends on %s', $rootNode->getName(), $edge->getName())
                    );
                }

                $this->innerResolve($edge, $resolved, $seen);
            }
        }

        $resolved->append($rootNode);
    }

    /**
     * @return SplObjectStorage|ArrayObject[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return DependencyNode[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Does the ArrayObject $haystack contain the passed DependencyNode $needle?
     *
     * @param DependencyNode $needle
     * @param ArrayObject $haystack
     * @return bool
     */
    private function arrayObjectContains(DependencyNode $needle, ArrayObject $haystack)
    {
        foreach ($haystack as $node) {
            if ($node === $needle) {
                return true;
            }
        }

        return false;
    }
}
