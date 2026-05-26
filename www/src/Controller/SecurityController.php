<?php

namespace App\Controller;

use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ProfileRepository $profileRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->profileRepository = $profileRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: '/', name: 'app_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('admin');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
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

    #[Route(path: '/login-react', name: 'app_login_react', methods: ['POST'])]
    public function loginReact(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des champs manquants
        if (empty($data['username']) || empty($data['password'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants manquants',
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];

        $user = $this->profileRepository->findOneBy(['username' => $username]);

        // Message générique — ne pas révéler si c'est le user ou le mdp qui est faux
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants invalides',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Vérification avec le hasheur — remplace la comparaison en clair
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants invalides',
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