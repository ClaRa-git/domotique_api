<?php

namespace App\Controller;

use App\Repository\ProfileRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(
        ProfileRepository $profileRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->profileRepository = $profileRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    #[Route(path: '/', name: 'app_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('admin');
    }

    #[Route(path: '/login-data', name: 'app_login_data', methods: ['GET'])]
    public function loginData(): JsonResponse
    {
        $profiles = $this->profileRepository->findAll();

        $data = array_map(fn($profile) => [
            'id'       => $profile->getId(),
            'username' => $profile->getUsername(),
            'avatar'   => $profile->getAvatar()?->getImagePath(),
        ], $profiles);

        return new JsonResponse($data, Response::HTTP_OK);
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

        if (empty($data['username']) || empty($data['password'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants manquants',
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];

        $user = $this->profileRepository->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants invalides',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Identifiants invalides',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Génération du token JWT
        $token = $this->jwtManager->create($user);

        return new JsonResponse([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ]
        ], Response::HTTP_OK);
    }
}