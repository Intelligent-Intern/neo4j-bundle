# Intelligent Intern Neo4j Bundle

The `intelligent-intern/neo4j-bundle` integrates Neo4j with the [Intelligent Intern Core Framework](https://github.com/Intelligent-Intern/core), allowing seamless graph database functionality.

## Installation

Install the bundle using Composer:

``` bash
composer require intelligent-intern/neo4j-bundle
``` 

## Configuration

Ensure the following secrets are set in vault:

``` env
NEO4J_URL=your_neo4j_url
NEO4J_USERNAME=your_neo4j_username
NEO4J_PASSWORD=your_neo4j_password
``` 

and to use the bundle set GRAPH_DB_TYPE to "neo4j".

## Usage

Once the bundle is installed and configured, the Core framework will dynamically detect the Neo4j service via the `graphdb.strategy` tag.

The service will be available via the `GraphDBFactory`:

``` php
<?php

namespace App\Controller;

use App\Service\Api\GraphDBFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GraphController extends AbstractController
{
    public function __construct(
        private GraphDBFactory $graphDBFactory
    ) {}

    public function createNode(Request $request): JsonResponse
    {
        $properties = $request->get('properties', []);

        if (empty($properties)) {
            return new JsonResponse(['error' => 'Properties cannot be empty'], 400);
        }

        try {
            $graphDBService = $this->graphDBFactory->create();
            $node = $graphDBService->createNode($properties);

            return new JsonResponse(['node' => $node]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createEdge(Request $request): JsonResponse
    {
        $fromNodeId = $request->get('fromNodeId');
        $toNodeId = $request->get('toNodeId');
        $type = $request->get('type', 'RELATED');
        $properties = $request->get('properties', []);

        if (empty($fromNodeId) || empty($toNodeId)) {
            return new JsonResponse(['error' => 'fromNodeId and toNodeId are required'], 400);
        }

        try {
            $graphDBService = $this->graphDBFactory->create();
            $edge = $graphDBService->createEdge($fromNodeId, $toNodeId, $type, $properties);

            return new JsonResponse(['edge' => $edge]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
```  

## Extensibility

This bundle is specifically designed to integrate with `intelligent-intern/core`. It leverages the dynamic service discovery mechanism to ensure seamless compatibility.

If you'd like to add additional strategies, simply create a similar bundle that implements the `GraphDBServiceInterface` and tag its service with `graphdb.strategy`.

Also reaching out to jschultz@php.net to get a contribution guide might be a good idea. 

## License

This bundle is open-sourced software licensed under the [MIT license](LICENSE).
