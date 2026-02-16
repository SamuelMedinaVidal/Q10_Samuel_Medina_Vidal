<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * =============================================================================
 * CONTROLADOR DE PRODUCTOS (PANEL DE PROVEEDOR)
 * =============================================================================
 * 
 * Gestiona el CRUD completo de productos para los usuarios con rol PROVEEDOR.
 * Cada proveedor solo puede ver y modificar sus propios productos.
 * 
 * Rutas disponibles:
 * - GET  /admin/productos           → Lista de productos del proveedor
 * - GET  /admin/productos/nuevo     → Formulario de creación
 * - POST /admin/productos/nuevo     → Procesar creación
 * - GET  /admin/productos/{id}/editar   → Formulario de edición
 * - POST /admin/productos/{id}/editar   → Procesar edición
 * - POST /admin/productos/{id}/eliminar → Eliminar producto
 * - POST /admin/productos/{id}/toggle   → Activar/Desactivar producto
 * 
 * @package App\Controller
 */
#[Route('/admin')]
#[IsGranted('ROLE_PROVEEDOR')]
class ProductController extends AbstractController
{
    /** Directorio de subida de imágenes de productos */
    private const UPLOAD_DIR = '/public/uploads/productos';

    /**
     * Lista todos los productos del proveedor autenticado.
     * 
     * @param ProductRepository $productRepository Repositorio de productos
     * @return Response Vista con el listado de productos
     */
    #[Route('/productos', name: 'app_admin_productos')]
    public function index(ProductRepository $productRepository): Response
    {
        $productos = $productRepository->findByVendedor($this->getUser());

        return $this->render('admin/productos/index.html.twig', [
            'productos' => $productos,
        ]);
    }

    /**
     * Muestra y procesa el formulario de creación de producto.
     * 
     * @param Request                $request       Petición HTTP
     * @param EntityManagerInterface $entityManager Gestor de entidades Doctrine
     * @param SluggerInterface       $slugger       Servicio para generar slugs seguros
     * @return Response Formulario o redirección tras éxito
     */
    #[Route('/productos/nuevo', name: 'app_admin_producto_nuevo')]
    public function nuevo(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el vendedor actual al producto
            $product->setVendedor($this->getUser());

            // Procesar imagen si se ha subido
            $this->handleImageUpload($form, $product, $slugger);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', '¡Producto creado con éxito!');
            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('admin/productos/form.html.twig', [
            'form' => $form,
            'titulo' => 'Añadir Producto',
            'producto' => null,
        ]);
    }

    /**
     * Muestra y procesa el formulario de edición de producto.
     * 
     * Verifica que el producto pertenezca al proveedor actual antes de permitir
     * la edición. Gestiona la actualización de imagen eliminando la anterior.
     * 
     * @param Product                $product       Producto a editar (inyectado por ParamConverter)
     * @param Request                $request       Petición HTTP
     * @param EntityManagerInterface $entityManager Gestor de entidades Doctrine
     * @param SluggerInterface       $slugger       Servicio para generar slugs seguros
     * @return Response Formulario o redirección tras éxito
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si el producto no pertenece al usuario
     */
    #[Route('/productos/{id}/editar', name: 'app_admin_producto_editar')]
    public function editar(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Validar propiedad del producto
        $this->validateOwnership($product);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Procesar nueva imagen (elimina la anterior si existe)
            $this->handleImageUpload($form, $product, $slugger, true);

            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', '¡Producto actualizado con éxito!');
            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('admin/productos/form.html.twig', [
            'form' => $form,
            'titulo' => 'Editar Producto',
            'producto' => $product,
        ]);
    }

    /**
     * Elimina un producto y su imagen asociada.
     * 
     * Requiere token CSRF válido para prevenir ataques.
     * 
     * @param Product                $product       Producto a eliminar
     * @param Request                $request       Petición HTTP con token CSRF
     * @param EntityManagerInterface $entityManager Gestor de entidades Doctrine
     * @return Response Redirección al listado
     */
    #[Route('/productos/{id}/eliminar', name: 'app_admin_producto_eliminar', methods: ['POST'])]
    public function eliminar(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->validateOwnership($product);

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Eliminar imagen física si existe
            $this->deleteProductImage($product);

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente.');
        }

        return $this->redirectToRoute('app_admin_productos');
    }

    /**
     * Alterna el estado activo/inactivo del producto.
     * 
     * Los productos inactivos no aparecen en la tienda pero se mantienen
     * en el inventario del proveedor.
     * 
     * @param Product                $product       Producto a modificar
     * @param Request                $request       Petición HTTP con token CSRF
     * @param EntityManagerInterface $entityManager Gestor de entidades Doctrine
     * @return Response Redirección al listado
     */
    #[Route('/productos/{id}/toggle', name: 'app_admin_producto_toggle', methods: ['POST'])]
    public function toggleActivo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->validateOwnership($product);

        if ($this->isCsrfTokenValid('toggle' . $product->getId(), $request->request->get('_token'))) {
            $product->setActivo(!$product->isActivo());
            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $status = $product->isActivo() ? 'activado' : 'desactivado';
            $this->addFlash('success', "Producto {$status} correctamente.");
        }

        return $this->redirectToRoute('app_admin_productos');
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE APOYO
    // =========================================================================

    /**
     * Valida que el producto pertenece al usuario autenticado.
     * 
     * @param Product $product Producto a validar
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si el producto no pertenece al usuario
     */
    private function validateOwnership(Product $product): void
    {
        if ($product->getVendedor() !== $this->getUser()) {
            throw $this->createAccessDeniedException(
                'No tienes permiso para modificar este producto.'
            );
        }
    }

    /**
     * Procesa la subida de imagen del formulario.
     * 
     * @param mixed            $form              Formulario con el campo imagenFile
     * @param Product          $product           Producto al que asignar la imagen
     * @param SluggerInterface $slugger           Servicio de slugs
     * @param bool             $deleteOld         Si true, elimina la imagen anterior
     */
    private function handleImageUpload(
        mixed $form,
        Product $product,
        SluggerInterface $slugger,
        bool $deleteOld = false
    ): void {
        $imagenFile = $form->get('imagenFile')->getData();
        
        if (!$imagenFile) {
            return;
        }

        $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

        try {
            $uploadPath = $this->getParameter('kernel.project_dir') . self::UPLOAD_DIR;
            $imagenFile->move($uploadPath, $newFilename);

            // Eliminar imagen anterior si se solicita
            if ($deleteOld) {
                $this->deleteProductImage($product);
            }

            $product->setImagen($newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
        }
    }

    /**
     * Elimina la imagen física de un producto del servidor.
     * 
     * @param Product $product Producto cuya imagen eliminar
     */
    private function deleteProductImage(Product $product): void
    {
        if (!$product->getImagen()) {
            return;
        }

        $imagePath = $this->getParameter('kernel.project_dir') 
                   . self::UPLOAD_DIR . '/' 
                   . $product->getImagen();
        
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
