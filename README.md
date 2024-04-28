[![PHP](https://github.com/digilist/dependency-graph/actions/workflows/php.yml/badge.svg)](https://github.com/digilist/dependency-graph/actions/workflows/php.yml)

# PHP DependencyGraph
This library provides a simple [Dependency Graph](http://en.wikipedia.org/wiki/Dependency_graph) resolver. It supports multiple root nodes which are detected automatically.

## Installation
You can install this library with Composer by requiring `digilist/dependency-graph`

## Example Usage:
There are two ways to define dependencies. One way is to define the dependencies directly on the node by calling the `dependsOn` method. By using this method you have to add the nodes manually to the graph (or at least the root nodes).
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

$graph = new DependencyGraph();
$graph->addNode($nodeA);
$resolved = DependencyGraph->resolve(); // returns [D, E, C, B, A]
```

Alternatively, you can define the dependencies on the graph. By using this method, the root node will be automatically detected.
```php
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

$resolved = DependencyGraph->resolve(); // returns [D, E, C, B, A]
```

The passed payload to DependencyNode can be any arbitrary PHP element (primitive, array, object, resource...).
