<?php declare(strict_types=1);

namespace Digilist\DependencyGraph;

/**
 * @template T
 * This class describes a node in a dependency graph.
 */
class DependencyNode
{
    /**
     * @var list<DependencyNode<T>>
     */
    private array $dependencies = [];

    /**
     * Create a new node for the dependency graph. The passed element can be an object or primitive,
     * it doesn't matter, as the resolving is based on nodes.
     *
     * Optionally you can pass a specific name, which will help you if circular dependencies are detected.
     *
     * @param T $element
     */
    public function __construct(private mixed $element = null, private ?string $name = null) {}

    /**
     * This node as a dependency on the passed node.
     *
     * @param DependencyNode<T> $node
     */
    public function dependsOn(self $node): void
    {
        if (!in_array($node, $this->dependencies)) {
            $this->dependencies[] = $node;
        }
    }

    /**
     * @return list<DependencyNode<T>>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return T
     */
    public function getElement(): mixed
    {
        return $this->element;
    }

    /**
     * @param T $element
     *
     * @return $this
     */
    public function setElement(mixed $element): self
    {
        $this->element = $element;

        return $this;
    }
}
