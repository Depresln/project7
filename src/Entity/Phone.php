<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhoneRepository")
 * @ORM\Table(name="p7_phone")
 */
class Phone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list", "show"})
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "show"})
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"list", "show"})
     * @Assert\NotBlank()
     * @Assert\Range(min="0")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Groups({"show"})
     */
    private $description;

    /**
     * @Groups({"list"})
     */
    private $routeList;

    /**
     * @Groups({"show"})
     */
    private $routeShow;

    /**
     * @return mixed
     */
    public function getRouteList()
    {
        return [
            'Links'=>[
                'Go to details (GET)'=>[
                    'href'=>'/api/phone/' . $this->id
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getRouteShow()
    {
        return [
            'Links'=>[
                'Return to list (GET)'=>[
                    'href'=>'/api/phones'
                ]
            ]
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
