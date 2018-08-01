<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Orders
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $supporterId;

    /**
     * @ORM\Column(type="integer")
     */
    private $articleId;

    public function getId()
    {
        return $this->id;
    }

    public function getSupporterId(): ?int
    {
        return $this->supporterId;
    }

    public function setSupporterId(int $supporterId): self
    {
        $this->supporterId = $supporterId;

        return $this;
    }

    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }
}
