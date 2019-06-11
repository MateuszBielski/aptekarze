<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberUserRepository")
 */
class MemberUser extends AbstrMember implements UserInterface
{
    
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberHistory", mappedBy="myUser")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $myHistory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="myUser")
     */
    private $contributions;

    public function __construct()
    {
        $this->myHistory = new ArrayCollection();
        $this->contributions = new ArrayCollection();
    }

    /*
    public function getId(): ?int
    {
        return $this->id;
    }
    */

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|MemberHistory[]
     */
    public function getMyHistory(): Collection
    {
        return $this->myHistory;
    }

    public function addMyHistory(MemberHistory $myHistory): self
    {
        if (!$this->myHistory->contains($myHistory)) {
            $this->myHistory[] = $myHistory;
            $myHistory->setMyHistory($this);
        }

        return $this;
    }

    public function removeMyHistory(MemberHistory $myHistory): self
    {
        if ($this->myHistory->contains($myHistory)) {
            $this->myHistory->removeElement($myHistory);
            // set the owning side to null (unless already changed)
            if ($myHistory->getMyHistory() === $this) {
                $myHistory->setmyHistory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Contribution[]
     */
    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setMyUser($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getMyUser() === $this) {
                $contribution->setMyUser(null);
            }
        }

        return $this;
    }

    public function createTempUsername()
    {
        $this->username = $this->firstName.$this->surname;
    }

}
