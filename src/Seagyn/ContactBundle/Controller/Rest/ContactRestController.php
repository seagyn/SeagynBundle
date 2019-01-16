<?php

namespace App\Seagyn\ContactBundle\Controller\Rest;

use App\Seagyn\ContactBundle\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactRestController extends AbstractFOSRestController {
	/**
	 * @Rest\Post("/contacts")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 *
	 * @param \Swift_Mailer $mailer
	 * @param ValidatorInterface $validator
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws HttpException
	 */
	public function contact( Request $request, EntityManagerInterface $entityManager, \Swift_Mailer $mailer, ValidatorInterface $validator) {
		// creates a task and gives it some dummy data for this example
		$contact = new Contact();

		$contact->setFirstName($request->get( 'first_name' ));
		$contact->setLastName($request->get( 'last_name' ));
		$contact->setContactNumber($request->get( 'contact_number' ));
		$contact->setEmail($request->get( 'email' ));
		$contact->setMessage($request->get( 'message' ));

		$errors = $validator->validate($contact);

		if (count($errors) > 0) {
			/*
			 * Uses a __toString method on the $errors variable which is a
			 * ConstraintViolationList object. This gives us a nice string
			 * for debugging.
			 */
			$errorsString = (string) $errors;

			return new Response($errorsString, Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$entityManager->persist( $contact );
		$entityManager->flush();

		// IMO this should dispatch an event. However, out of context these will be handled here.
		$contact->notifyUser( $this->renderView(
		// templates/emails/registration.html.twig
			'@SeagynContact/emails/user.html.twig',
			[
				'first_name' => $request->get( 'first_name' ),
			]
		), $mailer );

		// IMO this should dispatch an event. However, out of context these will be handled here.
		$contact->notifyAdmin( $this->renderView(
		// templates/emails/registration.html.twig
			'@SeagynContact/emails/admin.html.twig',
			[
				'first_name'     => $request->get( 'first_name' ),
				'last_name'      => $request->get( 'last_name' ),
				'contact_number' => $request->get( 'contact_number' ),
				'email'          => $request->get( 'email' ),
				'message'        => $request->get( 'message' ),
			]
		), $mailer );

		return new Response($this->json($contact), Response::HTTP_CREATED);
	}
}
