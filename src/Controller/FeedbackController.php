<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21.03.19
 * Time: 23:17
 */

namespace App\Controller;


use App\Entity\Feedback;
use App\Form\FeedbackForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback", name="feedback")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(FeedbackForm::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $formData = $form->getData();

            $feedback = new Feedback();
            $feedback->setName($formData['name']);
            $feedback->setText($formData['text']);
            $feedback->setCreated(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($feedback);
            $em->flush();

            $this->addFlash('success', "Thank you, {$formData['name']}! Your feedback will improve this system!");

            return $this->redirect('/feedback');
        }

        return $this->render('baseForm.twig', ['formTitle' => 'Send your feedback', 'form' => $form->createView()]);
    }
}