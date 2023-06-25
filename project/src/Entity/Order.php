<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getOrders","getUsers"])]
    private ?int $id = null;

    #[ORM\Column]

    #[Groups(["getOrders","getUsers"])]
    #[Assert\NotBlank(message: "L'ID de la ligne Article ne doit pas être vide ")]
    #[Assert\Length(min: 1, max: 255, minMessage:
     "Le titre doit faire au moins {{ limit }} caractères",
      maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]

    private ?int $Articles = null;

    #[ORM\Column]
  
    #[Groups(["getOrders","getUsers"])]
    private ?int $Prix = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getOrders"])]
    private ?Users $Users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticles(): ?int
    {
        return $this->Articles;
    }

    public function setArticles(int $Articles): self
    {
        $this->Articles = $Articles;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->Prix;
    }

    public function setPrix(int $Prix): self
    {
        $this->Prix = $Prix;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->Users;
    }

    public function setUsers(?Users $Users): self
    {
        $this->Users = $Users;

        return $this;
    }
}
