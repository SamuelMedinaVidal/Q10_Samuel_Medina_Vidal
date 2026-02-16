<?php

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

final class ContactController extends AbstractController
{
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
            // Gestionar la subida del archivo de imagen
            $imgFile = $form->get('img')->getData();

            if ($imgFile) {
                $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Generar un nombre único para el archivo
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imgFile->guessExtension();

                // Mover el archivo al directorio de uploads
                try {
                    $imgFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/proveedores',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen. Por favor, inténtalo de nuevo.');
                    return $this->redirectToRoute('app_proveedor');
                }

                // Guardar solo el nombre del archivo en la base de datos
                $contact->setImg($newFilename);
            } else {
                // Si no se sube imagen, establecer un valor por defecto
                $contact->setImg('default.png');
            }

            // Persistir la entidad
            $entityManager->persist($contact);
            $entityManager->flush();

            // Mensaje flash de éxito
            $this->addFlash('success', '¡Solicitud enviada con éxito!');

            return $this->redirectToRoute('app_proveedor');
        }

        return $this->render('page/proveedor.html.twig', [
            'form' => $form,
        ]);
    }
}
