<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;
use Proxies\__CG__\App\Entity\Category;
use App\Entity\Commande;
use App\Entity\Contenucommande;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/api")
*/
class ClientApiController extends FOSRestController
{
    /**
    * @Rest\Get("/users/{username}")
    */
    public function getUserByIdentifiant($username, ObjectManager $manager)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(["username" => $username]);

        if ($user == null)
        {
            throw new HttpException(404, "User not found");
        }

        $userInfo = [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "username" => $user->getUsername(),
            "adresse" => $user->getAdresse()
        ];

        return $this->handleView($this->view($userInfo, 200));
  
    }
    
    /**
    * @Rest\Get("/articles")
    */
    public function getArticles(ObjectManager $manager)
    {
        //récupére les produts
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $products = $repository->findall();


        //représenté les données
        $allProducts = [];
        foreach ($products as $product) {
             //récupére la category
            $repository = $this->getDoctrine()->getRepository(Category::class);
            $categorie = $repository->find($product->getCategory());

            //représenté les données
            $allProducts[] = [
                "id" => $product->getId(),
                "title" => $product->getTitle(),
                "content" => $product->getContent(),
                "prix" => $product->getPrix(),
                "image" => $product->getImage(),
               "category" => $categorie->getTitle()
                
            ];
        }
        return $this->handleView($this->view($allProducts, 200));

    }
        /**
    * @Rest\Get("/articles/{id}")
    */
    public function getArticlesById($id, ObjectManager $manager)
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->findOneBy(["id" => $id]);

        if ($article == null)
        {
            throw new HttpException(404, "User not found");
        }
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categorie = $repository->find($article->getCategory());

        $articleInfo = [
            "id" => $article->getId(),
            "title" => $article->getTitle(),
            "content" => $article->getContent(),
            "prix" => $article->getPrix(),
            "image" => $article->getImage(),
            "category" => $categorie->getTitle()
        ];

        return $this->handleView($this->view($articleInfo, 200));
  
    }
    /**
    * @Rest\Get("/users/{id}/commandes")
    */
    public function getcommande($id)
    {
        $repository = $this->getDoctrine()->getRepository(Commande::class);
        $userCommandes = $repository->findBy(["client" => $id]);

        $allcommandes = [];
        foreach ($userCommandes as $commande) {

            //représenté les données
            $allcommandes[] = [
                "id" => $commande->getId(),
                "PrixTotal" => $commande->getPrixTotal()
                
            ];
        }
        return $this->handleView($this->view($allcommandes, 200));

    } 
    
    /**
     * @Rest\Get("/commandes/{id}")
     */
    public function getCommandeById(Commande $commandes = null)
    {
        if ($commandes == null) {
            throw new HttpException(404, "Order not found");
        }
        $commandeInfo = [
            "id" => $commandes->getId(),
            "total" => $commandes->getPrixtotal(),
            "Articledelacommande" => [],
        ];
        foreach ($commandes->getContenucommandes() as $ContenuCommande) {
            $commandeInfo["Articledelacommande"][] = [
                "id" => $ContenuCommande->getId(),
                "title" => $ContenuCommande->getArticle()->getTitle(),
                "prix" => $ContenuCommande->getArticle()->getPrix()
            ];
        }
        return $this->handleView($this->view($commandeInfo, 200));
    }
        /**
     * @Rest\Get("/articles/barcode/{barcode}")
     */
    public function getArticlesByBarcode($barcode = null)
    {
        if ($barcode == null) {
            throw new HttpException(400, "Barcode not valid");
        }
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->findOneBy(["barcode" => $barcode]);
        if ($article == null) {
            throw new HttpException(404, "Product not found");
        }
        $articleInfo = [
                "id" => $article->getId(),
                "title" => $article->getTitle(),
                "content" => $article->getContent(),
                "prix" => $article->getPrix(),
                "image" => $article->getImage(),
               "category" => $article->getTitle()
           
        ];
      
        return $this->handleView($this->view($articleInfo, 200));
    }

     /**
     * @Rest\Patch("/articles/{id}/quantite")
     */
    public function updateProductsById(Article $article = null, Request $request, ObjectManager $manager)
    {
        if ($article == null) {
            throw new HttpException(404, "Article not found");
        }

    
        $qte = $request->request->get("quantite");
        if ($qte == null || !is_numeric($qte)) {
            throw new HttpException(400, "Bad request");
        }

        $article->setQuantite($qte);
        $manager->persist($article);
        $manager->flush();
        return $this->handleView($this->view(["message" => "quantity updated"], 200));
    }
    

 
}
