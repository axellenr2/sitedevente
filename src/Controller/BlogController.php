<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Category;
Use App\Form\ArticleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\LignePanier;
use App\Form\LignePanierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Contenucommande;
use App\Entity\Commande;
use Symfony\Component\Validator\Constraints\Count;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll(); 
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }
    /**
    * @Route("/panier", name="panier")
    */
    public function panier()
    {
        return $this->render('blog/panier.html.twig');
    } 
    /**
    * @Route("/", name="home")
    */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    } 
    /**
    * @Route("/info", name="info")
    */
    public function info()
    { 

        return $this->render('security/info.html.twig');
        
    } 
     /**
    * @Route("/commande", name="commande")
    */
    public function commande(ObjectManager $manager)
    {  
        $statut = null;
        $panier = $this->getUser()->getPanier(); 
        if($panier->getLigneArticle()->Count() == 0 ){
            $statut= "votre panier est vide";
        }
        else{
            $unecommande = new Commande();
            $unecommande->setPrixtotal($panier->getTotal());
            $unecommande->setClient($this->getUser());
            $manager->persist($unecommande);
            foreach( $panier->getLigneArticle() as $lignepanier){
                $uncontenucommandes = new Contenucommande();
                $uncontenucommandes->setArticle($lignepanier->getLignesPanier());
                $uncontenucommandes->setCommande($unecommande);
                $unecommande->addContenucommande($uncontenucommandes);
                $manager->persist($uncontenucommandes);
                $panier->removeLigneArticle($lignepanier);
                $manager->remove($lignepanier);
            } 
            $manager->flush();
            $statut="La commande a bien était effectuer";

            
        }
        return $this->render('blog/commande.html.twig', [
            "statut"=>$statut,

        ]);
            
    } 

    /**
    * @Route("/blog/new", name="blog_create")
    *@Route("/blog/{id}/edit", name="blog_edit")
    */
    public function form(Article $article = null, Request $request, ObjectManager $manager){
       if(!$article) {
          $article = new Article(); 
       } 
        $form = $this->createFormBuilder($article)
            ->add('title')
            ->add('category', EntityType::class, ['class' =>category::class, 'choice_label' =>'title'])
            ->add('content')
            ->add('image')
            ->add('prix', MoneyType::class)
            ->add('save', SubmitType::class, [
               'label'=> 'Enregistrer'
                ])
            ->getForm();
            $form=$this->createForm(ArticleType::class, $article);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                if(!$article->getId()){
                    $article->setCreatedAt(new \DateTime());
                }
                

                $manager->persist($article);
                $manager->flush(); //envoie la requete

                return $this->redirectToRoute('blog_show', ['id'=> $article->getID()]); //redirige sur la route blog/show et precise l'identifiant de l'article
            }


        return $this->render('blog/create.html.twig', [ 
           'formArticle' => $form->createView(),
           'editMode' => $article->getId() !== null
        ]);
    }
     /**
    * @Route("/blog/{id}", name="blog_show")
    */
    public function show(Article $article, Request $request, ObjectManager $manager)
    {
        $commentform = null;
      $comment = new Comment();
      $form= $this->createForm(CommentType::class, $comment);
     $form->handleRequest($request);
     if($form->isSubmitted() && $form->isValid()){
     $comment->setArticle($article);
        $manager->persist($comment);
        $manager->flush();

        return $this->redirectToRoute('blog_show', ['id'=> $article->getId() ]);
    }
    $commentform= $form->createView();

      $lignepanierform = null;
      $leClient = $this->getUser();
      if ($leClient !== null)
      {

      
      $lignepanier = new LignePanier();
      $form= $this->createForm(LignePanierType::class, $lignepanier);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
          $lignepanier->setLignesPanier($article);
          
          $lignepanier->setNPanier($leClient->getPanier());
          
         
          $manager->persist($lignepanier);
          $manager->flush();

          return $this->redirectToRoute('blog_show', ['id'=> $article->getId() ]);
      }
      $lignepanierform= $form->createView();

      }

        return $this->render('blog/show.html.twig', [ 
            'article' => $article,  
            'commentForm' => $commentform,
            'LignePanierForm' => $lignepanierform
        ]);
    }
    

}