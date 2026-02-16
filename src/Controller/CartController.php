<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * =============================================================================
 * CONTROLADOR DEL CARRITO DE COMPRAS
 * =============================================================================
 * 
 * Gestiona todas las operaciones del carrito de compras.
 * Solo accesible para usuarios con ROLE_USER (clientes).
 * Los proveedores (ROLE_PROVEEDOR) son redirigidos a su panel.
 * 
 * Rutas disponibles:
 * - GET /cart                → Ver carrito
 * - GET /cart/add/{id}       → Añadir producto
 * - GET /cart/remove/{id}    → Eliminar producto
 * - GET /cart/decrease/{id}  → Reducir cantidad
 * - GET /cart/increase/{id}  → Aumentar cantidad
 * - GET /cart/clear          → Vaciar carrito
 * 
 * @package App\Controller
 */
#[Route('/cart')]
class CartController extends AbstractController
{
    /**
     * Constructor con inyección del servicio de carrito.
     * 
     * @param CartService $cartService Servicio que gestiona el carrito en sesión
     */
    public function __construct(
        private readonly CartService $cartService
    ) {}

    /**
     * Muestra el contenido del carrito.
     * 
     * Verifica que el usuario no sea proveedor antes de mostrar la vista.
     * Los proveedores son redirigidos a su panel de productos.
     * 
     * @return Response Vista del carrito o redirección
     */
    #[Route('', name: 'app_cart')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // Los proveedores no pueden comprar productos
        if ($this->isGranted('ROLE_PROVEEDOR')) {
            $this->addFlash('error', 'Los proveedores no pueden realizar compras.');
            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('cart/index.html.twig', [
            'items' => $this->cartService->getFullCart(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    /**
     * Añade un producto al carrito.
     * 
     * @param int $id ID del producto a añadir
     * @return Response Redirección al carrito
     */
    #[Route('/add/{id}', name: 'app_cart_add')]
    #[IsGranted('ROLE_USER')]
    public function add(int $id): Response
    {
        // Bloquear proveedores
        if ($this->isGranted('ROLE_PROVEEDOR')) {
            $this->addFlash('error', 'Los proveedores no pueden realizar compras.');
            return $this->redirectToRoute('app_admin_productos');
        }

        $this->cartService->add($id);
        $this->addFlash('success', 'Producto añadido al carrito');

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Elimina completamente un producto del carrito.
     * 
     * @param int $id ID del producto a eliminar
     * @return Response Redirección al carrito
     */
    #[Route('/remove/{id}', name: 'app_cart_remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(int $id): Response
    {
        $this->cartService->remove($id);
        $this->addFlash('success', 'Producto eliminado del carrito');

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Reduce la cantidad de un producto en 1 unidad.
     * 
     * @param int $id ID del producto
     * @return Response Redirección al carrito
     */
    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    #[IsGranted('ROLE_USER')]
    public function decrease(int $id): Response
    {
        $this->cartService->decrease($id);
        return $this->redirectToRoute('app_cart');
    }

    /**
     * Aumenta la cantidad de un producto en 1 unidad.
     * 
     * @param int $id ID del producto
     * @return Response Redirección al carrito
     */
    #[Route('/increase/{id}', name: 'app_cart_increase')]
    #[IsGranted('ROLE_USER')]
    public function increase(int $id): Response
    {
        $this->cartService->increase($id);
        return $this->redirectToRoute('app_cart');
    }

    /**
     * Vacía completamente el carrito.
     * 
     * @return Response Redirección al carrito vacío
     */
    #[Route('/clear', name: 'app_cart_clear')]
    #[IsGranted('ROLE_USER')]
    public function clear(): Response
    {
        $this->cartService->clear();
        $this->addFlash('success', 'Carrito vaciado');

        return $this->redirectToRoute('app_cart');
    }
}
