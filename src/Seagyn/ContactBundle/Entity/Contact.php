<?php

namespace App\Seagyn\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Seagyn\ContactBundle\Repository\ContactRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Contact
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     *
     * @Assert\NotBlank
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=45)
     *
     * @Assert\NotBlank
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=16)
     *
     * @Assert\NotBlank
     */
    private $contact_number;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\Email
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=65536)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->contact_number;
    }

    public function setContactNumber(string $contact_number): self
    {
        $this->contact_number = $contact_number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

	/**
	 * Gets triggered only on insert

	 * @ORM\PrePersist
	 */
	public function onPrePersist()
	{
		$this->setCreatedAt(new \DateTime("now"));
		$this->setUpdatedAt(new \DateTime("now"));
	}

	/**
	 * Gets triggered every time on update
	 * @ORM\PreUpdate
	 */
	public function onPreUpdate()
	{
		$this->setUpdatedAt(new \DateTime("now"));
	}

	public function notifyUser($body, \Swift_Mailer $mailer)
	{
		$message = (new \Swift_Message('Thank you for contacting us!'))
			->setFrom('hello@socialplaces.co.za')
			->setTo($this->getEmail())
			->setBody(
				$body,
				'text/html'
			)
		;

		$mailer->send($message);
	}

	public function notifyAdmin($body, \Swift_Mailer $mailer)
	{
		$message = (new \Swift_Message('You have received a query from your site!'))
			->setFrom('hello@socialplaces.co.za')
			->setTo('admin@socialplaces.co.za')
			->setBody(
				$body,
				'text/html'
			)
		;

		$mailer->send($message);
	}
}
