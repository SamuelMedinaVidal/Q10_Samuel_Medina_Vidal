<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * =============================================================================
 * CONTROLADOR DE PÁGINAS ESTÁTICAS
 * =============================================================================
 * 
 * Gestiona las páginas públicas estáticas del sitio (Home, About).
 * Estas páginas no requieren autenticación.
 * 
 * Rutas disponibles:
 * - GET /       → Página principal (Home)
 * - GET /about  → Página "Sobre nosotros"
 * 
 * @package App\Controller
 */
final class PageController extends AbstractController
{
    /**
     * Página principal de Economik0.
     * 
     * @return Response Vista de la página de inicio
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('page/index.html.twig');
    }

    /**
     * Página "Sobre nosotros".
     * 
     * @return Response Vista de información de la empresa
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig');
    }
}
