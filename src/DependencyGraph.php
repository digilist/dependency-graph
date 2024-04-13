<?php

namespace Digilist\DependencyGraph;

use ArrayObject;
use SplObjectStorage;

/**
 * @template T
 * @phpstan-type Node DependencyNode<T>
 * @phpstan-type DependencyObject ArrayObject<array-key, Node>
 * @phpstan-type DependencyObjectStorage SplObjectStorage<Node, DependencyObject>
 * This class can resolve a dependency graph.
 */
class DependencyGraph
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var DependencyObjectStorage
     */
    private $dependencies;

    public function __construct()
    {
        $this->dependencies = new SplObjectStorage();
    }

    /**
     * Add a new node to the graph and adopt the defined dependencies automatically.
     * @param Node $node
     */
    public function addNode(DependencyNode $node): void
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
     * @param Node $node
     * @param Node $dependsOn
     */
    public function addDependency(DependencyNode $node, DependencyNode $dependsOn): void
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
     * @return Node[]
     */
    public function findRootNodes(): array
    {
        /** @var \SplObjectStorage<Node, bool> $possibleRoots */
        $possibleRoots = new \SplObjectStorage();
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
        $rootNodes = [];
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
     * @return Node[]
     */
    public function resolve(): array
    {
        if ($this->dependencies->count() === 0) {
            return [];
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
     * @param Node $rootNode
     * @param DependencyObject $resolved
     * @param DependencyObject $seen
     */
    private function innerResolve(DependencyNode $rootNode, ArrayObject $resolved, ArrayObject $seen): void
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
     * @return DependencyObjectStorage
     */
    public function getDependencies(): SplObjectStorage
    {
        return $this->dependencies;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Does the ArrayObject $haystack contain the passed DependencyNode $needle?
     *
     * @param Node $needle
     * @param DependencyObject $haystack
     */
    private function arrayObjectContains(DependencyNode $needle, ArrayObject $haystack): bool
    {
        foreach ($haystack as $node) {
            if ($node === $needle) {
                return true;
            }
        }

        return false;
    }
}
