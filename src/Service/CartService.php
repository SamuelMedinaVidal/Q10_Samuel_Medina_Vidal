<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * =============================================================================
 * SERVICIO DE CARRITO DE COMPRAS
 * =============================================================================
 * 
 * Gestiona el carrito de compras almacenado en la sesión del usuario.
 * Proporciona operaciones CRUD sobre los items del carrito y cálculos
 * de totales para la vista.
 * 
 * @package App\Service
 * @author  Economik0 Development Team
 */
class CartService
{
    /** @var SessionInterface Sesión HTTP para persistir el carrito */
    private SessionInterface $session;
    
    /** @var ProductRepository Repositorio para acceder a los productos */
    private ProductRepository $productRepository;

    /**
     * Constructor del servicio.
     * 
     * Inyecta las dependencias necesarias usando el RequestStack de Symfony
     * para obtener la sesión de forma compatible con PHP 8.2+.
     * 
     * @param RequestStack      $requestStack      Stack de peticiones HTTP
     * @param ProductRepository $productRepository Repositorio de productos
     */
    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->session = $requestStack->getSession();
        $this->productRepository = $productRepository;
    }

    /**
     * Obtiene el carrito desde la sesión.
     * 
     * @return array<int, int> Mapa de ID de producto => cantidad
     */
    private function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    /**
     * Persiste el carrito en la sesión.
     * 
     * @param array<int, int> $cart Carrito a guardar
     */
    private function saveCart(array $cart): void
    {
        $this->session->set('cart', $cart);
    }

    /**
     * Añade un producto al carrito.
     * 
     * Si el producto ya existe en el carrito, incrementa su cantidad en 1.
     * Si no existe, lo añade con cantidad inicial de 1.
     * 
     * @param int $id ID del producto a añadir
     */
    public function add(int $id): void
    {
        $cart = $this->getCart();
        $cart[$id] = isset($cart[$id]) ? $cart[$id] + 1 : 1;
        $this->saveCart($cart);
    }

    /**
     * Elimina completamente un producto del carrito.
     * 
     * @param int $id ID del producto a eliminar
     */
    public function remove(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->saveCart($cart);
        }
    }

    /**
     * Reduce la cantidad del producto en 1 unidad.
     * 
     * Si la cantidad llega a 0, el producto se elimina del carrito.
     * 
     * @param int $id ID del producto
     */
    public function decrease(int $id): void
    {
        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            return;
        }

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        $this->saveCart($cart);
    }

    /**
     * Incrementa la cantidad del producto en 1 unidad.
     * 
     * @param int $id ID del producto
     */
    public function increase(int $id): void
    {
        $this->add($id);
    }

    /**
     * Obtiene el carrito completo con datos de productos.
     * 
     * Retorna un array enriquecido con la entidad Product completa,
     * la cantidad y el subtotal calculado para cada línea.
     * Solo incluye productos activos (no desactivados por el vendedor).
     * 
     * @return array<int, array{product: Product, quantity: int, subtotal: float}>
     */
    public function getFullCart(): array
    {
        $cart = $this->getCart();
        $fullCart = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            
            // Solo incluir productos activos y existentes
            if ($product !== null && $product->isActivo()) {
                $fullCart[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => (float) $product->getPrecio() * $quantity
                ];
            }
        }

        return $fullCart;
    }

    /**
     * Calcula el precio total del carrito.
     * 
     * @return float Suma de todos los subtotales
     */
    public function getTotal(): float
    {
        return array_reduce(
            $this->getFullCart(),
            static fn(float $total, array $item): float => $total + $item['subtotal'],
            0.0
        );
    }

    /**
     * Obtiene el número total de artículos en el carrito.
     * 
     * Usado para mostrar el badge en el navbar.
     * 
     * @return int Suma de todas las cantidades
     */
    public function getItemCount(): int
    {
        return array_sum($this->getCart());
    }

    /**
     * Vacía completamente el carrito.
     */
    public function clear(): void
    {
        $this->saveCart([]);
    }

    /**
     * Comprueba si el carrito está vacío.
     * 
     * @return bool True si no hay productos en el carrito
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }
}
