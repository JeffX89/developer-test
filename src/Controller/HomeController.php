<?php

namespace App\Controller;

use App\Entity\Supporter;
use App\Form\SupporterFormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Request $request)
    {
        $supporter = new Supporter();
        $form = $this->createForm(SupporterFormType::class, $supporter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supporter = $form->getData();
            // check if supporter had already ordered.
            $repository = $this->getDoctrine()->getRepository(Supporter::class);
            $existingSupporter = $repository->findOneBy([
                'birthDate' => $supporter->getBirthDate(),
                'supporterId' => $supporter->getSupporterId(),
            ]);

            if (!empty($existingSupporter)){
                $form->addError(new FormError('U heeft reeds een bestelling geplaatst'));
            }

            // save supporter and set in session
            if ($form->getErrors()->count() === 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($supporter);
                $entityManager->flush();
                $this->get('session')->set('loggedInSupporter', $supporter->getId());
                return $this->redirectToRoute('order');
            }

        }
//        echo '<pre>';
//        print_r($form->getErrors()->count());
//        echo '<pre>';exit;
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order", name="order")
     */
    public function order()
    {
        echo '<pre>';
        print_r('test');
        echo '<pre>';exit;
        return $this->render('home/order.html.twig', [
            'controller_name' => 'HomeController',
//            'errors' => $form->getErrors(),
        ]);
    }


}
