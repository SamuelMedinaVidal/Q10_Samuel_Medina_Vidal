<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    ) {}

    #[Route('', name: 'app_cart')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // Verificar que no sea proveedor
        if ($this->isGranted('ROLE_PROVEEDOR')) {
            $this->addFlash('error', 'Los proveedores no pueden realizar compras.');
            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('cart/index.html.twig', [
            'items' => $this->cartService->getFullCart(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    #[IsGranted('ROLE_USER')]
    public function add(int $id): Response
    {
        // Verificar que no sea proveedor
        if ($this->isGranted('ROLE_PROVEEDOR')) {
            $this->addFlash('error', 'Los proveedores no pueden realizar compras.');
            return $this->redirectToRoute('app_admin_productos');
        }

        $this->cartService->add($id);
        $this->addFlash('success', 'Producto añadido al carrito');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(int $id): Response
    {
        $this->cartService->remove($id);
        $this->addFlash('success', 'Producto eliminado del carrito');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    #[IsGranted('ROLE_USER')]
    public function decrease(int $id): Response
    {
        $this->cartService->decrease($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/increase/{id}', name: 'app_cart_increase')]
    #[IsGranted('ROLE_USER')]
    public function increase(int $id): Response
    {
        $this->cartService->increase($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    #[IsGranted('ROLE_USER')]
    public function clear(): Response
    {
        $this->cartService->clear();
        $this->addFlash('success', 'Carrito vaciado');

        return $this->redirectToRoute('app_cart');
    }
}
