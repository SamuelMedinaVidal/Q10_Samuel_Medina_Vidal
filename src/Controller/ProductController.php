<?php

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

#[Route('/admin')]
#[IsGranted('ROLE_PROVEEDOR')]
class ProductController extends AbstractController
{
    #[Route('/productos', name: 'app_admin_productos')]
    public function index(ProductRepository $productRepository): Response
    {
        $productos = $productRepository->findByVendedor($this->getUser());

        return $this->render('admin/productos/index.html.twig', [
            'productos' => $productos,
        ]);
    }

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
            // Asignar el vendedor actual
            $product->setVendedor($this->getUser());

            // Gestionar imagen
            $imagenFile = $form->get('imagenFile')->getData();
            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                try {
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/productos',
                        $newFilename
                    );
                    $product->setImagen($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen');
                }
            }

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

    #[Route('/productos/{id}/editar', name: 'app_admin_producto_editar')]
    public function editar(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Verificar que el producto pertenece al usuario actual
        if ($product->getVendedor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permiso para editar este producto.');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestionar imagen
            $imagenFile = $form->get('imagenFile')->getData();
            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                try {
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/productos',
                        $newFilename
                    );
                    // Eliminar imagen anterior si existe
                    if ($product->getImagen()) {
                        $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/productos/' . $product->getImagen();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    $product->setImagen($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen');
                }
            }

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

    #[Route('/productos/{id}/eliminar', name: 'app_admin_producto_eliminar', methods: ['POST'])]
    public function eliminar(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Verificar que el producto pertenece al usuario actual
        if ($product->getVendedor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permiso para eliminar este producto.');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Eliminar imagen si existe
            if ($product->getImagen()) {
                $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/productos/' . $product->getImagen();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente.');
        }

        return $this->redirectToRoute('app_admin_productos');
    }

    #[Route('/productos/{id}/toggle', name: 'app_admin_producto_toggle', methods: ['POST'])]
    public function toggleActivo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($product->getVendedor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('toggle' . $product->getId(), $request->request->get('_token'))) {
            $product->setActivo(!$product->isActivo());
            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $status = $product->isActivo() ? 'activado' : 'desactivado';
            $this->addFlash('success', "Producto {$status} correctamente.");
        }

        return $this->redirectToRoute('app_admin_productos');
    }
}
