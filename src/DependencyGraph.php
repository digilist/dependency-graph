<?php

namespace Digilist\DependencyGraph;

use ArrayObject;

/**
 * This class can resolve a dependency graph.
 */
class DependencyGraph
{

    /**
     * Resolve a dependency graph starting from the given node. In the end a valid path will be returned.
     *
     * @param DependencyNode $node
     * @return DependencyNode[]
     */
    public static function resolve(DependencyNode $node)
    {
        $resolved = new ArrayObject();
        self::innerResolve($node, $resolved, new ArrayObject());

        return $resolved->getArrayCopy();
    }

    /**
     * Inner recursive function.
     *
     * @param DependencyNode $node
     * @param ArrayObject|DependencyNode[] $resolved
     * @param ArrayObject|DependencyNode[]  $seen
     * @return ArrayObject|DependencyNode[]
     * @throws \Exception
     */
    private static function innerResolve(DependencyNode $node, ArrayObject $resolved, ArrayObject $seen)
    {
        $seen->append($node);
        foreach ($node->getDependencies() as $edge) {
            if (!static::arrayObjectContains($edge, $resolved)) {
                if (static::arrayObjectContains($edge, $seen)) {
                    throw new CircularDependencyException(
                        sprintf('Circular dependency detected: %s depends on %s', $node->getName(), $edge->getName())
                    );
                }

                static::innerResolve($edge, $resolved, $seen);
            }
        }

        $resolved->append($node);
    }

    /**
     * Does the ArrayObject $haystack contain the passed DependencyNode $needle?
     *
     * @param DependencyNode $needle
     * @param ArrayObject $haystack
     * @return bool
     */
    private static function arrayObjectContains(DependencyNode $needle, ArrayObject $haystack)
    {
        foreach ($haystack as $node) {
            if ($node === $needle) {
                return true;
            }
        }

        return false;
    }
}
