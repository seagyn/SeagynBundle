<?php

namespace App\Seagyn\ContactBundle\Controller;

use App\Seagyn\ContactBundle\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController {
	/**
	 * @Route("/contact", name="seagyn_contact_bundle_controller_contact")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function contact( Request $request, EntityManagerInterface $entityManager, \Swift_Mailer $mailer ) {
		// creates a task and gives it some dummy data for this example
		$contact = new Contact();

		$form = $this->createFormBuilder( $contact )
		             ->add( 'first_name', TextType::class )
		             ->add( 'last_name', TextType::class )
		             ->add( 'contact_number', TextType::class )
		             ->add( 'email', EmailType::class )
		             ->add( 'message', TextareaType::class )
		             ->add( 'recaptcha', EWZRecaptchaType::class, array(
			             'attr'        => array(
				             'options' => array(
					             'theme' => 'light',
					             'type'  => 'image',
					             'size'  => 'normal'
				             )
			             ),
			             'label' => false,
			             'mapped'      => false,
			             'constraints' => array(
				             new IsTrue()
			             )
		             ) )
		             ->add( 'save', SubmitType::class, [ 'label' => 'Submit' ] )
		             ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$contact = $form->getData();

			$entityManager->persist( $contact );
			$entityManager->flush();

			$this->addFlash(
				'success',
				'Thank you for getting in contact with us. We will get back to you within 2 business days.'
			);

			// IMO this should dispatch an event. However, out of context these will be handled here.
			$contact->notifyUser( $this->renderView(
			// templates/emails/registration.html.twig
				'@SeagynContact/emails/user.html.twig',
				[
					'first_name' => $form->get( 'first_name' )->getViewData(),
				]
			), $mailer );

			// IMO this should dispatch an event. However, out of context these will be handled here.
			$contact->notifyAdmin( $this->renderView(
			// templates/emails/registration.html.twig
				'@SeagynContact/emails/admin.html.twig',
				[
					'first_name'     => $form->get( 'first_name' )->getViewData(),
					'last_name'      => $form->get( 'last_name' )->getViewData(),
					'contact_number' => $form->get( 'contact_number' )->getViewData(),
					'email'          => $form->get( 'email' )->getViewData(),
					'message'        => $form->get( 'message' )->getViewData(),
				]
			), $mailer );

			return $this->redirectToRoute( 'seagyn_contact_bundle_controller_contact' );
		}

		return $this->render( '@SeagynContact/contact/contact.html.twig', [
			'form' => $form->createView(),
		] );
	}
}
