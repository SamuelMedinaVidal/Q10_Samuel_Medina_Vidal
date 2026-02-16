<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * =============================================================================
 * CONTROLADOR DE CONTACTO (SOLICITUD DE PROVEEDOR)
 * =============================================================================
 * 
 * Gestiona el formulario público de solicitud para convertirse en proveedor.
 * Los datos se almacenan en la entidad Contact para revisión posterior.
 * 
 * Rutas disponibles:
 * - GET/POST /proveedor → Formulario de solicitud de proveedor
 * 
 * @package App\Controller
 */
final class ContactController extends AbstractController
{
    /** Directorio de subida de imágenes de solicitudes */
    private const UPLOAD_DIR = '/public/uploads/proveedores';
    
    /** Imagen por defecto si no se sube ninguna */
    private const DEFAULT_IMAGE = 'default.png';

    /**
     * Muestra y procesa el formulario de solicitud de proveedor.
     * 
     * Este formulario permite a usuarios externos solicitar ser proveedores
     * de la plataforma Economik0. Incluye subida de imagen identificativa.
     * 
     * @param Request                $request       Petición HTTP
     * @param EntityManagerInterface $entityManager Gestor de entidades Doctrine
     * @param SluggerInterface       $slugger       Servicio para generar nombres seguros
     * @return Response Formulario o redirección tras éxito
     */
    #[Route('/proveedor', name: 'app_proveedor')]
    public function proveedor(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Procesar imagen adjunta
            $imageName = $this->handleImageUpload($form->get('img')->getData(), $slugger);
            
            if ($imageName === false) {
                $this->addFlash('error', 'Error al subir la imagen. Por favor, inténtalo de nuevo.');
                return $this->redirectToRoute('app_proveedor');
            }
            
            $contact->setImg($imageName);

            // Persistir solicitud en base de datos
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', '¡Solicitud enviada con éxito!');
            return $this->redirectToRoute('app_proveedor');
        }

        return $this->render('page/proveedor.html.twig', [
            'form' => $form,
        ]);
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE APOYO
    // =========================================================================

    /**
     * Procesa la subida de imagen del formulario.
     * 
     * @param mixed            $imgFile Archivo subido o null
     * @param SluggerInterface $slugger Servicio de slugs
     * @return string|false Nombre del archivo guardado o false si hay error
     */
    private function handleImageUpload(mixed $imgFile, SluggerInterface $slugger): string|false
    {
        // Si no hay imagen, usar la por defecto
        if (!$imgFile) {
            return self::DEFAULT_IMAGE;
        }

        $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imgFile->guessExtension();

        try {
            $uploadPath = $this->getParameter('kernel.project_dir') . self::UPLOAD_DIR;
            $imgFile->move($uploadPath, $newFilename);
            return $newFilename;
        } catch (FileException $e) {
            return false;
        }
    }
}
