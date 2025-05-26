<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController
{
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('refresh_token', '/', null, true, true, 'Strict');

        return $response;
    }
}