<?php
namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductLot;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produits')]
class ProductController extends AbstractController
{
    // 📦 LIST
#[Route('', methods: ['GET'], name: 'app_shop')]
public function index(
    ProductRepository $repo,
    CategoryRepository $catRepo,
    Request $request
) {
    $search = $request->query->get('q');        // 🔎 recherche
    $categoryId = $request->query->get('cat');  // 📂 filtre catégorie
    $section = $request->query->get('section');
    $categoryName = $request->query->get('category_name');
    $shopTitle = 'Catalogue';

    $qb = $repo->createQueryBuilder('p')
        ->leftJoin('p.category', 'c')
        ->addSelect('c')
        ->orderBy('p.id', 'DESC');

    // 🔎 FILTRE RECHERCHE
    if ($search) {
        $qb->andWhere('p.titre LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    // 📂 FILTRE CATEGORIE
    if ($categoryId) {
        $qb->andWhere('c.id = :cat')
           ->setParameter('cat', $categoryId);
    }

    if ($categoryName) {
        $qb->andWhere('c.nom = :categoryName')
           ->setParameter('categoryName', $categoryName);
        $shopTitle = $categoryName;
    }

    if ($section === 'phares') {
        $qb->andWhere('p.phares = true');
        $shopTitle = 'Produits phares';
    }

    if ($section === 'promotions') {
        $qb->andWhere('p.promotion = true');
        $shopTitle = 'Promotions du moment';
    }

    if ($section === 'nouveautes') {
        $qb->andWhere('p.nouveaute = true');
        $shopTitle = 'Nouveautés';
    }

    if ($section === 'best_sellers') {
        $qb->andWhere('p.bestSeller = true');
        $shopTitle = 'Best sellers';
    }

    $products = $qb->getQuery()->getResult();

    return $this->render('shop-sidebar.html.twig', [
        'products' => $products,
        'categories' => $catRepo->findAll(),
        'currentSearch' => $search,
        'currentCategory' => $categoryId,
        'currentSection' => $section,
        'currentCategoryName' => $categoryName,
        'shopTitle' => $shopTitle
    ]);
}

    // 📦 SHOW
 #[Route('/{id}', methods: ['GET'], name: 'product_show')]
public function show(Product $product, ProductRepository $productRepository)
{
    $similarProducts = $productRepository->createQueryBuilder('p')
        ->join('p.category', 'c')
        ->where('c = :cat')
        ->andWhere('p.id != :id')
        ->setParameter('cat', $product->getCategory())
        ->setParameter('id', $product->getId())
        ->setMaxResults(6)
        ->getQuery()
        ->getResult();

    return $this->render('details.html.twig', [
        'product' => $product,
        'similarProducts' => $similarProducts
    ]);
}

    // 🛠️ ADMIN DELETE PRODUCT
    #[Route('/{id}/delete', name: 'app_product_delete')]
public function delete(Product $product, EntityManagerInterface $em): Response
{
    $em->remove($product);
    $em->flush();

    $this->addFlash('success', 'Produit supprimé');

    return $this->redirectToRoute('admin_products');
}

    // 🛠️ ADMIN UPDATE STOCK
   #[Route('/admin/{id}/stock', methods: ['PATCH'])]
public function updateStock(
    Product $product,
    ProductService $service,
    Request $request
){
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $data = json_decode($request->getContent(), true);

    $service->setStock($product, $data['stock']);

    return $this->json(['message' => 'stock updated']);
}
#[Route('/create', name: 'app_product_create', methods: ['POST'])]
public function create(
    Request $request,
    EntityManagerInterface $em,
    CategoryRepository $categoryRepo
): Response {

    $data = $request->request->all()['product'] ?? [];
    $file = $request->files->get('product')['imageFile'] ?? null;

    if (!$data) {
        $this->addFlash('error', 'Formulaire invalide');
        return $this->redirectToRoute('admin_products');
    }

    $product = new Product();

    $product->setTitre($data['titre'] ?? '');
    $product->setDescription($data['description'] ?? '');
    $product->setPrix((float) ($data['prix'] ?? 0));
    $product->setStock((int) ($data['stock'] ?? 0));

    // 📸 IMAGE UPLOAD
    if ($file instanceof UploadedFile) {

    // 🔥 contrôle taille (10 MB recommandé)
    if ($file->getSize() > 10 * 1024 * 1024) {
        $this->addFlash('error', 'Image trop grande (max 10 MB)');
        return $this->redirectToRoute('admin_products');
    }

    $newFilename = uniqid().'.jpg';

    // 📌 chemin temporaire
    $source = $file->getPathname();

    // 📌 création image avec GD
    $image = imagecreatefromstring(file_get_contents($source));

    if ($image !== false) {

        $width = imagesx($image);
        $height = imagesy($image);

        // 📏 nouvelle taille max 1200px
        $maxWidth = 1200;

        if ($width > $maxWidth) {

            $newHeight = ($maxWidth / $width) * $height;

            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            imagecopyresampled(
                $resized,
                $image,
                0, 0, 0, 0,
                $maxWidth,
                $newHeight,
                $width,
                $height
            );

            // 💾 compression JPEG (75%)
            imagejpeg(
                $resized,
                $this->getParameter('products_directory').'/'.$newFilename,
                75
            );

            imagedestroy($resized);

        } else {

            // si petite image → juste compression
            imagejpeg(
                $image,
                $this->getParameter('products_directory').'/'.$newFilename,
                75
            );
        }

        imagedestroy($image);
    }

    $product->setImage($newFilename);
}

    // 🏷️ FLAGS
    $product->setPromotion(isset($data['promotion']));
    $product->setNouveaute(isset($data['nouveaute']));
    $product->setBestSeller(isset($data['bestSeller']));
    $product->setPhares(isset($data['phares']));

    // 📂 CATEGORY
    if (!empty($data['category'])) {
        $category = $categoryRepo->find($data['category']);
        $product->setCategory($category);
    }
    $product->setCreatedAt(new \DateTimeImmutable());

    $em->persist($product);
    $em->flush();

    $this->addFlash('success', 'Produit créé avec succès');

    return $this->redirectToRoute('admin_products');
}
#[Route('/{id}/edit', name: 'app_product_edit', methods: ['POST'])]
public function edit(
    Product $product,
    Request $request,
    EntityManagerInterface $em,
    CategoryRepository $categoryRepo
): Response {

  $data = $request->request->all('product');
$file = $request->files->get('product')['imageFile'] ?? null;

$product->setTitre($data['titre'] ?? '');
$product->setDescription($data['description'] ?? '');
$product->setPrix((float) ($data['prix'] ?? 0));
$product->setStock((int) ($data['stock'] ?? 0));

/* 📸 IMAGE UPDATE */
if ($file instanceof UploadedFile) {

    if ($file->getSize() > 10 * 1024 * 1024) {
        $this->addFlash('error', 'Image trop grande (max 10MB)');
        return $this->redirectToRoute('admin_products');
    }

    // 🧹 delete old image
    if ($product->getImage()) {
        $oldPath = $this->getParameter('products_directory').'/'.$product->getImage();
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $newFilename = uniqid().'.jpg';

    $image = imagecreatefromstring(file_get_contents($file->getPathname()));

    if ($image) {

        $width = imagesx($image);
        $height = imagesy($image);

        $maxWidth = 1200;

        if ($width > $maxWidth) {
            $newHeight = ($maxWidth / $width) * $height;
            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            imagecopyresampled($resized, $image, 0,0,0,0, $maxWidth,$newHeight,$width,$height);

            imagejpeg($resized, $this->getParameter('products_directory').'/'.$newFilename, 75);
            imagedestroy($resized);
        } else {
            imagejpeg($image, $this->getParameter('products_directory').'/'.$newFilename, 75);
        }

        imagedestroy($image);
    }

    $product->setImage($newFilename);
}

$product->setUpdatedAt(new \DateTimeImmutable());

$em->flush();

$this->addFlash('success', 'Produit modifié');

return $this->redirectToRoute('admin_products');
}

#[Route('/produits/{id}/lots', name: 'app_product_update_lots', methods: ['POST'])]
public function updateLots(
    Product $product,
    Request $request,
    EntityManagerInterface $em
): Response {
    // ⚠️ Pour récupérer un tableau, il faut utiliser all()
    $lotsData = $request->request->all('lots');

    // Debug
    // dd($lotsData);

    // Supprimer les anciens lots
    foreach ($product->getLots()->toArray() as $oldLot) {
        $product->removeLot($oldLot);
        $em->remove($oldLot);
    }

    $em->flush();

    // Recréer les nouveaux lots
    foreach ($lotsData as $lotData) {
        if (empty(trim($lotData['nom'] ?? ''))) {
            continue;
        }

        $lot = new ProductLot();
        $lot->setNom(trim($lotData['nom']));
        $lot->setQuantite((int) ($lotData['quantite'] ?? 0));
        $lot->setPrix((float) ($lotData['prix'] ?? 0));
        $lot->setProduct($product);

        $em->persist($lot);
    }

    $product->setUpdatedAt(new \DateTimeImmutable());

    $em->flush();
    $this->addFlash('success', 'Les lots ont été mis à jour avec succès.');

    return $this->redirectToRoute('admin_products');
}
#[Route('/product/{id}/lots', methods: ['GET'])]
public function getProductLots(Product $product)
{
    $lots = [];

    foreach ($product->getLots() as $lot) {
        $lots[] = [
            'id' => $lot->getId(),
            'nom' => $lot->getNom(),
            'quantite' => $lot->getQuantite(),
            'prix' => $lot->getPrix(),
        ];
    }

    return $this->json($lots);
}
}
