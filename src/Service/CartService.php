<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;
    private ProductRepository $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->session = $requestStack->getSession();
        $this->productRepository = $productRepository;
    }

    /**
     * Obtiene el carrito de la sesión
     */
    private function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    /**
     * Guarda el carrito en la sesión
     */
    private function saveCart(array $cart): void
    {
        $this->session->set('cart', $cart);
    }

    /**
     * Añadir un producto al carrito (si ya existe, aumentar cantidad)
     */
    public function add(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->saveCart($cart);
    }

    /**
     * Eliminar un producto del carrito
     */
    public function remove(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->saveCart($cart);
    }

    /**
     * Reducir la cantidad en 1 unidad
     */
    public function decrease(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $this->saveCart($cart);
    }

    /**
     * Aumentar la cantidad en 1 unidad
     */
    public function increase(int $id): void
    {
        $this->add($id);
    }

    /**
     * Obtener el carrito completo con objetos Product y cantidades
     */
    public function getFullCart(): array
    {
        $cart = $this->getCart();
        $fullCart = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            
            if ($product && $product->isActivo()) {
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
     * Calcular el precio total del carrito
     */
    public function getTotal(): float
    {
        $total = 0;
        $fullCart = $this->getFullCart();

        foreach ($fullCart as $item) {
            $total += $item['subtotal'];
        }

        return $total;
    }

    /**
     * Obtener el número total de productos en el carrito
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        return array_sum($cart);
    }

    /**
     * Vaciar el carrito
     */
    public function clear(): void
    {
        $this->saveCart([]);
    }

    /**
     * Verificar si el carrito está vacío
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }
}
