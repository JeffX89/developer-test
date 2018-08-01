<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Orders;
use App\Entity\Supporter;
use App\Form\SupporterFormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            // check if supporter had already ordered. todo this doesnt check order
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
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order", name="order")
     */
    public function order(Request $request)
    {
        // send users to homepage when session is not set
        $supporterId = $this->get('session')->get('loggedInSupporter');
        if (empty($supporterId)) {
            return $this->redirectToRoute('home');
        }
        echo 'logged in userid: '.$supporterId;
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();
        $articleFormChoices = [];
        foreach ($articles as $article) {
            $articleFormChoices[$article->getName()] = $article->getId();
        }
        $order = new Orders();
        $form = $this->createFormBuilder($order)
            ->add('articleId', ChoiceType::class, array(
            'choices'   => $articleFormChoices,
            'required'  => true,
            'multiple'  => false,
            'expanded'  => true,
            ))
//            ->add('supporterId', HiddenType::class, ['value' => $supporterId])
            ->add('save', SubmitType::class, ['label'=>'Verzenden'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $order->setSupporterId($supporterId);
            // check if order already exists for supporter .
            $repository = $this->getDoctrine()->getRepository(Orders::class);
            $existingOrder = $repository->createQueryBuilder('o')
                ->andWhere('o.supporterId = :supporter')
                ->setParameter('supporter', $supporterId)
                ->getQuery()
                ->execute();

            if (!empty($existingOrder)){
                $form->addError(new FormError('U heeft reeds een bestelling geplaatst'));
            }

            // save supporter and set in session
            if ($form->getErrors()->count() === 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($order);
                $entityManager->flush();
                return $this->redirectToRoute('order_success');
            }

        }

        return $this->render('home/order.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order_success", name="order_success")
     */
    public function order_success()
    {
        $this->get('session')->set('loggedInSupporter', null);
        return $this->render('home/order_success.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
