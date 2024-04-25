<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    //Para obtener el usuario autenticado actualmente se utiliza el Current User
     //el if del user se usa para comprobar si el formulario se ha enviado sin el 'Content-Type': 'application/json'
        //si no está el if y no enviemos el context-type en la cabecera de la peticion en la pestaña no aparecera
        //ningun mensaje
    public function login(IriConverterInterface $iriConverter,#[CurrentUser] User $user = null):Response{
        if (!$user){
            return $this->json([
                'error' => 'Invalid login request: check that Content-Type',
            ], 401);
        }
        //todo ha salido bien pero no hay contenido que devolver, ese es el codigo 204
        //el iri era la url que obtenemos al hacer la peticion
        //por ejemplo /api/users/5 eso seria un ejemplo de iri
        return new Response(null, 204, [
            'Location' => $iriConverter->getIriFromResource($user),
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/logout', name: 'app_logout')]
    //para que esta funcion funcione y nunca llegue al throw hay que activarlo en el archivo security.yaml
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}