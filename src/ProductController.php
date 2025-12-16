<?php

class ProductController
{
    private ProductRepository $repository;
    private float $usdValue;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
        $this->usdValue = (float)(getenv('PRECIO_USD') ?: 0);
    }

    public function list(): void
    {
        try {
            $products = array_map(fn($product) => $this->transformProduct($product), $this->repository->all());
            Response::json($products);
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function show(array $params): void
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $product = $this->repository->find($id);

            if (!$product) {
                Response::json(['error' => 'Producto no encontrado'], 404);
                return;
            }

            Response::json($this->transformProduct($product));
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function create(): void
    {
        $payload = $this->parseJsonBody();

        if (!$this->isValid($payload)) {
            Response::json(['error' => 'Nombre y precio son requeridos'], 422);
            return;
        }

        try {
            $product = $this->repository->create([
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? '',
                'precio' => $this->normalizePrice($payload['precio']),
            ]);

            Response::json($this->transformProduct($product), 201);
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $payload = $this->parseJsonBody();

        if (!$this->isValid($payload)) {
            Response::json(['error' => 'Nombre y precio son requeridos'], 422);
            return;
        }

        try {
            if (!$this->repository->find($id)) {
                Response::json(['error' => 'Producto no encontrado'], 404);
                return;
            }

            $product = $this->repository->update($id, [
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? '',
                'precio' => $this->normalizePrice($payload['precio']),
            ]);

            Response::json($this->transformProduct($product));
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        try {
            if (!$this->repository->find($id)) {
                Response::json(['error' => 'Producto no encontrado'], 404);
                return;
            }

            $this->repository->delete($id);
            Response::json(['message' => 'Producto eliminado']);
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    private function transformProduct(array $product): array
    {
        $price = (float)$product['precio'];
        $usdRate = $this->usdValue > 0 ? $this->usdValue : 0;
        $product['precio_usd'] = $usdRate > 0 ? round($price / $usdRate, 2) : null;

        return $product;
    }

    private function parseJsonBody(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : [];
    }

    private function isValid(array $payload): bool
    {
        return isset($payload['nombre'], $payload['precio']) && $payload['nombre'] !== '' && is_numeric($payload['precio']);
    }

    private function normalizePrice($value): float
    {
        return round((float)$value, 2);
    }

    private function handleException(Throwable $exception): void
    {
        Response::json([
            'error' => 'Ocurrió un error inesperado',
            'details' => $exception->getMessage(),
        ], 500);
    }
}
