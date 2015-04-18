# PHP DependencyGraph
This library provides a simple [Dependency Graph](http://en.wikipedia.org/wiki/Dependency_graph) resolver.

## Installation
You can install this library with Composer by requiring `digilist/php-dependency-graph`

## Example Usage:
```php
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

$resolved = DependencyGraph::resolve($nodeA); // returns [D, E, C, B, A]
```

The passed payload to DependencyNode can be any arbitrary PHP element (primitive, array, object, resource...).
