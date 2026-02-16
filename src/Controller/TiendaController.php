<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TiendaController extends AbstractController
{
    #[Route('/tienda', name: 'app_tienda')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q', '');
        $categoria = $request->query->get('categoria', '');

        if ($query) {
            $productos = $productRepository->searchByNameOrCategory($query);
        } elseif ($categoria) {
            $productos = $productRepository->findByCategory($categoria);
        } else {
            $productos = $productRepository->findActiveWithStock();
        }

        $categorias = $productRepository->findAllCategories();

        return $this->render('tienda/index.html.twig', [
            'productos' => $productos,
            'categorias' => $categorias,
            'query' => $query,
            'categoriaActual' => $categoria,
        ]);
    }

    #[Route('/tienda/producto/{id}', name: 'app_tienda_detalle')]
    public function detalle(int $id, ProductRepository $productRepository): Response
    {
        $producto = $productRepository->find($id);

        if (!$producto || !$producto->isActivo()) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        // Productos relacionados (misma categoría)
        $relacionados = $productRepository->findByCategory($producto->getCategoria());
        $relacionados = array_filter($relacionados, fn($p) => $p->getId() !== $producto->getId());
        $relacionados = array_slice($relacionados, 0, 4);

        return $this->render('tienda/detalle.html.twig', [
            'producto' => $producto,
            'relacionados' => $relacionados,
        ]);
    }
}
