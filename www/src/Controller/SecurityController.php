<?php

namespace App\Controller;

use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private ProfileRepository $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    #[Route(path: '/', name: 'app_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('admin');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/login-react", name="app_login_react")
     */
    #[Route(path: '/login-react', name: 'app_login_react')]
    public function loginReact(Request $request): JsonResponse
    {
        // on récupère la requête
        $data = json_decode($request->getContent(), true);

        // on récupère le username et le mot de passe
        $username = $data['username'];
        $password = $data['password'];
        // on récupère l'user en bdd d'après le username
        $user = $this->profileRepository->findOneBy(['username' => $username]);

        // on vérifie si l'utilisateur existe
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Utilisateur non trouvé',
                'message' => 'Utilisateur non trouvé',
            ], Response::HTTP_UNAUTHORIZED);
        }
        // on vérifie si le mot de passe est correct
        if ($password !== $user->getPassword()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Mot de passe invalide',
                'message' => 'Mot de passe invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ]
        ], Response::HTTP_OK);
    }
}
