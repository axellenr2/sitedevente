<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContenucommandeRepository")
 */
class Contenucommande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\commande", inversedBy="contenucommandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commande;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Article", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?commande
    {
        return $this->commande;
    }

    public function setCommande(?commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->Article;
    }

    public function setArticle(Article $Article): self
    {
        $this->Article = $Article;

        return $this;
    }
}
