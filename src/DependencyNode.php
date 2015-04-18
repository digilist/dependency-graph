<?php

namespace Digilist\DependencyGraph;

/**
 * This class describes a node in a dependency graph.
 */
class DependencyNode
{

    /**
     * @var mixed
     */
    private $name;

    /**
     * @var mixed
     */
    private $element;

    /**
     * @var array
     */
    private $dependencies = array();

    /**
     * Create a new node for the dependency graph. The passed element can be an object or primitive, it doesn't matter.
     * Optionally you can pass a specific name, which will help you if circular dependencies are detected.
     *
     * @param string $name
     * @param mixed $element
     */
    public function __construct($element, $name = null)
    {
        $this->element = $element;
        $this->name = $name;
    }

    /**
     * This node as a dependency on the passed node.
     *
     * @param DependencyNode $node
     */
    public function dependsOn(self $node)
    {
        if (!in_array($node, $this->dependencies)) {
            $this->dependencies[] = $node;
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return DependencyNode[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
