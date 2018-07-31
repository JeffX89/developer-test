<?php

namespace App\Controller;

use App\Entity\Article;
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

        //clear session when going to index
        $this->get('session')->set('loggedInSupporter', null);

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
        // send users to homepage when session is not set
        $supporterId = $this->get('session')->get('loggedInSupporter');
        if (empty($supporterId)) {
            return $this->redirectToRoute('home');
        }
        echo 'logged in userid: '.$supporterId;
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();
        echo '<pre>';

        print_r($articles);
        echo '<pre>';exit;
//        $form = $this->createFormBuilder($task)
//            ->add('task', TextType::class)
//            ->add('dueDate', DateType::class)
//            ->add('save', SubmitType::class, array('label' => 'Create Task'))
//            ->getForm();
//        $builder->add('is_anonymous', 'choice', array(
//            'choices'   => $options['is_anonymous'],
//            'required'  => true,
//            'multiple'  => false,
//            'expanded'  => true,
//        ));

        // show products order form

        // save order
        // clear session

        return $this->render('home/order.html.twig', [
            'controller_name' => 'HomeController',
//            'errors' => $form->getErrors(),
        ]);
        echo '<pre>';

        print_r('test');
        echo '<pre>';exit;
    }


}
