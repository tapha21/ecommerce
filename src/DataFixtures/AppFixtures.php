<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Orders;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- 1. CRÉATION DES USERS ---
        $users = [];
        
        // 1 Admin
        $admin = new User();
        $admin->setEmail('tapha@tapha.sn')
            ->setNom('TALL')
            ->setPrenom('Tapha')
            ->setRoles(['ROLE_ADMIN'])
            ->setTelephone('771234567')
            ->setAdresse('Plateau, Dakar')
            ->setPassword($this->hasher->hashPassword($admin, '1234'));
             $manager->persist($admin);

        // 3 Users classiques
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail("user$i@gmail.com")
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setRoles(['ROLE_USER'])
                ->setTelephone($faker->phoneNumber)
                ->setAdresse($faker->address)
                ->setPassword($this->hasher->hashPassword($user, 'user123'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- 2. CRÉATION DES CATÉGORIES (7 au total) ---
        $categories = [];
        $catNames = ['Romans', 'Livres Islamiques', 'Science', 'Développement Personnel', 'Enfants', 'Fantasy', 'Histoire'];
        
        foreach ($catNames as $name) {
            $category = new Category();
            $category->setNom($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        // Ajout d'une catégorie spécifique pour les BOX
        $boxCategory = new Category();
        $boxCategory->setNom('Box Cadeaux');
        $manager->persist($boxCategory);

        // --- 3. CRÉATION DES PRODUITS (8 par catégorie) ---
        $allProducts = [];
        foreach ($categories as $cat) {
            for ($j = 1; $j <= 8; $j++) {
                $product = new Product();
                $product->setTitre($cat->getNom() . " - Livre n°" . $j)
                    ->setDescription($faker->paragraph)
                    ->setPrix($faker->randomFloat(2, 5000, 25000))
                    ->setStock($faker->numberBetween(10, 100))
                    ->setImage("assets/img/hero/heroimg2.png") // Utilise ton image par défaut
                    ->setCategory($cat)
                    ->setNouveaute($faker->boolean(30))
                    ->setPhares($faker->boolean(20))
                    ->setPromotion($faker->boolean(20))
                    ->setBestSeller($faker->boolean(20))
                    ->setCreatedAt(new \DateTimeImmutable());
                
                $manager->persist($product);
                $allProducts[] = $product;
            }
        }

        // --- 4. CRÉATION DES 5 BOX ---
        for ($b = 1; $b <= 5; $b++) {
            $box = new Product();
            $box->setTitre("Pack Sérénité - Box n°$b")
                ->setDescription("Une magnifique box cadeau contenant un Coran, un tapis et un musc.")
                ->setPrix($faker->randomFloat(2, 35000, 60000))
                ->setStock($faker->numberBetween(5, 20))
                ->setImage("assets/img/hero/heroimg2.png")
                ->setCategory($boxCategory)
                ->setBestSeller(true)
                ->setCreatedAt(new \DateTimeImmutable());
            
            $manager->persist($box);
            $allProducts[] = $box;
        }

        // --- 5. CRÉATION DES 10 COMMANDES ---
        for ($o = 0; $o < 10; $o++) {
            $order = new Orders();
            $order->setUser($faker->randomElement($users)) // Réparti sur les 3 users
                ->setCreatedAt(new \DateTimeImmutable())
                ->setStatus($faker->randomElement(OrderStatus::cases()))
                ->setTrackingNumber("TRK-" . strtoupper($faker->bothify('#?#?#?')));

            $totalOrder = 0;
            // Chaque commande a entre 1 et 3 articles
            for ($itemCount = 0; $itemCount < $faker->numberBetween(1, 3); $itemCount++) {
                $product = $faker->randomElement($allProducts);
                
                $orderItem = new OrderItem();
                $orderItem->setOrder($order)
                    ->setProduct($product)
                    ->setQuantity($faker->numberBetween(1, 2))
                    ->setPrice($product->getPrix())
                    ->setProductName($product->getTitre());
                
                $totalOrder += ($orderItem->getPrice() * $orderItem->getQuantity());
                $manager->persist($orderItem);
            }
            
            $order->setTotal($totalOrder);
            $manager->persist($order);
        }

        $manager->flush();
    }
}