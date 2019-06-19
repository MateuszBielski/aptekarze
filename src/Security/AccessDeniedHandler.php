<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use App\Controller\SecurityController;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    // private $sc;
    
    // public function __construc(SecurityController $sc)
    // {
    //     $this->sc = $sc;
    // }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $content = "<p> Brak uprawnień </p>";
        // $sc = new SecurityController;
        // return $sc->access_denied();
        return new Response($content, 403);
    }
}