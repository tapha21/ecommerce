<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrdersRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/auth')]
class AuthController extends AbstractController
{
    // 📝 TRAITEMENT REGISTER (modal)
//    #[Route('/register', name: 'register', methods: ['POST'])]
//     public function register(Request $request, AuthService $service, UserRepository $repo): Response
//     {
//         $data = $request->request->all();

//         // 1. créer user
//         $service->register($data);

//         // 2. récupérer user créé
//         $user = $repo->findOneByEmail($data['email']);

//         // 3. auto login
//         $service->login($user);

//         // 4. redirection selon rôle
//         $roles = $user->getRoles();

//         if (in_array('ROLE_ADMIN', $roles)) {
//             return $this->redirectToRoute('dashboard');
//         }

//         return $this->redirectToRoute('app_home');
//     }
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, AuthService $service)
    {
        $service->register($request->request->all());

        return $this->redirectToRoute('login_form');
    }

    // 🔑 TRAITEMENT LOGIN (modal - avec gestion erreur)
    // #[Route('/login', name: 'login', methods: ['POST'])]
    // public function login(
    //     Request $request,
    //     UserRepository $repo,
    //     AuthService $service
    // ): Response {
    //     $data = $request->request->all();
    //     $user = $repo->findOneByEmail($data['email']);

    //     if (!$user || !password_verify($data['password'], $user->getPassword())) {
    //         $this->addFlash('error', 'Email ou mot de passe incorrect.');
    //         return $this->redirectToRoute('app_home');
    //     }

    //     $service->login($user);
    //     $roles = $user->getRoles();

    //     // ADMIN → Dashboard
    //     if (in_array('ROLE_ADMIN', $roles)) {
    //         return $this->redirectToRoute('dashboard');
    //     }

    //     // USER → Accueil
    //     return $this->redirectToRoute('app_home');
    // }
    // #[Route('/login', name: 'login', methods: ['POST'])]
    // public function login()
    // {
    //      return $this->redirectToRoute('app_home');

    // }
#[Route('/login', name: 'login_form', methods: ['GET', 'POST'])]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    if ($authenticationUtils->getLastAuthenticationError()) {
        $this->addFlash('login_error', 'Identifiants invalides');

        return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_home');
}
    // 🚪 LOGOUT
   #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('Handled by Symfony firewall');
    }

    // 👤 PROFILE
  #[Route('/profile', name: 'profile', methods: ['GET'])]
public function me(OrdersRepository $ordersRepository): Response
{
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_home');
    }

    $orders = $ordersRepository->findByUserWithItems($user);

    return $this->render('profile.html.twig', [
        'user' => $user,
        'orders' => $orders
    ]);
}



    // 📊 DASHBOARD
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('dashboard.html.twig');
    }
    #[Route('/change-password', name: 'change_password', methods: ['POST'])]
public function changePassword(
    Request $request,
    UserPasswordHasherInterface $hasher,
    EntityManagerInterface $em,
    AuthService $service
) {
    $user = $service->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $old = $request->request->get('old_password');
    $new = $request->request->get('new_password');

    if (!$old || !$new) {
        $this->addFlash('error', 'Tous les champs sont obligatoires');
        return $this->redirectToRoute('profile');
    }

    if (!$hasher->isPasswordValid($user, $old)) {
        $this->addFlash('error', 'Ancien mot de passe incorrect');
        return $this->redirectToRoute('profile');
    }

    $user->setPassword(
        $hasher->hashPassword($user, $new)
    );

    $em->flush();

    $this->addFlash('success', 'Mot de passe mis à jour avec succès');

    return $this->redirectToRoute('profile');
}
#[Route('/create', name: 'admin_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ) {
        $data = $request->request->all()['user'];

        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setTelephone($data['telephone']);
        $user->setAdresse($data['adresse']);

        $user->setPassword(
            $hasher->hashPassword($user, $data['password'])
        );

        $user->setRoles(json_decode($data['roles'], true));

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    // EDIT
    #[Route('/edit/{id}', name: 'admin_user_edit', methods: ['POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ) {
        $data = $request->request->all();

        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setTelephone($data['telephone']);
        $user->setAdresse($data['adresse']);

        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    // DELETE
    #[Route('/delete/{id}', name: 'admin_user_delete')]
    public function delete(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }
}