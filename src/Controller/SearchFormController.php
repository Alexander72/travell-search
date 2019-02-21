<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 13.02.19
 * Time: 1:01
 */

namespace App\Controller;


use App\Entity\SearchRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class SearchFormController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        // just setup a fresh $searchRequest object (remove the dummy data)
        $searchRequest = new SearchRequest();

        $form = $this->createFormBuilder($searchRequest)
                     ->add('dateFrom', DateType::class)
                     ->add('dateTo', DateType::class)
                     ->add('daysDurationMin', IntegerType::class, [
                         'constraints' => [new NotBlank(), new LessThan(5)],
                     ])
                     ->add('daysDurationMax', IntegerType::class, [
                         'constraints' => [new NotBlank()],
                     ])
                     ->add('description', TextType::class)
                     ->add('save', SubmitType::class, ['label' => 'Submit search request'])
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$searchRequest` variable has also been updated
            $searchRequest = $form->getData();

            // ... perform some action, such as saving the searchRequest to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($searchRequest);
            // $entityManager->flush();

            return $this->redirectToRoute('search_request_submitted_successfully');
        }

        return $this->render('searchRequest/form.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     * @Route("/success", name="search_request_submitted_successfully")
     */
    public function success()
    {
        return new Response('Success!');
    }
}