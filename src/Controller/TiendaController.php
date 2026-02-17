<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * =============================================================================
 * CONTROLADOR DE TIENDA (PORTAL PÚBLICO)
 * =============================================================================
 * 
 * Gestiona la visualización del catálogo de productos para los clientes.
 * Incluye búsqueda por texto, filtrado por categoría y vista de detalle.
 * 
 * Rutas disponibles:
 * - GET /tienda                    → Catálogo con filtros
 * - GET /tienda/producto/{id}      → Detalle de producto
 * 
 * @package App\Controller
 */
class TiendaController extends AbstractController
{
    /** Número máximo de productos relacionados a mostrar */
    private const MAX_RELATED_PRODUCTS = 4;

    /**
     * Muestra el catálogo de productos con opciones de filtrado.
     * 
     * Soporta tres modos de visualización:
     * 1. Búsqueda por texto (parámetro q): busca en nombre y categoría
     * 2. Filtro por categoría (parámetro categoria): muestra solo esa categoría
     * 3. Sin filtros: muestra todos los productos activos con stock
     * 
     * @param Request           $request           Petición HTTP con parámetros de filtro
     * @param ProductRepository $productRepository Repositorio de productos
     * @return Response Vista del catálogo
     */
    #[Route('/tienda', name: 'app_tienda')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // Obtener parámetros de filtrado de la URL
        $query = $request->query->get('q', '');
        $categoria = $request->query->get('categoria', '');

        // Aplicar filtros según parámetros recibidos
        $productos = match (true) {
            !empty($query)     => $productRepository->searchByNameOrCategory($query),
            !empty($categoria) => $productRepository->findByCategory($categoria),
            default            => $productRepository->findActiveWithStock(),
        };

        // Obtener todas las categorías para el menú de filtros
        $categorias = $productRepository->findAllCategories();

        return $this->render('tienda/index.html.twig', [
            'productos' => $productos,
            'categorias' => $categorias,
            'q' => $query,
            'categoria' => $categoria,
        ]);
    }

    /**
     * Muestra la página de detalle de un producto.
     * 
     * Incluye una sección de productos relacionados (misma categoría)
     * para fomentar el cross-selling.
     * 
     * @param int               $id                ID del producto
     * @param ProductRepository $productRepository Repositorio de productos
     * @return Response Vista de detalle
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si el producto no existe o no está activo
     */
    #[Route('/tienda/producto/{id}', name: 'app_tienda_detalle')]
    public function detalle(int $id, ProductRepository $productRepository): Response
    {
        $producto = $productRepository->find($id);

        // Verificar existencia y disponibilidad del producto
        if ($producto === null || !$producto->isActivo()) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        // Obtener productos relacionados (misma categoría, excluyendo el actual)
        $relacionados = $this->getRelatedProducts($producto, $productRepository);

        return $this->render('tienda/detalle.html.twig', [
            'producto' => $producto,
            'relacionados' => $relacionados,
        ]);
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE APOYO
    // =========================================================================

    /**
     * Obtiene productos relacionados por categoría.
     * 
     * @param mixed             $producto          Producto actual
     * @param ProductRepository $productRepository Repositorio
     * @return array Lista de productos relacionados (máximo 4)
     */
    private function getRelatedProducts(mixed $producto, ProductRepository $productRepository): array
    {
        $relacionados = $productRepository->findByCategory($producto->getCategoria());
        
        // Excluir el producto actual de la lista
        $relacionados = array_filter(
            $relacionados,
            static fn($p) => $p->getId() !== $producto->getId()
        );
        
        return array_slice($relacionados, 0, self::MAX_RELATED_PRODUCTS);
    }
}
