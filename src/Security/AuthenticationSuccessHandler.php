<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * =============================================================================
 * MANEJADOR DE AUTENTICACIÓN EXITOSA
 * =============================================================================
 * 
 * Redirige a los usuarios tras un login exitoso según su rol:
 * - ROLE_PROVEEDOR → Panel de administración de productos
 * - ROLE_USER      → Catálogo de tienda
 * 
 * Este handler se registra en security.yaml bajo form_login.success_handler.
 * 
 * @package App\Security
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * Constructor con inyección del generador de URLs.
     * 
     * @param UrlGeneratorInterface $urlGenerator Servicio para generar rutas
     */
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Procesa la redirección tras autenticación exitosa.
     * 
     * El orden de comprobación importa: ROLE_PROVEEDOR se verifica primero
     * porque también incluye ROLE_USER en la jerarquía de roles.
     * 
     * @param Request        $request Petición HTTP original
     * @param TokenInterface $token   Token de autenticación con datos del usuario
     * @return RedirectResponse Redirección al panel correspondiente
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $roles = $token->getUser()->getRoles();

        // Proveedor: redirigir al panel de gestión de productos
        if (in_array('ROLE_PROVEEDOR', $roles, true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('app_admin_productos')
            );
        }

        // Cliente: redirigir a la tienda
        if (in_array('ROLE_USER', $roles, true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('app_tienda')
            );
        }

        // Fallback: página principal si no tiene rol reconocido
        return new RedirectResponse(
            $this->urlGenerator->generate('index')
        );
    }
}
