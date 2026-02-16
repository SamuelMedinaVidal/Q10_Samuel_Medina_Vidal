<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * =============================================================================
 * CONTROLADOR DE SEGURIDAD (AUTENTICACIÓN)
 * =============================================================================
 * 
 * Gestiona el flujo de autenticación de usuarios: login, logout y dashboard.
 * El firewall de Symfony intercepta las rutas de logout automáticamente.
 * 
 * Rutas disponibles:
 * - GET/POST /login     → Formulario de inicio de sesión
 * - GET      /logout    → Cierre de sesión (interceptado por firewall)
 * - GET      /dashboard → Panel principal post-login
 * 
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * Muestra el formulario de login.
     * 
     * Redirige al dashboard si el usuario ya está autenticado.
     * Muestra errores de autenticación fallida si existen.
     * 
     * @param AuthenticationUtils $authenticationUtils Utilidades de autenticación Symfony
     * @return Response Formulario de login o redirección
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirigir usuarios ya autenticados
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Cierra la sesión del usuario.
     * 
     * Este método nunca se ejecuta realmente: el firewall de Symfony
     * intercepta la petición y procesa el logout automáticamente.
     * 
     * @throws \LogicException Siempre (nunca debería alcanzarse)
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException(
            'Este método está vacío intencionalmente. ' .
            'El firewall intercepta la ruta /logout.'
        );
    }

    /**
     * Muestra el dashboard principal tras login.
     * 
     * Esta vista es genérica. Los usuarios son redirigidos a paneles
     * específicos según su rol por el AuthenticationSuccessHandler.
     * 
     * @return Response Vista del dashboard
     */
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('security/dashboard.html.twig');
    }
}
